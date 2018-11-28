<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.15.
 * Time: 11:51
 */

namespace Wizards\DockerProject;
use App\Event\SkeletonBuild\DumpFileEvent;
use App\Skeleton\FileType\SkeletonFile;
use App\Wizard\WizardInterface;
use Wizards\Docker\BaseDocker;

/**
 * Class DockerProject.
 *
 * Lapos könyvtárstruktúrát húz rá a projektre.
 *
 * <code>
 *  DockerProject
 *  ├── [...]
 *  │
 *  └── .wf.yml.dist
 * </code>
 */
class DockerProjectWizard extends BaseDocker implements WizardInterface
{
    /**
     * Az itt visszaadott fájllal ellenőrizzük, hogy az adott dekorátor lefutott-e már.
     * <code>
     *  protected function getBuiltCheckFile() {
     *      return '.docker';
     *  }
     * </code>.
     *
     * @return string
     */
    protected function getBuiltCheckFile()
    {
        return '.wf.yml.dist';
    }

    /**
     * Itt kérjük be az adatokat a felhasználótól, ami alapján létrehozzuk a végső fájlokat.
     */
    protected function addVariables($targetProjectDirectory, $variables)
    {
        $defaults = [
            'project_directory'     => '.',
            'deploy_directory'      => '.',
            'project_name'          => basename($this->getEnv('ORIGINAL_PWD', $targetProjectDirectory)),
            'current_wf_version'    => $this->input->getOption('wf-version'),
        ];

        return array_merge($variables, $defaults);
    }

    protected function eventBeforeDumpTargetExists(DumpFileEvent $event)
    {
        parent::eventBeforeDumpTargetExists($event);

        switch (strtolower($event->getSkeletonFile()->getBaseFileInfo()->getFilename())) {
            case '.gitignore':
            case 'readme.md':
                $event->getSkeletonFile()->setHandleExisting(SkeletonFile::HANDLE_EXISTING_APPEND);
                break;
        }
    }

    public function getDefaultName()
    {
        return 'Docker Project';
    }

    public function getInfo()
    {
        return 'Create a dockered project';
    }

    public function getDefaultGroup()
    {
        return 'WF';
    }

    protected function build($targetProjectDirectory)
    {
        // TODO: Implement build() method.
    }
}
