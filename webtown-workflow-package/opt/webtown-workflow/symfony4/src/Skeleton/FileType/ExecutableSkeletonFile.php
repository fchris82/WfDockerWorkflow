<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.24.
 * Time: 22:01
 */

namespace App\Skeleton\FileType;

class ExecutableSkeletonFile extends SkeletonFile
{
    /**
     * @var int
     */
    protected $permission = 0755;

    /**
     * @return int
     */
    public function getPermission()
    {
        return $this->baseFileInfo->isExecutable() ? $this->baseFileInfo->getPerms() : $this->permission;
    }

    /**
     * @param int $permission
     *
     * @return $this
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;

        return $this;
    }
}
