<?php

namespace DoS\ResourceBundle\Provider;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use DoS\ResourceBundle\Doctrine\RepositoryInterface;
use DoS\ResourceBundle\Factory\FactoryInterface;

/**
 * @author liverbool <nukboon@gmail.com>
 */
abstract class AbstractProvider
{
    /**
     * @var ObjectManager|DocumentManager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $dataClass;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var FactoryInterface
     */
    protected $factory;

    public function __construct(RepositoryInterface $repository, FactoryInterface $factory)
    {
        $this->repository = $repository;
        $this->factory = $factory;
        $this->manager = $repository->getManager();
        $this->dataClass = $repository->getEntityName();
    }
}
