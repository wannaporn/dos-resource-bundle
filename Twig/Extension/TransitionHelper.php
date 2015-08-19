<?php

namespace DoS\ResourceBundle\Twig\Extension;

abstract class TransitionHelper extends \Twig_Extension
{
    /**
     * @var array
     */
    protected $options = array(
        'state' => array(
            'translation' => null,
            'default_color' => 'black',
            'negative_colors' => array('red'),
            'positive_colors' => array('green'),
        ),
        'transition' => array(
            'translation' => null,
            'default_color' => 'black',
            'negative_colors' => array('red'),
            'positive_colors' => array('green'),
        ),
    );

    /**
     * @var array
     */
    protected $colors = array();

    /**
     * @var array
     */
    protected $negativeColors = array();

    /**
     * @var array
     */
    protected $positiveColors = array();

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = array_replace_recursive($this->options, $options);

        foreach ($this->options as $type => $options) {
            if (!empty($options['colors'])) {
                foreach ($options['colors'] as $color => $states) {
                    if (!is_array($states)) {
                        $states = (array) $states;
                    }

                    foreach ($states as $state) {
                        $this->colors[$type][$state] = $color;
                    }
                }
            }

            if (!empty($options['negative_colors'])) {
                $this->negativeColors[$type] = $options['negative_colors'];
            }

            if (!empty($options['positive_colors'])) {
                $this->positiveColors[$type] = $options['positive_colors'];
            }
        }
    }

    /**
     * Get transition translator key.
     *
     * @param string $transition
     *
     * @return string
     */
    public function getTransition($transition)
    {
        return $this->options['transition']['translation'].'.'.$transition;
    }

    /**
     * Get transition color.
     *
     * @param string $transition
     *
     * @return string
     */
    public function getTransitionColor($transition)
    {
        return empty($this->colors['transition'][$transition])
            ? $this->options['transition']['default_color']
            : $this->colors['transition'][$transition];
    }

    /**
     * Check color is negative?
     *
     * @param $color
     * @param $type
     *
     * @return bool
     */
    public function isNegativeColor($color, $type)
    {
        if (empty($this->negativeColors[$type])) {
            return false;
        }

        return in_array($color, $this->negativeColors[$type]);
    }

    /**
     * Check color is positive?
     *
     * @param $color
     * @param $type
     *
     * @return bool
     */
    public function isPositiveColor($color, $type)
    {
        if (empty($this->positiveColors[$type])) {
            return false;
        }

        return in_array($color, $this->positiveColors[$type]);
    }

    /**
     * Get state translator key.
     *
     * @param string $state
     *
     * @return string
     */
    public function getState($state)
    {
        return $this->options['state']['translation'].'.'.$state;
    }

    /**
     * Get state color.
     *
     * @param string $state
     *
     * @return string
     */
    public function getStateColor($state)
    {
        return empty($this->colors['state'][$state])
            ? $this->options['state']['default_color']
            : $this->colors['state'][$state];
    }

    /**
     * @param string $class Class name
     * @param string $name  Constant name
     *
     * @return mixed
     */
    protected function getConstant($class, $name)
    {
        $cls = new \ReflectionClass($class);

        return $cls->getConstant($name);
    }

    /**
     * @param $class
     * @param string $prefix
     *
     * @return array
     */
    protected function getTransitions($class, $prefix = 'TS')
    {
        $cls = new \ReflectionClass($class);

        $transitions = array();
        foreach ($cls->getConstants() as $const => $value) {
            if (preg_match(sprintf('/^%s_.*/', $prefix), $const)) {
                $transitions[$const] = $value;
            }
        }

        return $transitions;
    }
}
