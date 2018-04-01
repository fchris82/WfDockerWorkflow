<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.24.
 * Time: 22:01
 */

namespace App\Skeleton;

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
        return $this->permission;
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