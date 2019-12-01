<?php declare(strict_types=1);

namespace WfMonorepo\MonorepoBuilder\Release;

use PharIo\Version\Version;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;

class ChainWorker implements ReleaseWorkerInterface
{

    /**
     * 1 line description of what this worker does, in a commanding form! e.g.:
     * - "Add new tag"
     * - "Dump new items to CHANGELOG.md"
     * - "Run coding standards"
     */
    public function getDescription(Version $version): string
    {
        return "Start Wf Chain Worker ...";
    }

    /**
     * Higher first
     */
    public function getPriority(): int
    {
        // TODO: Implement getPriority() method.
    }

    public function work(Version $version): void
    {
        // TODO: Implement work() method.
    }
}
