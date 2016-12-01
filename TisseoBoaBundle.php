<?php

namespace Tisseo\BoaBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use CanalTP\SamEcoreApplicationManagerBundle\SamApplication;

class TisseoBoaBundle extends Bundle implements SamApplication
{
    public function getCanonicalName()
    {
        return 'boa';
    }
}
