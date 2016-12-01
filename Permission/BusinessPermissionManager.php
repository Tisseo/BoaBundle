<?php

namespace Tisseo\BoaBundle\Permission;

use CanalTP\SamEcoreApplicationManagerBundle\Permission\AbstractBusinessPermissionManager;

class BusinessPermissionManager extends AbstractBusinessPermissionManager
{
    private $businessModule;

    public function __construct($businessModule)
    {
        $this->businessModule = $businessModule;
    }

    public function getBusinessModules()
    {
        return $this->businessModule;
    }
}
