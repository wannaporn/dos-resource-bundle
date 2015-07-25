<?php

namespace DoS\ResourceBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;
use Symfony\Component\Form\ResolvedFormTypeFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use DoS\UserBundle\Model\UserInterface;

class Factory extends FormFactory
{
    protected $pattern;
    protected $replacement = 'dos_';

    /**
     * @var FormRegistryInterface
     */
    private $registry;

    /**
     * @var ResolvedFormTypeFactoryInterface
     */
    private $resolvedTypeFactory;

    public function __construct(FormRegistryInterface $registry, ResolvedFormTypeFactoryInterface $resolvedTypeFactory)
    {
        $this->registry = $registry;
        $this->resolvedTypeFactory = $resolvedTypeFactory;
    }

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function setSecurityTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param string $pattern ereg pattern
     * @param string $replacement
     */
    public function setPrefixAndReplacement($pattern = null, $replacement = null)
    {
        $this->pattern = $pattern ?: $this->pattern
        $this->replacement = $replacement ?: $this->replacement
    }

    /**
     * @inheritdoc
     */
    public function createNamedBuilder($name, $type = 'form', $data = null, array $options = array())
    {
        if ($this->pattern) {
            $name = preg_replace($this->pattern, $this->replacement, $name);

            if (null !== $data && !array_key_exists('data', $options)) {
                $options['data'] = $data;
            }

            if ($type instanceof FormTypeInterface) {
                $type = $this->resolveType($type);
            } elseif (is_string($type)) {
                $type = $this->registry->getType($type);
            } elseif (!$type instanceof ResolvedFormTypeInterface) {
                throw new UnexpectedTypeException($type, 'string, Symfony\Component\Form\ResolvedFormTypeInterface or Symfony\Component\Form\FormTypeInterface');
            }

            $builder = $type->createBuilder($this, $name, $options);
        }

        // set default user
        $builder->setAttribute('user_aware', $this->getUser());

        // Explicitly call buildForm() in order to be able to override either
        // createBuilder() or buildForm() in the resolved form type
        $type->buildForm($builder, $builder->getOptions());

        return $builder;
    }

    /**
     * @inheritdoc
     */
    private function resolveType(FormTypeInterface $type)
    {
        $parentType = $type->getParent();

        if ($parentType instanceof FormTypeInterface) {
            $parentType = $this->resolveType($parentType);
        } elseif (null !== $parentType) {
            $parentType = $this->registry->getType($parentType);
        }

        return $this->resolvedTypeFactory->createResolvedType(
            $type,
            // Type extensions are not supported for unregistered type instances,
            // i.e. type instances that are passed to the FormFactory directly,
            // nor for their parents, if getParent() also returns a type instance.
            array(),
            $parentType
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createBuilderForProperty($class, $property, $data = null, array $options = array())
    {
        if (null === $guesser = $this->registry->getTypeGuesser()) {
            return $this->createNamedBuilder($property, 'text', $data, $options);
        }

        $typeGuess = $guesser->guessType($class, $property);
        $maxLengthGuess = $guesser->guessMaxLength($class, $property);
        $requiredGuess = $guesser->guessRequired($class, $property);
        $patternGuess = $guesser->guessPattern($class, $property);

        $type = $typeGuess ? $typeGuess->getType() : 'text';

        $maxLength = $maxLengthGuess ? $maxLengthGuess->getValue() : null;
        $pattern = $patternGuess ? $patternGuess->getValue() : null;

        if (null !== $pattern) {
            $options = array_replace_recursive(array('attr' => array('pattern' => $pattern)), $options);
        }

        if (null !== $maxLength) {
            $options = array_replace_recursive(array('attr' => array('maxlength' => $maxLength)), $options);
        }

        if ($requiredGuess) {
            $options = array_merge(array('required' => $requiredGuess->getValue()), $options);
        }

        // user options may override guessed options
        if ($typeGuess) {
            $options = array_merge($typeGuess->getOptions(), $options);
        }

        return $this->createNamedBuilder($property, $type, $data, $options);
    }

    /**
     * @return UserInterface
     */
    private function getUser()
    {
        $token = $this->tokenStorage->getToken();

        return $token ? $token->getUser() : null;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param UserInterface|null   $user
     *
     * @return callable
     */
    public static function getUserAwareChoiceBuilder(FormBuilderInterface $builder, UserInterface $user = null)
    {
        return function (EntityRepository $objectRepository) use ($builder, $user) {
            return $objectRepository->createQueryBuilder('o')
                ->andWhere('o.user = :user')
                ->setParameter('user', $user ?: $builder->getAttribute('user_aware'))
                ;
        };
    }
}
