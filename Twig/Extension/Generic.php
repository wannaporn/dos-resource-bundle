<?php

namespace DoS\ResourceBundle\Twig\Extension;

use Sylius\Bundle\SettingsBundle\Templating\Helper\SettingsHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class Generic extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->settingsHelper = $container->get('sylius.templating.helper.settings');
    }

    /**
     * @inheritdoc
     */
    public function getGlobals()
    {
        return array(
        );
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        $self = array('is_safe' => array('all'));

        return array(
            new \Twig_SimpleFunction('d', '\Kint::dump', $self),
            new \Twig_SimpleFunction('e', array($this, 'dumpExit'), $self),
            new \Twig_SimpleFunction('is_string', 'is_string'),
            new \Twig_SimpleFunction('ui_random_string', array($this, 'getRandomString'), $self),
            new \Twig_SimpleFunction('ui_percentage', array($this, 'calculatePercent')),
            new \Twig_SimpleFunction('ui_setting', array($this, 'getSettingsParameter')),
            new \Twig_SimpleFunction('ui_settings', array($this->settingsHelper, 'getSettings')),
            new \Twig_SimpleFunction('ui_param', array($this->container, 'getParameter')),
            new \Twig_SimpleFunction('ui_param_has', array($this->container, 'hasParameter')),
            new \Twig_SimpleFunction('ui_has', array($this->container, 'has')),
            new \Twig_SimpleFunction('ui_obfuscated_email', array($this, 'getObfuscatedEmail')),
            new \Twig_SimpleFunction('ui_property', array($this, 'getContextValue')),
        );
    }

    public function getFilters()
    {
        //$self = array('is_safe' => array('all'));

        return array(
            new \Twig_SimpleFilter('is_match', array($this, 'match')),
            new \Twig_SimpleFilter('ui_no_space', array($this, 'cleanWhiteSpace')),
        );
    }

    /**
     * @param $key
     * @param string $default
     *
     * @return null|mixed
     */
    public function getSettingsParameter($key, $default = null)
    {
        list($alias, $key) = explode('.', $key);

        $settings = $this->container->get('sylius.templating.helper.settings')
            ->getSettings($alias)
        ;

        if (array_key_exists($key, $settings)) {
            return $settings[$key];
        }

        return $default;
    }

    public function getRandomString($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    /**
     * @param $total
     * @param $pie
     *
     * @return string
     */
    public function calculatePercent($total, $pie)
    {
        if (empty($total)) {
            return '0%';
        }

        $total = ($pie / $total) * 100;

        if (is_float($total)) {
            return number_format($total, 1).'%';
        }

        return $total.'%';
    }

    /**
     * @param $email
     *
     * @return string
     */
    public function getObfuscatedEmail($email)
    {
        if (false !== $pos = strpos($email, '@')) {
            $email = '...'.substr($email, $pos);
        }

        return $email;
    }

    /**
     * @param $context
     * @param $path
     *
     * @return mixed
     */
    public function getContextValue($context, $path)
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        return $accessor->getValue($context, $path);
    }

    /**
     * @param $string
     *
     * @return string
     */
    public function cleanWhiteSpace($string)
    {
        return preg_replace('/ /', '', $string);
    }

    public function match($subject, $pattern)
    {
        return preg_match($pattern, $subject);
    }

    public function dumpExit($val)
    {
        \Kint::dump($val);
        exit;
    }

    /**
     * @api
     */
    public function getName()
    {
        return 'ui_generic';
    }
}
