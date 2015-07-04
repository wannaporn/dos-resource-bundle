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
