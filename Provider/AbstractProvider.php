<?php

namespace DoS\ResourceBundle\Provider;

use Doctrine\Common\Persistence\ObjectManager;
use DoS\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Resource\Repository\RepositoryInterface;

/**
 * @author liverbool <nukboon@gmail.com>
 */
abstract class AbstractProvider
{
    /**
     * @var ObjectManager
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

    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
        $this->manager = $repository->getManager();
        $this->dataClass = $repository->getEntityName();
    }
}
