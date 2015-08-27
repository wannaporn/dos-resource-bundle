<?php

namespace DoS\ResourceBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType as BaseAbstractResourceType;

abstract class AbstractResourceType extends BaseAbstractResourceType
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var ObjectRepository
     */
    protected $repository;

    /**
     * @param ObjectManager|null $manager
     */
    public function setObjectManager(ObjectManager $manager = null)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository($this->dataClass);
    }

    /**
     * {@inheritdoc}
     */
    public function __construct($dataClass, array $validationGroups = array(), $name = null)
    {
        parent::__construct($dataClass, $validationGroups);

        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }
}
