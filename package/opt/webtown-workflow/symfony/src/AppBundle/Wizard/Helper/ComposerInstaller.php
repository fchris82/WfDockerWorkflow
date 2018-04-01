<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.08.
 * Time: 16:54.
 */

namespace AppBundle\Wizard\Helper;

use Symfony\Component\Console\Output\OutputInterface;

class ComposerInstaller
{
    const COMPOSER_NODEV = 'nodev';
    const COMPOSER_DEV = 'dev';

    public static function installComposerPackages($targetProjectDirectory, $composerPackages, OutputInterface $output)
    {
        if (array_key_exists(self::COMPOSER_NODEV, $composerPackages)) {
            self::runComposerRequire($targetProjectDirectory, $composerPackages[self::COMPOSER_NODEV], $output);
        }
        if (array_key_exists(self::COMPOSER_DEV, $composerPackages)) {
            self::runComposerRequire($targetProjectDirectory, $composerPackages[self::COMPOSER_DEV], $output, ['--dev']);
        }
    }

    /**
     * @param $targetProjectDirectory
     * @param array           $packages
     * @param OutputInterface $output
     * @param array           $options
     */
    protected static function runComposerRequire($targetProjectDirectory, array $packages, OutputInterface $output, array $options = [])
    {
        $packages = trim(implode(' ', $packages));
        if ($packages) {
            $output->writeln('<info>Start composer require command ...</info> (' . $packages . ')');
            exec(sprintf(
                'cd %s && composer require %s %s',
                $targetProjectDirectory,
                implode(' ', $options),
                $packages
            ));
        }
    }
}
