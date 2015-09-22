<?php

namespace DoS\ResourceBundle\Doctrine;

use Sylius\Component\Resource\Repository\RepositoryInterface as BaseRepositoryInterface;

interface RepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    public function getManager();

    /**
     * @return string
     */
    public function getEntityName();
}
