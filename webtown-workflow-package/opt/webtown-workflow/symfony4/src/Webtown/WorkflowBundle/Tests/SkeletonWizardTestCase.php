<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.15.
 * Time: 13:39
 */

namespace App\Webtown\WorkflowBundle\Tests;

use App\Webtown\WorkflowBundle\Tests\Dummy\Builder\AbstractWizardBuilder;
use App\Webtown\WorkflowBundle\Tests\Dummy\Filesystem;
use App\Webtown\WorkflowBundle\Wizards\BaseSkeletonWizard;

class SkeletonWizardTestCase extends TestCase
{
//    public function test()
//    {
//        $wizardBuilder = new AbstractWizardBuilder();
//        $wizardBuilder->setAnswers($answers);
//        /** @var BaseSkeletonWizard $wizard */
//        $wizard = $wizardBuilder->build();
//        $wizard->runBuild("test");
//
//        $this->assertFilesEquals($directory, $wizardBuilder->getFilesystem(), "test");
//    }

    public function assertFilesEquals(string $directory, Filesystem $filesystem, string $workingDirectory)
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
}
