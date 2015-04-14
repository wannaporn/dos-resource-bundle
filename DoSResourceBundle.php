<?php

namespace DoS\ResourceBundle;

use DoS\ResourceBundle\DependencyInjection\AbstractResourceBundle;

class DoSResourceBundle extends AbstractResourceBundle
{
    /**
     * {@inheritdoc}
     */
    protected function getDependencyBundles()
    {
        return array(
            new \Sylius\Bundle\ResourceBundle\SyliusResourceBundle(),
            new \Sylius\Bundle\MoneyBundle\SyliusMoneyBundle(),
        );
    }
}
