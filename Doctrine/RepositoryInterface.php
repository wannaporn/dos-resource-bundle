<?php

namespace DoS\ResourceBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Component\Resource\Repository\RepositoryInterface as BaseRepositoryInterface;

interface RepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return ObjectManager
     */
    public function getManager();

    /**
     * @return string
     */
    public function getEntityName();
}
