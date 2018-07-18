<?php
namespace Deployer;

const ROLE_DEFAULT = 'default';
const ROLE_WORKFLOW = 'workflow';
const ROLE_BUILD = 'build';
const ROLE_FIXTURE_RELOAD = 'fixtures';

require 'vendor/deployer/deployer/recipe/symfony.php';
require '.deployer/functions.php';
require '.deployer/wf.php';

// Project name
set('application', '{{ project_name | default('???') }}');

// Project repository
set('repository', '{{ remote_url | default('???') }}');

// [Optional] Allocate tty for git clone. Default value is false.
//set('git_tty', true);

// Timout beállítás
set('default_timeout', 600);

// Shared files/dirs between deploys
add('shared_files', [
    '.wf.yml',
]);
/**
 * Nem lehet symlink a Dockerfile! Valójában a `shared_files`-ben lenne a helye, de sajnos a docker nem tud azzal működni:
 * @see https://github.com/docker/compose/issues/5315
 */
//add('copy_dirs', [
//    '.docker/engine/Dockerfile',
//]);
add('shared_dirs', [
    'web/var',
    '.wf/.data',
]);
add('dist_files', [
    'app/config/parameters.yml',
    '.wf.yml',
]);

// Writable dirs by web server
add('writable_dirs', [
    'web/var'
]);

inventory(__DIR__ . '/.deployer/hosts.yml');

// ============================== W O R K F L O W ================================
task('deploy:init-config', function () {
    cd('{{ "{{release_path}}" }}');
    foreach (get('dist_files') as $distFile) {
        if (!test(sprintf('[[ -f %s ]]', $distFile))) {
            run(sprintf('cp %1$s.dist %1$s', $distFile));
        }
    }

    if (get('workflow_project_config', false)) {
        $configPath = sys_get_temp_dir() . '/workflow_project_config.project.yml';
        $content = get('workflow_project_config');
        file_put_contents($configPath, $content);
        upload($configPath, '{{ "{{release_path}}" }}/.wf.yml');
    }
});
before('deploy:shared', 'deploy:init-config');

task('deploy:wf', function () {
    cd('{{ "{{release_path}}" }}');
    writeln('Start init...');
    run('{{ "{{wf}}" }} init');
    run('{{ "{{wf}}" }} restart');
    writeln('...Init is ready');
    writeln('Start install... (it would be long)');
    // A lassú futás miatt a timeout-ot megnöveljük
    run('{{ "{{wf}}" }} install', [
        'timeout' => 1200,
    ]);
})->onRoles(ROLE_WORKFLOW);
after('deploy:shared', 'deploy:wf');

// ============================== D E F A U L T ================================
// Migrate database before symlink new release.
task('database:build', function () {
    sf('doctrine:database:create','--if-not-exists');
{% if is_ez %}
    // Csak akkor futtatjuk az ezplatform:install-t ha még nem létezik az adatbázis.
    run (sprintf(
        '%s || %s',
        buildSfCommand('doctrine:schema:validate', '--skip-mapping'),
        buildSfCommand('ezplatform:install', 'app')
    ));
{% endif %}
    sf('doctrine:migrations:migrate','--allow-no-migration');
{% if is_ez %}
    // A -u azért kell, hogy ne transaction-ben fusson, különben nem működnek a references dolgok
    sf('kaliop:migration:migrate','-u --default-language=hun-HU');
{% endif %}
    //sf('doctrine:fixtures:load');
})
    ->desc('Build database.')
    ->onRoles(ROLE_DEFAULT)
;
before('deploy:symlink', 'database:build');

task('database:reload', function() {
    cd('{{ "{{release_path}}" }}');
    run('{{ "{{wf}}" }} dbreload --full');
})
    ->desc('Load the fixtures')
    ->onRoles(ROLE_FIXTURE_RELOAD)
;
after('deploy:wf', 'database:reload');

// Only SF
$onlyDefaultTasks = [
    'deploy:assets',
    'deploy:vendors',
    'deploy:assets:install',
    'deploy:assetic:dump',
    'deploy:cache:clear',
    'deploy:cache:warmup',
    'database:migrate',
];
foreach ($onlyDefaultTasks as $task) {
    task($task)->onRoles(ROLE_DEFAULT);
}

// ============================== B U I L D ================================
task('deploy:build:files-clean', function() {
    cd('{{ "{{current_path}}" }}');
    // Írható könyvtárak törlése
    foreach (get('writable_dirs') as $dir) {
        run(sprintf('rm -rf %s/*', $dir));
    }
    // .gitignore adatok törlése
    $gitignoreFiles = [
        '.git',
        '.deployer',
        '.docker',
        'bin',
        'app/check.php',
        'app/SymfonyRequirements.php',
        'ide-twig.json',
    ];
    foreach ($gitignoreFiles as $file) {
        run(sprintf('rm -rf %s', $file));
    }

    // A paramters.yml ürítése
    run('echo "" > app/config/parameters.yml');
})
    ->desc('Törli a shared és writable fájlokat és könyvtárakat, amiket nem szeretnénk bezippelni.')
    ->setPrivate()
    ->onRoles(ROLE_BUILD);

task('deploy:build:add-composer', function() {
    $content = file_get_contents('https://getcomposer.org/composer.phar');
    $tmpFilePath = sys_get_temp_dir() . '/release_' . get('release_name') . '.composer.phar';
    file_put_contents($tmpFilePath, $content);
    upload($tmpFilePath, '{{ "{{current_path}}" }}/composer.phar');
    unlink($tmpFilePath);
});

task('deploy:build:update-version-number', function() {
    cd('{{ "{{current_path}}" }}');
    set('git_version_tag', function() {
        return runLocally('git describe --tags --always');
    });
    $yml = <<<EOS
parameters:
    app.version: {{ "{{git_version_tag}}" }}
EOS;
    set('version_yml_content', $yml);
    run('echo "{{ "{{version_yml_content}}" }}" > app/config/version_info.yml');
})
    ->desc('Frissíti a verzió számot.')
    ->setPrivate()
    ->onRoles(ROLE_BUILD);

task('deploy:build:create-zip', function() {
    cd('{{ "{{current_path}}" }}');
    run('tar -zchvf {{ "{{deploy_path}}" }}/{{ "{{release_name}}" }}.tar.gz .');
})
    ->desc('Zippeli a release-t.')
    ->onRoles(ROLE_BUILD);

task('deploy:build:create-makefile', function() {
    $content = file_get_contents(__DIR__ . '/.deployer/tpl/makefile');
    $configs = ['shared_dirs', 'shared_files', 'writable_dirs'];
    foreach ($configs as $configName) {
        $content = str_replace('%' . $configName . '%', implode(' ', get($configName, [])), $content);
    }
    $tmpFilePath = sys_get_temp_dir() . '/release_' . get('release_name') . '.makefile';
    file_put_contents($tmpFilePath, $content);
    upload($tmpFilePath, '{{ "{{deploy_path}}" }}/makefile');
    unlink($tmpFilePath);
})
    ->desc('Elkészíti a makefile-t a zip-hez.')
    ->setPrivate()
    ->onRoles(ROLE_BUILD);

task('deploy:build', [
    'deploy:build:files-clean',
    'deploy:build:update-version-number',
    'deploy:build:add-composer',
    'deploy:build:create-zip',
    'deploy:build:create-makefile',
])->onRoles(ROLE_BUILD);
after('cleanup', 'deploy:build');

// ============================== O T H E R ================================
// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
