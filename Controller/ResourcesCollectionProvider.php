<?php

namespace DoS\ResourceBundle\Controller;

use Hateoas\Configuration\Route;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Pagerfanta\Pagerfanta;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\ResourcesCollectionProviderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ResourcesCollectionProvider implements ResourcesCollectionProviderInterface
{
    /**
     * @var PagerfantaFactory
     */
    private $pagerfantaRepresentationFactory;

    /**
     * @param PagerfantaFactory $pagerfantaRepresentationFactory
     */
    public function __construct(PagerfantaFactory $pagerfantaRepresentationFactory)
    {
        $this->pagerfantaRepresentationFactory = $pagerfantaRepresentationFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function get(RequestConfiguration $config, RepositoryInterface $repository)
    {
        if (null !== $repositoryMethod = $config->getRepositoryMethod()) {
            $callable = [$repository, $repositoryMethod];

            return call_user_func_array($callable, $config->getRepositoryArguments());
        }

        if (!$config->isPaginated() && !$config->isLimited()) {
            return $repository->findAll();
        }

        if (!$config->isPaginated()) {
            return $repository->findBy($config->getCriteria(), $config->getSorting(), $config->getLimit());
        }

        $request = $config->getRequest();

        /** @var Pagerfanta $paginator */
        $paginator = $repository->createPaginator($config->getCriteria(), $config->getSorting());
        $paginator->setCurrentPage($request->query->get('page', 1));
        $paginator->setMaxPerPage($request->query->get('limit', $config->getLimit()));

        if (!$config->isHtmlRequest()) {
            $route = new Route($request->attributes->get('_route'), array_merge($request->attributes->get('_route_params'), $request->query->all()));

            return $this->pagerfantaRepresentationFactory->createRepresentation($paginator, $route);
        }

        return $paginator;
    }
}
