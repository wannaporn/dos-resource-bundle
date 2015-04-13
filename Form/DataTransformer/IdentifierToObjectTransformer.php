<?php

namespace DoS\ResourceBundle\Form\DataTransformer;

use Doctrine\Common\Persistence\ObjectRepository;
use Sylius\Bundle\ResourceBundle\Form\DataTransformer\ObjectToIdentifierTransformer;

/**
 * Object to id transformer.
 */
class IdentifierToObjectTransformer extends ObjectToIdentifierTransformer
{
    /**
     * Repository.
     *
     * @var ObjectRepository
     */
    protected $repository;

    /**
     * Identifier.
     *
     * @var string
     */
    protected $identifier;

    /**
     * Constructor.
     *
     * @param ObjectRepository $repository
     * @param string           $identifier
     */
    public function __construct(ObjectRepository $repository, $identifier = 'id')
    {
        $this->repository = $repository;
        $this->identifier = $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        return parent::transform($value);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return parent::reverseTransform($value);
    }
}
