<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.06.
 * Time: 16:47.
 */

namespace AppBundle\Wizard\Docker;

use AppBundle\Wizard\BaseChainWizard;
use AppBundle\Wizard\Docker\Wide\CreateEnvironmentsSkeleton;
use AppBundle\Wizard\Docker\Wide\MoveProjectFiles;
use AppBundle\Wizard\Helper\CheckGitUncommittedChangesForChain;
use AppBundle\Wizard\Helper\GitCommitWizardForChain;
use AppBundle\Wizard\PublicWizardInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Wide.
 *
 * Szeparált könyvtárstruktúrát alakít ki, ahol külön van a SF projekt, külön van a deploy és a docker fájlok:
 * <code>
 *  DockerProjectWide
 *  ├── deploy          <-- Deploy fájlok
 *  │   ├── composer.json
 *  │   ├── composer.lock
 *  │   ├── .gitignore
 *  │   └── README.md
 *  │
 *  ├── equipment       <-- Docker fájlok
 *  │   ├── .data
 *  │   │   └── .gitkeep
 *  │   └── dev
 *  │       ├── engine
 *  │       │   ├── config
 *  │       │   │   └── php_requires.txt
 *  │       │   ├── Dockerfile.dist
 *  │       │   ├── .gitignore
 *  │       │   └── entrypoint.sh
 *  │       ├── nginx
 *  │       │   └── vhost.conf
 *  │       │
 *  │       ├── docker-compose.local.yml.dist
 *  │       └── docker-compose.yml
 *  │
 *  ├── project         <-- Project fájl
 *  │   └── [...]
 *  │
 *  └── .gitignore
 * </code>
 *
 * @deprecated Az ötletet először a EZ-től vettem, de aztán a tapasztalat azt mutatta, hogy ez az irány nem túl használható azzal, hogy a projekt a project könyvtárba kerül. Pl a PHPStorm fel sem ismeri így a Symfony projekteket. Környezet kialakításához pedig használd inkább a DevEnvironemnt osztályt.
 */
class Wide extends BaseChainWizard implements PublicWizardInterface
{
    /**
     * Skeletons base dir.
     *
     * @var string
     */
    protected $baseDir;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * BaseSkeleton constructor.
     *
     * @param string            $baseDir
     * @param \Twig_Environment $twig
     * @param Filesystem        $filesystem
     */
    public function __construct($baseDir, \Twig_Environment $twig, Filesystem $filesystem)
    {
        $this->baseDir = $baseDir;
        $this->twig = $twig;
        $this->filesystem = $filesystem;
    }

    protected function getWizardNames()
    {
        return [
            new CheckGitUncommittedChangesForChain($this->input, $this->output, $this->command),
            new MoveProjectFiles($this->filesystem),
            new GitCommitWizardForChain('Move project files', $this->input, $this->output, $this->command),
            new CreateEnvironmentsSkeleton($this->baseDir, $this->twig, $this->filesystem),
        ];
    }

    public function getName()
    {
        return 'Docker - Wide';
    }

    public function getInfo()
    {
        return 'Create a wide docker project';
    }
}
