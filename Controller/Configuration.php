<?php

namespace DoS\ResourceBundle\Controller;

use Sylius\Bundle\ResourceBundle\Controller\Configuration as SyliusConfiguration;

class Configuration extends SyliusConfiguration
{
    public function getPaginationMaxPerPage()
    {
        return (int) $this->parameters->get(
            'paginate',
            $this->request->get('limit', $this->settings['default_page_size'])
        );
    }
}
