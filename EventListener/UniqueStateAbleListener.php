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
            $er = $event->getEntityManager()->getRepository(get_class($object));
            if ($entities = $er->findBy(array($object->getUniqueStateField() => true))) {
                /** @var UniqueStateAbleInterface $entity */
                foreach($entities as $entity) {
                    $entity->setUniqueState(false);
                    $event->getEntityManager()->persist($entity);
                }

                $object->setUniqueState(true);
            }
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
