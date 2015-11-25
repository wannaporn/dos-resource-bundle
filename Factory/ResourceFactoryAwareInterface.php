<?php

namespace DoS\ResourceBundle\Factory;

interface ResourceFactoryAwareInterface
{
    /**
     * @param FactoryInterface $factory
     */
    public function setResourceFactory(FactoryInterface $factory);
}
