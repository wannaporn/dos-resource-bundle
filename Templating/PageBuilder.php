<?php

namespace DoS\ResourceBundle\Templating;

use Sylius\Bundle\SettingsBundle\Templating\Helper\SettingsHelper;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageBuilder
{
    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * @var array
     */
    protected $options = array();

    //private $theme = null;

    public function __construct(SettingsHelper $settingsHelper, $defaultOptions = array())
    {
        $this->settingsHelper = $settingsHelper;
        $this->options = $defaultOptions;
    }

    /**
     * Set page options.
     *
     * @param array $options
     */
    public function setOptions(array $options = array())
    {
        $options['inited'] = true;

        // Resolve merged options
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $options = $resolver->resolve($options);
        $this->options = array_merge($this->options, $options);

        if (empty($this->options['blocks']['header'])) {
            $this->options['header'] = false;
        }

        if (!$this->options['header']) {
            $this->options['css'] = trim($this->options['css'].' no page header');
        }
    }

    /**
     * @param $name
     * @param null $default
     *
     * @return null|string
     */
    public function getOption($name, $default = null)
    {
        return $this->getOptionValue($name, $this->options) ?: $default;
    }

    /**
     * @param string     $option
     * @param string     $printOut
     * @param null|mixed $defaultOptionValue
     *
     * @return string
     */
    public function get($option, $printOut = '%s', $defaultOptionValue = null)
    {
        if ($value = $this->getOption($option, $defaultOptionValue)) {
            if (is_array($value) || is_object($value)) {
                return $value;
            }

            return sprintf($printOut, $value);
        }

        return;
    }

    /**
     * @param string $steps
     * @param array  $options
     *
     * @return null|string
     */
    private function getOptionValue($steps, array $options)
    {
        $value = $options;
        $steps = explode('.', $steps);

        foreach ($steps as $step) {
            if (!array_key_exists($step, $value)) {
                return;
            }

            $value = $value[$step];
        }

        return $value;
    }

    /**
     * Configure the option resolver.
     *
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(array());

        $resolver->setDefaults(array(
            'id' => null,
            'css' => null,
            'charset' => 'UTF-8',
            'title' => $this->settingsHelper->getSettingsParameter('general.title'),
            'locale' => $this->settingsHelper->getSettingsParameter('general.locale'),
            'metas' => array(
                'keywords' => $this->settingsHelper->getSettingsParameter('general.meta_keywords'),
                'description' => $this->settingsHelper->getSettingsParameter('general.meta_description'),
            ),
            'blocks' => null,
            'styles' => null,
            'scripts' => null,
            'keywords' => null,
            'decription' => null,
            'breadcrumbs' => null,
            'reset_script' => false,
            'reset_style' => false,
            'reset_meta' => false,
            'reset_breadcrumb' => false,
            'heading' => null,
            'icon' => null,
            'inited' => false,
        ));

        $resolver->setAllowedTypes(array(
            'inited' => array('boolean'),
            'heading' => array('null', 'string'),
            'icon' => array('null', 'string'),
            'metas' => array('null', 'array'),
            'blocks' => array('null', 'array'),
        ));
    }
}
