<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.26.
 * Time: 17:12
 */

namespace App\Webtown\WorkflowBundle\Event;

use App\Webtown\WorkflowBundle\Event\SkeletonBuild\DumpFileEvent;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\PostBuildSkeletonFileEvent;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\PostBuildSkeletonFilesEvent;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\PreBuildSkeletonFileEvent;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\PreBuildSkeletonFilesEvent;

class SkeletonBuildBaseEvents
{
    /**
     * @see PreBuildSkeletonFilesEvent
     */
    const BEFORE_BUILD_FILES = 'app.skeleton.before_build_skeleton_files';
    /**
     * @see PostBuildSkeletonFilesEvent
     */
    const AFTER_BUILD_FILES = 'app.skeleton.after_build_skeleton_files';

    /**
     * @see PreBuildSkeletonFileEvent
     */
    const BEFORE_BUILD_FILE = 'app.skeleton.before_build_skeleton_file';
    /**
     * @see PostBuildSkeletonFileEvent
     */
    const AFTER_BUILD_FILE = 'app.skeleton.after_build_skeleton_file';

    /**
     * @see DumpFileEvent
     */
    const BEFORE_DUMPS = 'app.skeleton.before_dump';
    const BEFORE_DUMP_FILE = 'app.skeleton.before_dump_skeleton_file';
    const BEFORE_DUMP_TARGET_EXISTS = 'app.skeleton.before_dump_target_exists';
    const AFTER_DUMP_FILE = 'app.skeleton.after_dump_skeleton_file';
    const SKIP_DUMP_FILE = 'app.skeleton.skip_dump_skeleton_file';
}
