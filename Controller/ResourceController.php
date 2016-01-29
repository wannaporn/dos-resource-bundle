<?php

namespace DoS\ResourceBundle\Controller;

use FOS\RestBundle\View\View;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController as BaseResourceController;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ResourceController extends BaseResourceController
{
    protected $activedPath = 'actived';
    protected $enabledPath = 'enabled';
    protected $stateMachineGraph = 'default';

    /**
     * @param $string
     *
     * @return bool
     */
    protected function stringToBoolean($string)
    {
        $string = is_string($string) ? strtolower($string) : $string;

        if (in_array($string, array(true, 1, '1', 'yes', 'true'), true)) {
            return true;
        }

        return false;
    }

    /**
     * @param Request $request
     * @param $state
     * @param null $path
     *
     * @return RedirectResponse|Response
     */
    public function activeStateAction(Request $request, $state, $path = null)
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);
        $path = $path ?: $this->activedPath;
        $resource = $this->findOr404($configuration);
        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($resource, $path, $this->stringToBoolean($state));

        if ($state) {
            // reset other to false
            $this->repository->bulkUpdate(array($path => false));
            $this->manager->flush();
        }

        if (!$configuration->isHtmlRequest()) {
            return $this->viewHandler->handle($configuration, View::create($resource, 204));
        }

        return $this->redirectHandler->redirectToReferer($configuration);
    }

    /**
     * @param Request $request
     * @param $state
     * @param null $path
     *
     * @return RedirectResponse|Response
     */
    public function enableStateAction(Request $request, $state, $path = null)
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::UPDATE);
        $resource = $this->findOr404($configuration);

        $path = $path ?: $this->enabledPath;
        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($resource, $path, $this->stringToBoolean($state));

        $resource = $this->findOr404($configuration);

        $this->eventDispatcher->dispatchPreEvent(ResourceActions::UPDATE, $configuration, $resource);
        $this->manager->flush();
        $this->eventDispatcher->dispatchPostEvent(ResourceActions::UPDATE, $configuration, $resource);

        if (!$configuration->isHtmlRequest()) {
            return $this->viewHandler->handle($configuration, View::create($resource, 204));
        }

        $this->flashHelper->addSuccessFlash($configuration, $state ? $path . '_enabled' : $path . '_disabled', $resource);

        return $this->redirectHandler->redirectToIndex($configuration, $resource);
    }

    public function batchDeleteAction(Request $request, array $ids = null)
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::DELETE);

        if (null == $ids) {
            $ids = $request->get('ids');
        }

        if (is_string($ids)) {
            $ids = explode( ',', $ids);
        }

        $resources = $this->repository->findBy(array(
            'id' => $ids
        ));

        if (empty($resources)) {
            throw new NotFoundHttpException(
                sprintf(
                    'Requested %s does not exist with these ids: %s.',
                    $this->metadata->getPluralName(),
                    json_encode($configuration->getCriteria($ids))
                )
            );
        }

        foreach ($resources as $resource) {
            $this->manager->remove($resource);
        }

        $this->manager->flush();

        if (!$configuration->isHtmlRequest()) {
            return $this->viewHandler->handle($configuration, View::create(null, 204));
        }

        return $this->redirectHandler->redirectToIndex($configuration);
    }

    public function updateStateAction(Request $request, $transition, $graph = null)
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::UPDATE);
        $resource = $this->findOr404($configuration);

        if (null === $graph) {
            $graph = $this->stateMachineGraph;
        }

        $stateMachine = $this->get('sm.factory')->get($resource, $graph);

        if (!$stateMachine->can($transition)) {
            throw new NotFoundHttpException(sprintf(
                'The requested transition %s cannot be applied on the given %s with graph %s.',
                $transition,
                $this->metadata->getName(),
                $graph
            ));
        }

        $stateMachine->apply($transition);

        $this->manager->flush();
        $this->eventDispatcher->dispatchPostEvent(ResourceActions::UPDATE, $configuration, $resource);

        if (!$configuration->isHtmlRequest()) {
            return $this->viewHandler->handle($configuration, View::create($resource, 204));
        }

        $this->flashHelper->addSuccessFlash($configuration, ResourceActions::UPDATE, $resource);

        return $this->redirectHandler->redirectToResource($configuration, $resource);
    }
}
