<?php

namespace DoS\ResourceBundle\Controller;

use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration as BaseRequestConfiguration;

class RequestConfiguration extends BaseRequestConfiguration
{
    /**
     * @return int
     */
    public function getPaginationMaxPerPage()
    {
        return (int) $this->parameters->get('paginate', $this->getRequest()->get('limit', 10));
    }
}
