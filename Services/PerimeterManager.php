<?php

/**
 * Description of Network
 *
 * @author rabikhalil
 */
namespace Tisseo\BoaBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\NmmPortalBundle\Services\PerimeterManager as NmmPerimeterManager;

class PerimeterManager extends NmmPerimeterManager
{
    private $om = null;
    private $repository = null;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repository = $om->getRepository('CanalTPNmmPortalBundle:Perimeter');
    }

    public function findAll()
    {
        return ($this->repository->findAll());
    }

    public function find($id)
    {
        return ($id ? $this->perimeterManager->find($id) : null);
    }
}
