<?php

namespace Tisseo\BOABundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use CanalTP\SamCoreBundle\DataFixtures\ORM\CustomerTrait;

class FixturesCustomer extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    use CustomerTrait;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $om)
    {
        $navitiaToken = $this->container->getParameter('nmm.navitia.token');
        $samFixturePerimeters = $this->container->getParameter('sam_fixture_perimeters');
        $this->createCustomer($om, 'Tisseo', 'nmm-ihm@tisseo.fr', 'tisseo');

        $this->addCustomerToApplication($om, 'app-boa', 'customer-tisseo', $navitiaToken);

        foreach($samFixturePerimeters as $key => $value) {
            $this->addPerimeterToCustomer($om, $value['coverage'], $value['network'], 'customer-tisseo');
        }
        $om->flush();
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 3;
    }

}
