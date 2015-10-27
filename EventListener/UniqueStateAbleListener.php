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
    public function postPersist(LifecycleEventArgs $event)
    {
        $object = $event->getObject();

        if ($object instanceof UniqueStateAbleInterface && $object->getUniqueState()) {
            // with this approach `entities` will not take there's events.
            $em = $event->getEntityManager();
            $id = $em->getClassMetadata(get_class($object))->identifier[0];

            $em->createQueryBuilder()
                ->update(get_class($object), 'o')
                ->set('o.' . $object->getUniqueStateField(), ':update')
                ->where('o.' . $object->getUniqueStateField() . ' = :where')
                ->andWhere('o.' . $id . ' <> :self')
                ->setParameter('update', false)
                ->setParameter('where', true)
                ->setParameter('self', $object)
                ->getQuery()->execute()
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate(LifecycleEventArgs $event)
    {
        $this->postPersist($event);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
            'postUpdate',
        );
    }
}
