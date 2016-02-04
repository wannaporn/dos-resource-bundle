<?php

namespace DoS\ResourceBundle\Controller;

use Sylius\Bundle\ResourceBundle\Controller\Parameters;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration as BaseRequestConfiguration;
use Sylius\Component\Resource\Metadata\MetadataInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestConfiguration extends BaseRequestConfiguration
{
    /**
     * @param MetadataInterface $metadata
     * @param Request $request
     * @param Parameters $parameters
     */
    public function __construct(MetadataInterface $metadata, Request $request, Parameters $parameters)
    {
        if ($request->query->has('limit')) {
            $parameters->set('limit', $request->query->get('limit'));
        }

        parent::__construct($metadata, $request, $parameters);
    }
}
