<?php

namespace DoS\ResourceBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use DoS\ResourceBundle\Model\UniqueStateAbleInterface;

class UniqueStateAbleListener implements EventSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function prePersist(LifecycleEventArgs $event)
    {
        $object = $event->getObject();

        if ($object instanceof UniqueStateAbleInterface && $object->getUniqueState()) {
            $event->getEntityManager()->createQueryBuilder()
                ->update(get_class($object), 'o')
                ->set('o.' . $object->getUniqueStateField(), ':update')
                ->where('o.' . $object->getUniqueStateField() . ' = :where')
                ->setParameter('update', false)
                ->setParameter('where', true)
                ->getQuery()->execute()
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(LifecycleEventArgs $event)
    {
        $this->prePersist($event);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate',
        );
    }
}
