<?php

namespace Tisseo\BoaBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use CanalTP\SamEcoreUserManagerBundle\Entity\User;
use CanalTP\SamCoreBundle\DataFixtures\ORM\RoleTrait;

class FixturesRole extends AbstractFixture implements OrderedFixtureInterface
{
    use RoleTrait;

    private $roles = array(
        array(
            'name'          => 'User Boa',
            'reference'     => 'user-boa',
            'application'   => 'app-boa',
            'isEditable'    => true,
            'permissions'   => array(
                'BUSINESS_MANAGE_ROUTES',
                'BUSINESS_VIEW_CONFIGURATION',
                'BUSINESS_VIEW_STOPS',
                'BUSINESS_MANAGE_CALENDARS',
                'BUSINESS_MANAGE_DAY_CALENDARS',
                'BUSINESS_MANAGE_PERIOD_CALENDARS',
                'BUSINESS_MANAGE_MIXED_CALENDARS',
                'BUSINESS_MANAGE_BRICK_CALENDARS',
            )
        ),
        array(
            'name'          => 'Consult Boa',
            'reference'     => 'consult-boa',
            'application'   => 'app-boa',
            'isEditable'    => true,
            'permissions'   => array(
                'BUSINESS_VIEW_CONFIGURATION',
                'BUSINESS_VIEW_STOPS',
            )
        ),
        array(
            'name'          => 'Admin Boa',
            'reference'     => 'admin-boa',
            'application'   => 'app-boa',
            'isEditable'    => true,
            'permissions'  => array(
                'BUSINESS_MANAGE_CONFIGURATION',
                'BUSINESS_MANAGE_ROUTES',
                'BUSINESS_MANAGE_CALENDARS',
                'BUSINESS_MANAGE_DAY_CALENDARS',
                'BUSINESS_MANAGE_PERIOD_CALENDARS',
                'BUSINESS_MANAGE_ACCESSIBILITY_CALENDARS',
                'BUSINESS_MANAGE_MIXED_CALENDARS',
                'BUSINESS_MANAGE_BRICK_CALENDARS',
                'BUSINESS_MANAGE_STOPS',
            )
        )
    );

    public function load(ObjectManager $om)
    {
         foreach ($this->roles as $role) {
            $this->createApplicationRole($om,  $role);
        }
        $om->flush();
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 2;
    }
}
