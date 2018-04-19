<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.13.
 * Time: 21:46
 */

namespace App\Tests\Tool;

use App\DependencyInjection\Compiler\TwigExtendingPass;
use App\Wizard\WizardInterface;
use App\Tests\Dummy\Command;
use App\Tests\Dummy\Filesystem;
use App\Tests\Dummy\Input;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tests\Fixtures\DummyOutput;

class BaseSkeletonTestCase extends TestCase
{
    protected function initSkeleton(WizardInterface $skeleton, array $responses = [])
    {
        $command = new Command(get_class($skeleton));
        $command->setQuetionResponses($responses);
        $skeleton
            ->setCommand($command)
            ->setInput(new Input())
            ->setOutput(new DummyOutput())
        ;
    }

    protected function compareResults($resultDir, $alias, Filesystem $filesystem)
    {
        $resultFilesystem = new Filesystem($resultDir, $alias);
        $results = $filesystem->getContents();
        ksort($results);
        $responses = $resultFilesystem->getContents();
        ksort($responses);

        $this->assertEquals(
            $responses,
            $results,
            "\e[31mThere are some differencis between directories. The \e[1;97m+\e[0;31m sign is a unnecessary file, the \e[1;97m-\e[0;31m sign is a missing file.\e[0m"
        );

        foreach ($resultFilesystem->getContents() as $file => $content) {
            $this->assertEquals($results[$file], $content, sprintf('The `%s` file contents are different!', $file));
        }
    }

    protected function getTwig($path = null)
    {
        if (is_null($path)) {
            $path = $this->getBaseDir();
        }

        $loader = new \Twig_Loader_Filesystem($path);
        $loader->addPath($this->getBaseDir(), TwigExtendingPass::SKELETON_TWIG_NAMESPACE);
        $twig = new \Twig_Environment($loader);

        return $twig;
    }

    protected function getBaseDir()
    {
        return __DIR__ . '/../../../skeletons';
    }
}
