<?php

namespace DoS\ResourceBundle\Twig\Extension;

use Sylius\Bundle\ResourceBundle\Twig\ResourceExtension;

class Resource extends ResourceExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array_merge(parent::getFunctions(), array(
            new \Twig_SimpleFunction(
                'ui_sort',
                array($this, 'renderSortingLink'),
                array('needs_environment' => true, 'is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction(
                'ui_paginate',
                array($this, 'renderPaginateSelect'),
                array('needs_environment' => true, 'is_safe' => array('html'))
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ui_resource';
    }
}
