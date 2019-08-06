<?php declare(strict_types=1);

namespace Wf\DemoExtension\Wizards\Demo;

use Symfony\Component\Console\Question\Question;
use Wf\DockerWorkflowBundle\Event\Wizard\BuildWizardEvent;
use Wf\DockerWorkflowBundle\Wizards\BaseSkeletonWizard;

class DemoWizard extends BaseSkeletonWizard
{
    public function getDefaultName(): string
    {
        return 'Demo Extension';
    }

    public function getInfo(): string
    {
        return 'Add demo.txt file to the project.';
    }

    public function getDefaultGroup(): string
    {
        return 'DEMO';
    }

    protected function getBuiltCheckFile(): string
    {
        return 'demo.txt';
    }

    protected function build(BuildWizardEvent $event): void
    {
        // do nothing
    }

    protected function readSkeletonVars(BuildWizardEvent $event): array
    {
        $extraContentQuestion = new Question('Give me a short content: [<info>Lorem ipsum</info>] ', 'Lorem ipsum');
        $extraContent = $this->ask($extraContentQuestion);

        return ['content' => $extraContent];
    }
}
