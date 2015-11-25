<?php

namespace DoS\ResourceBundle\Factory;

class ResourceFactoryAware implements ResourceFactoryAwareInterface
{
    protected $factory;

    /**
     * {@inheritdoc}
     */
    public function setResourceFactory(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }
}
