<?php

namespace AppBundle;

use AppBundle\DependencyInjection\Compiler\CollectWizardsPass;
use AppBundle\DependencyInjection\Compiler\TwigExtendingPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CollectWizardsPass());
        $container->addCompilerPass(new TwigExtendingPass());
    }
}
