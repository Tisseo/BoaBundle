<?php

namespace Tisseo\BoaBundle\Event;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Tisseo\EndivBundle\Entity\StopHistory;
use Tisseo\EndivBundle\Entity\Log;
/**
* Class BoaLogSubscriber
*/
class BoaLogSubscriber implements EventSubscriber
{
    private $container;
    private $user;
    private $accessor;
    protected $logs = [];
    /**
    * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
    */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }
    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'preRemove',
            'onFlush',
            'preUpdate',
            'postFlush'
        );
    }
    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        foreach ($uow->getScheduledEntityInsertions() AS $entity) {
            if ($entity instanceof StopHistory) {
                $this->log($entity, 'insert');
            }
        }
    }
    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $this->log($args->getEntity(), 'delete');
    }
    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $changeSet = $args->getEntityManager()
                                            ->getUnitOfWork()
                                            ->getEntityChangeSet($entity);
        $this->log($entity, 'update', $changeSet);
    }
    /**
     * @param LifecycleEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $event)
    {
        if(!empty($this->logs)) {
            $em = $event->getEntityManager();
            foreach ($this->logs as $log) {
                $em->persist($log);
            }
            $this->logs = [];
            $em->flush();
        }
    }
    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    private function log($entity, $action, $changeSet = null)
    {
        if ($entity instanceof StopHistory) {
            $this->logs[] = $this->setStopHistoryLog($entity, $action, $changeSet);
        }
    }
    /**
     * @return Log
     */
     private function setStopHistoryLog($entity, $action, $changeSet = null)
    {
        if (empty($this->user)) {
            $this->user = $this->container->get('security.context')->getToken();
        }
        //\Doctrine\Common\Util\Debug::dump($changeSet, 3);

        $Log = new Log();
        $Log->setDatetime(new \DateTime());
        $Log->setTable("stop_history");
        $Log->setUser($this->user->getUsername());
        $Log->setAction($action);

        $strEntity = $this->stopHistoryToString($entity);

        switch ($action) {
            case 'delete':
                $Log->setPreviousData($strEntity);
                $Log->setInsertedData("");
                break;
            case 'update':
                $oldEntity = clone $entity;
                $newEntity = clone $entity;
                foreach ($changeSet as $field => $values) {
                    $this->accessor->setValue($oldEntity, $field, $values[0]);
                    $this->accessor->setValue($newEntity, $field, $values[1]);
                }
                $Log->setPreviousData($this->stopHistoryToString($oldEntity));
                $Log->setInsertedData($this->stopHistoryToString($newEntity));
                break;
            case 'insert':
                $Log->setPreviousData("");
                $Log->setInsertedData($strEntity);
                break;
            default:
                $Log->setPreviousData("");
                $Log->setInsertedData("");
        }

        return $Log;
    }
    /**
     * @return string
     */
     private function stopHistoryToString($entity)
     {
        $strEntity = $entity->getId().";";
        $strEntity .= $entity->getStop()->getId().";";
        if( $entity->getStartDate() )
            $strEntity .= $entity->getStartDate()->format('d/m/Y');
        $strEntity .= ";";
        if( $entity->getEndDate() )
            $strEntity .= $entity->getEndDate()->format('d/m/Y');
        $strEntity .= ";";
        $strEntity .= $entity->getShortName().";";
        $strEntity .= $entity->getLongName().";";
        $strEntity .= $entity->getTheGeom().";";

        return $strEntity;
     }
}