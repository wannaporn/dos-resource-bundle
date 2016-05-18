<?php

namespace DoS\ResourceBundle\AclDecoratedResolver;

use DoS\ResourceBundle\AclDecoratedResolver\Grid\ResourceOwnerFilter;
use Sylius\Component\Rbac\Authorization\AuthorizationCheckerInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\ResourcesCollectionProviderInterface;
use Sylius\Component\Rbac\Provider\CurrentIdentityProviderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ResourcesCollectionProvider implements ResourcesCollectionProviderInterface
{
    /**
     * @var ResourcesCollectionProviderInterface
     */
    private $decoratedResolver;

    /**
     * @var CurrentIdentityProviderInterface
     */
    private $currentIdentityProvider;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $rbacAuthorizationChecker;

    /**
     * @var string
     */
    private $ignoreWhenHasRbacRole;

    public function __construct(
        ResourcesCollectionProviderInterface $decoratedResolver,
        CurrentIdentityProviderInterface $currentIdentityProvider,
        AuthorizationCheckerInterface $rbacAuthorizationChecker,
        $ignoreWhenHasRbacRole = 'admin'
    ) {
        $this->decoratedResolver = $decoratedResolver;
        $this->currentIdentityProvider = $currentIdentityProvider;
        $this->rbacAuthorizationChecker = $rbacAuthorizationChecker;
        $this->ignoreWhenHasRbacRole = $ignoreWhenHasRbacRole;
    }

    private function requireOwnerCheck(array $vars)
    {
        if ($this->rbacAuthorizationChecker->isGranted($this->ignoreWhenHasRbacRole)) {
            return false;
        }

        if (!array_key_exists('acl_owner', $vars)) {
            return false;
        }

        return false !== $vars['acl_owner'];
    }

    /**
     * {@inheritdoc}
     */
    public function get(RequestConfiguration $requestConfiguration, RepositoryInterface $repository)
    {
        if ($this->requireOwnerCheck($vars = $requestConfiguration->getVars())) {
            $owner = $this->currentIdentityProvider->getIdentity();
            $ownerField = is_bool($vars['acl_owner'])
                ? ResourceOwnerFilter::FIELD
                : $vars['acl_owner']
            ;

            // using in grid param
            $requestConfiguration
                ->getRequest()
                ->query->set(ResourceOwnerFilter::TYPE, $owner)
            ;

            $criteria = $requestConfiguration->getCriteria();
            $criteria[$ownerField] = $owner;

            $requestConfiguration->getParameters()->set('criteria', $criteria);
        }

        return $this->decoratedResolver->get($requestConfiguration, $repository);
    }
}
