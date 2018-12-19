<?php

namespace App\Webtown\WorkflowBundle;

use App\Webtown\WorkflowBundle\DependencyInjection\Compiler\CollectExtensionInstallersPass;
use App\Webtown\WorkflowBundle\DependencyInjection\Compiler\TwigExtendingPass;
use App\Webtown\WorkflowBundle\Recipes\BaseRecipe;
use App\Webtown\WorkflowBundle\Wizard\WizardInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class WebtownWorkflowBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(BaseRecipe::class)
            ->addTag('wf.recipe');
        $container->registerForAutoconfiguration(WizardInterface::class)
            ->addTag('wf.wizard');

        $container->addCompilerPass(new TwigExtendingPass());
        $container->addCompilerPass(new CollectExtensionInstallersPass());
    }
}
