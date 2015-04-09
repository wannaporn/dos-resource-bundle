<?php

namespace Dos\ResourceBundle\Controller;

use Sylius\Bundle\ResourceBundle\Controller\ResourceController as BaseResourceController;
use Symfony\Component\HttpFoundation\Request;

class ResourceController extends BaseResourceController
{
    /**
     * @inheritdoc
     */
    public function findOr404(Request $request, array $criteria = array())
    {
        $resource = parent::findOr404($request, $criteria);

        if ($roles = $this->config->getParameters()->get('is_granted')) {
            $this->denyAccessUnlessGranted((array) $roles, $resource);
        }

        return $resource;
    }
}
