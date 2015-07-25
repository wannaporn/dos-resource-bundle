<?php

namespace DoS\ResourceBundle\Twig\Extension;

class Arrays extends \Twig_Extension
{
    public function getFunctions()
    {
        $self = array('is_safe' => array('all'));

        return array(
            //new \Twig_SimpleFunction('magice_icon_color', array($this, 'getColor'), $self),
        );
    }

    public function getFilters()
    {
        $self = array('is_safe' => array('all'));

        return array(
            new \Twig_SimpleFilter('is_array', 'is_array'),
            new \Twig_SimpleFilter('in_array', 'in_array'),
            new \Twig_SimpleFilter('search_array', 'array_search'),
        );
    }

    /**
     * @api
     */
    public function getName()
    {
        return 'ui_arrays';
    }
}
