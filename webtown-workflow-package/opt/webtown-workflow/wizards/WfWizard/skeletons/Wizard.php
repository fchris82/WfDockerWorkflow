<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.22.
 * Time: 13:32
 */

namespace Wizards\{{ namespace }};

use Wizards\{{ parent_wizard }};

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
}
