<?php

namespace Tisseo\BoaBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use CanalTP\SamCoreBundle\DataFixtures\ORM\RoleTrait;

class FixturesRole extends AbstractFixture implements OrderedFixtureInterface
{
    use RoleTrait;

    private $roles = array(
        array(
            'name' => 'Utilisateur Boa',
            'reference' => 'user-boa',
            'application' => 'app-boa',
            'isEditable' => true,
            'permissions' => array(
                'BUSINESS_VIEW_CONFIGURATION',
                'BUSINESS_VIEW_ROUTES',
                'BUSINESS_VIEW_CALENDARS',
                'BUSINESS_VIEW_STOPS',
            )
        ),
        array(
            'name' => 'Administrateur Boa',
            'reference' => 'admin-boa',
            'application' => 'app-boa',
            'isEditable' => true,
            'permissions' => array(
                'BUSINESS_MANAGE_CONFIGURATION',
                'BUSINESS_MANAGE_ROUTES',
                'BUSINESS_MANAGE_CALENDARS',
                'BUSINESS_MANAGE_STOPS',
            )
        )
    );

    public function load(ObjectManager $om)
    {
        foreach ($this->roles as $role) {
            $this->createApplicationRole($om, $role);
        }
        $om->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 2;
    }
}
