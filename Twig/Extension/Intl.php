<?php

namespace DoS\ResourceBundle\Twig\Extension;

class Intl extends \Twig_Extensions_Extension_Intl
{
    protected $traditional = true;

    public function __construct($traditional = true)
    {
        parent::__construct();

        $this->traditional = $traditional;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('localizeddate', array($this, 'twig_localized_date_filter'), array('needs_environment' => true)),
            new \Twig_SimpleFilter('localizednumber', 'twig_localized_number_filter'),
            new \Twig_SimpleFilter('localizedcurrency', 'twig_localized_currency_filter'),
        );
    }

    public function twig_localized_date_filter(\Twig_Environment $env, $date, $dateFormat = 'medium', $timeFormat = 'medium', $locale = null, $timezone = null, $format = null)
    {
        $date = twig_date_converter($env, $date, $timezone);

        $formatValues = array(
            'none' => \IntlDateFormatter::NONE,
            'short' => \IntlDateFormatter::SHORT,
            'medium' => \IntlDateFormatter::MEDIUM,
            'long' => \IntlDateFormatter::LONG,
            'full' => \IntlDateFormatter::FULL,
        );

        $formatter = \IntlDateFormatter::create(
            $locale,
            $formatValues[$dateFormat],
            $formatValues[$timeFormat],
            $date->getTimezone()->getName(),
            $this->traditional ? \IntlDateFormatter::TRADITIONAL : \IntlDateFormatter::GREGORIAN,
            $format
        );

        return $formatter->format($date->getTimestamp());
    }
}
