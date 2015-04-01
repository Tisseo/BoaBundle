<?php

namespace Tisseo\BoaBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use CanalTP\SamCoreBundle\DataFixtures\ORM\UserTrait;

class FixturesUser extends AbstractFixture implements OrderedFixtureInterface
{
    use UserTrait;

    private $users = array(
        array(
            'id'        => null,
            'username'  => 'utilisateur Boa',
            'firstname' => 'utilisateur',
            'lastname'  => 'Boa',
            'email'     => 'user-boa@tisseo.fr',
            'password'  => 'tid',
            'roles'     => array('role-user-boa'),
            'customer'  => 'customer-tisseo'
        ),
        array(
            'id'        => null,
            'username'  => 'admin Boa',
            'firstname' => 'admin',
            'lastname'  => 'Boa',
            'email'     => 'admin-boa@tisseo.fr',
            'password'  => 'admin',
            'roles'     => array('role-admin-boa'),
            'customer'  => 'customer-tisseo'
        )
    );

    public function load(ObjectManager $om)
    {
        foreach ($this->users as $userData) {
            $userEntity = $this->createUser($om, $userData);
        }
        $om->flush();
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 5;
    }
}
