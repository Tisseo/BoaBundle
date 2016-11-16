<?php

namespace Tisseo\BoaBundle\Permission;

use CanalTP\SamEcoreApplicationManagerBundle\Permission\AbstractBusinessPermissionModule;

class BusinessPermissionModule extends AbstractBusinessPermissionModule
{
    public function getName()
    {
        return 'boa_business_module';
    }
}
