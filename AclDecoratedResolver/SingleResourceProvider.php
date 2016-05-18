<?php

namespace DoS\ResourceBundle\AclDecoratedResolver;

use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\SingleResourceProviderInterface;
use Sylius\Component\Rbac\Authorization\AuthorizationCheckerInterface as SyliusAuthorizationCheckerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

class SingleResourceProvider implements SingleResourceProviderInterface
{
    /**
     * @var SingleResourceProviderInterface
     */
    private $singleResourceDecorator;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var SyliusAuthorizationCheckerInterface
     */
    private $rbacAuthorizationChecker;

    /**
     * @var string
     */
    private $ignoreWhenHasRbacRole;

    public function __construct(
        SingleResourceProviderInterface $singleResourceDecorator,
        AuthorizationCheckerInterface $authorizationChecker,
        SyliusAuthorizationCheckerInterface $rbacAuthorizationChecker,
        $ignoreWhenHasRbacRole = 'admin'
    ) {
        $this->singleResourceDecorator = $singleResourceDecorator;
        $this->authorizationChecker = $authorizationChecker;
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
        $resource = $this->singleResourceDecorator->get($requestConfiguration, $repository);

        if ($resource && $this->requireOwnerCheck($requestConfiguration->getVars())) {
            try {
                if ($this->authorizationChecker->isGranted('OWNER', $resource)) {
                    return $resource;
                }
            } catch (AuthenticationCredentialsNotFoundException $e) {
                throw new AccessDeniedException();
            }

            throw new AccessDeniedException();
        }

        return $resource;
    }
}
