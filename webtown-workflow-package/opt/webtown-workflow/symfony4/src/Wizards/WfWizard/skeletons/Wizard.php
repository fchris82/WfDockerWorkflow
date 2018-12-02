<?php

namespace App\Wizards\{{ namespace }};

{% if parent_wizard == 'BaseSkeletonWizard' %}
use App\Event\SkeletonBuild\DumpFileEvent;
use App\Event\SkeletonBuild\PostBuildSkeletonFileEvent;
use App\Event\SkeletonBuild\PostBuildSkeletonFilesEvent;
use App\Event\SkeletonBuild\PreBuildSkeletonFileEvent;
use App\Event\SkeletonBuild\PreBuildSkeletonFilesEvent;
{% endif %}
use App\Event\Wizard\BuildWizardEvent;
use App\Wizards\{{ parent_wizard }};

class {{ wizard_class }} extends {{ parent_wizard }}
{
    public function getDefaultName()
    {
        return '{{ name }}';
    }

    public function getDefaultGroup()
    {
        return '{{ group }}';
    }

    public function getInfo()
    {
        return ''; // @todo
    }

    protected function build($targetProjectDirectory)
    {
        // @todo
    }

    // @todo
    protected function init(BuildWizardEvent $event)
    {
    }

    // @todo
    protected function cleanUp(BuildWizardEvent $event)
    {
    }

{% if parent_wizard == 'BaseSkeletonWizard' %}
    // @todo or delete
    protected function eventBeforeBuildFiles(PreBuildSkeletonFilesEvent $event)
    {
        parent::eventBeforeBuildFiles($event);
    }

    // @todo or delete
    protected function eventBeforeBuildFile(PreBuildSkeletonFileEvent $event)
    {
        parent::eventBeforeBuildFile($event);
    }

    // @todo or delete
    protected function eventAfterBuildFile(PostBuildSkeletonFileEvent $event)
    {
        parent::eventAfterBuildFile($event);
    }

    // @todo or delete
    protected function eventAfterBuildFiles(PostBuildSkeletonFilesEvent $event)
    {
        parent::eventAfterBuildFiles($event);
    }

    // @todo or delete
    protected function eventBeforeDumpFile(DumpFileEvent $event)
    {
        parent::eventBeforeDumpFile($event);
    }

    // @todo or delete
    protected function eventBeforeDumpTargetExists(DumpFileEvent $event)
    {
        parent::eventBeforeDumpTargetExists($event);
    }

    // @todo or delete
    protected function eventAfterDumpFile(DumpFileEvent $event)
    {
        parent::eventAfterDumpFile($event);
    }

    // @todo or delete
    protected function eventSkipDumpFile(DumpFileEvent $event)
    {
        parent::eventSkipDumpFile($event);
    }
{% endif %}
}
