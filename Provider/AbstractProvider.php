<?php

namespace DoS\ResourceBundle\Provider;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use DoS\ResourceBundle\Doctrine\RepositoryInterface;
use DoS\ResourceBundle\Factory\ResourceFactoryAware;

/**
 * @author liverbool <nukboon@gmail.com>
 */
abstract class AbstractProvider extends ResourceFactoryAware
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

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->manager = $repository->getManager();
        $this->dataClass = $repository->getEntityName();
    }
}
