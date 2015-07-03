<?php

namespace DoS\ResourceBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use DoS\ResourceBundle\Model\ImageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ImageUploadSubscriber implements EventSubscriber
{
    /**
     * @var ContainerInterface;
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $subject
     */
    private function upload($subject)
    {
        if ($subject instanceof ImageInterface && $subject->hasFile()) {
            $this->container->get('dos.image_uploader')->upload($subject);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(LifecycleEventArgs $event)
    {
        $this->upload($event->getObject());
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        $this->upload($event->getObject());
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'postPersist',
        );
    }
}
