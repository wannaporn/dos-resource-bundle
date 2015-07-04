<?php

namespace DoS\ResourceBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Id\UuidGenerator;
use DoS\ResourceBundle\Model\UuidAwareInterface;

class UuidGeneratorListener
{
    public function prePersist(LifecycleEventArgs $event)
    {
        $object = $event->getObject();

        if ($object instanceof UuidAwareInterface and !$object->getId()) {
            $object->setId((new UuidGenerator())->generate($event->getEntityManager(), $object));
        }
    }
}
