<?php

namespace DoS\ResourceBundle\Controller;

use Doctrine\ORM\EntityManager;
use DoS\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController as BaseResourceController;
use Sylius\Component\Resource\Event\ResourceEvent;
use Sylius\Component\Resource\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ResourceController extends BaseResourceController
{
    protected $activedPath = 'actived';
    protected $enabledPath = 'enabled';

    /**
     * @return EntityManager
     */
    protected function getManager()
    {
        return $this->get($this->config->getServiceName('manager'));
    }

    /**
     * @param $resource
     * @param bool|true $dosRepository
     * @return \Doctrine\ORM\EntityRepository|EntityRepository
     */
    protected function getEntityRepository($resource, $dosRepository = true)
    {
        $er = $this->getManager()->getRepository(get_class($resource));

        if ($dosRepository) {
            if (!$er instanceof EntityRepository) {
                throw new UnexpectedTypeException($er, EntityRepository::class);
            }
        }

        return $er;
    }

    /**
     * @param $string
     * @return bool
     */
    protected function stringToBoolean($string)
    {
        $string = is_string($string) ? strtolower($string) : $string;

        if (in_array($string, array(true, 1, '1', 'yes', 'true'))) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function findOr404(Request $request, array $criteria = array())
    {
        $resource = parent::findOr404($request, $criteria);

        if ($roles = $this->config->getParameters()->get('is_granted')) {
            $this->denyAccessUnlessGranted((array)$roles, $resource);
        }

        return $resource;
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
        $path = $path ?: $this->activedPath;
        $resource = $this->findOr404($request);
        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($resource, $path, $this->stringToBoolean($state));

        $this->getManager()->transactional(function() use ($state, $resource, $path) {
            if ($state) {
                // reset other to false
                $this->getEntityRepository($resource)->bulkUpdate(array($path => false));
            }

            $this->domainManager->update($resource);
        });

        if ($this->config->isApiRequest()) {
            if ($resource instanceof ResourceEvent) {
                throw new HttpException($resource->getErrorCode(), $resource->getMessage());
            }

            return $this->handleView($this->view($resource, 204));
        }

        return $this->redirectHandler->redirectToReferer();
    }

    /**
     * @param Request $request
     * @param $state
     * @param null $path
     * @return RedirectResponse|Response
     */
    public function enableStateAction(Request $request, $state, $path = null)
    {
        $path = $path ?: $this->enabledPath;
        $resource = $this->findOr404($request);
        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($resource, $path, $this->stringToBoolean($state));

        $this->domainManager->update($resource);

        if ($this->config->isApiRequest()) {
            if ($resource instanceof ResourceEvent) {
                throw new HttpException($resource->getErrorCode(), $resource->getMessage());
            }

            return $this->handleView($this->view($resource, 204));
        }

        return $this->redirectHandler->redirectToReferer();
    }
}
