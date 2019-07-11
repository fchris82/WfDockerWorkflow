<?php

declare(strict_types=1);

namespace App\Webtown\WorkflowBundle;

use App\Webtown\WorkflowBundle\DependencyInjection\Compiler\CollectRecipesPass;
use App\Webtown\WorkflowBundle\DependencyInjection\Compiler\CollectWizardsPass;
use App\Webtown\WorkflowBundle\Recipes\BaseRecipe;
use App\Webtown\WorkflowBundle\Wizard\WizardInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class WebtownWorkflowBundle extends Bundle
{
    const RECIPE_TAG = 'wf.recipe';
    const WIZARD_TAG = 'wf.wizard';

    public function build(ContainerBuilder $container)
    {
        // Register autoconfigurations
        $container->registerForAutoconfiguration(BaseRecipe::class)
            ->addTag(static::RECIPE_TAG);
        $container->registerForAutoconfiguration(WizardInterface::class)
            ->addTag(static::WIZARD_TAG);

        // Register collect passes
        $container->addCompilerPass(new CollectRecipesPass());
        $container->addCompilerPass(new CollectWizardsPass());
    }
}
