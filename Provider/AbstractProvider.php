<?php

namespace DoS\ResourceBundle\Provider;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

/**
 * @author liverbool <nukboon@gmail.com>
 */
abstract class AbstractProvider implements ProviderInterface
{
    /**
     * @var EntityManager|DocumentManager
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

    /**
     * @return ResourceInterface
     */
    public function createNew()
    {
        return $this->factory->createNew();
    }

    /**
     * @param ResourceInterface $resource
     */
    public function persist(ResourceInterface $resource)
    {
        $this->manager->persist($resource);
    }

    /**
     * @param ResourceInterface|null $resource
     */
    public function flush(ResourceInterface $resource = null)
    {
        $this->manager->flush($resource);
    }

    /**
     * @param ResourceInterface $resource
     * @param bool $single
     */
    public function save(ResourceInterface $resource, $single = false)
    {
        $this->manager->persist($resource);
        $this->manager->flush($single ? $resource : null);
    }

    /**
     * @param ResourceInterface $resource
     */
    public function detach(ResourceInterface $resource)
    {
        $this->manager->detach($resource);
    }

    /**
     * @param ResourceInterface $resource
     */
    public function remove(ResourceInterface $resource)
    {
        $this->manager->remove($resource);
    }

    /**
     * @param null $objectName
     */
    public function clear($objectName = null)
    {
        $this->manager->clear($objectName);
    }

    /**
     * @param $id
     *
     * @return ResourceInterface
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }

    /**
     * @return ResourceInterface[]
     */
    public function findAll()
    {
        return $this->repository->findAll();
    }

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     *
     * @return ResourceInterface[]
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @param array $criteria
     *
     * @return ResourceInterface[]
     */
    public function findOneBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }
}
