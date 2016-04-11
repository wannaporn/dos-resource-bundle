<?php

namespace DoS\ResourceBundle\Provider;

use Sylius\Component\Resource\Model\ResourceInterface;

interface ProviderInterface
{
    /**
     * @return ResourceInterface
     */
    public function createNew();

    /**
     * @param ResourceInterface $resource
     */
    public function persist(ResourceInterface $resource);

    /**
     * @param ResourceInterface|null $resource
     */
    public function flush(ResourceInterface $resource = null);

    /**
     * @param ResourceInterface $resource
     * @param bool $single
     */
    public function save(ResourceInterface $resource, $single = false);

    /**
     * @param $id
     *
     * @return ResourceInterface
     */
    public function find($id);

    /**
     * @return ResourceInterface[]
     */
    public function findAll();

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     *
     * @return ResourceInterface[]
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);

    /**
     * @param array $criteria
     *
     * @return ResourceInterface[]
     */
    public function findOneBy(array $criteria);
}
