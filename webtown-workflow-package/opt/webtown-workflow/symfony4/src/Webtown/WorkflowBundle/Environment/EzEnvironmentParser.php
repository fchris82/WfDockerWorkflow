<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.30.
 * Time: 15:21
 */

namespace App\Webtown\WorkflowBundle\Environment;

use App\Webtown\WorkflowBundle\Exception\InvalidComposerVersionNumber;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class EzEnvironmentParser extends SymfonyEnvironmentParser
{
    public function isEzProject($workingDirectory)
    {
        $ezVersion = false;
        $kaliopVersion = false;
        $ezYmlExists = file_exists($workingDirectory . '/.ez.yml');
        try {
            $ezVersion = $this->composerParser->get(
                $workingDirectory,
                'ezsystems/ezpublish-kernel'
            );
            $kaliopVersion = $this->composerParser->get(
                $workingDirectory,
                'kaliop/ezmigrationbundle'
            );
        } catch (InvalidComposerVersionNumber $e) {
            return true;
        } catch (FileNotFoundException $e) {
            return false;
        }

        return $ezVersion || $kaliopVersion || $ezYmlExists;
    }

    public function getSymfonyEnvironmentVariables($projectWorkDir)
    {
        $variables = parent::getSymfonyEnvironmentVariables($projectWorkDir);
        $variables['is_ez'] = $this->isEzProject($projectWorkDir);

        return $variables;
    }
}
