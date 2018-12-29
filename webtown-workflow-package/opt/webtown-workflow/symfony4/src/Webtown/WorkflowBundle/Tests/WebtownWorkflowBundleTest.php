<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.27.
 * Time: 17:03
 */

namespace App\Webtown\WorkflowBundle\Tests;

use App\Webtown\WorkflowBundle\DependencyInjection\Compiler\CollectRecipesPass;
use App\Webtown\WorkflowBundle\DependencyInjection\Compiler\CollectWizardsPass;
use App\Webtown\WorkflowBundle\Recipes\BaseRecipe;
use App\Webtown\WorkflowBundle\WebtownWorkflowBundle;
use App\Webtown\WorkflowBundle\Wizard\WizardInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class WebtownWorkflowBundleTest extends \PHPUnit\Framework\TestCase
{
    public function testBuild()
    {
        $bundle = new WebtownWorkflowBundle();
        $containerBuilder = new ContainerBuilder();
        $bundle->build($containerBuilder);

        $autoConfiguredInstanceof = $containerBuilder->getAutoconfiguredInstanceof();
        $this->assertEquals([
            BaseRecipe::class,
            WizardInterface::class,
        ], array_keys($autoConfiguredInstanceof));

        $tags = [];
        foreach ($autoConfiguredInstanceof as $interface => $childDefinition) {
            $tags = array_merge($tags, $childDefinition->getTags());
        }
        $this->assertEquals([
            'wf.recipe',
            'wf.wizard',
        ], array_keys($tags));

        $passConfig = $containerBuilder->getCompilerPassConfig();
        $passes = $passConfig->getBeforeOptimizationPasses();
        $passClasses = [];
        foreach ($passes as $compilerPass) {
            $passClasses[] = \get_class($compilerPass);
        }

        $this->assertTrue(\in_array(CollectRecipesPass::class, $passClasses));
        $this->assertTrue(\in_array(CollectWizardsPass::class, $passClasses));
    }
}