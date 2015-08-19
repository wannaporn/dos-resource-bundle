<?php

namespace DoS\ResourceBundle\Twig\Extension;

use DoS\ResourceBundle\Model\StatableInterface;
use SM\Factory\Factory;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;

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
     * @var Factory
     */
    protected $factory;


    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var string
     */
    protected $updateStateRouting;

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
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('ts_is', array($this, 'isState')),
            new \Twig_SimpleFunction('ts_state', array($this, 'getState')),
            new \Twig_SimpleFunction('ts_color', array($this, 'getStateColor')),
            new \Twig_SimpleFunction('ts_transitions', array($this, 'getPosibleTransitions')),
            new \Twig_SimpleFunction('ts_routing', array($this, 'getUpdateStateRouting')),
        );
    }

    /**
     * @param Factory $factory
     */
    public function setFactory(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param RouterInterface $router
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param string $updateStateRouting
     */
    public function setUpdateStateRouting($updateStateRouting)
    {
        $this->updateStateRouting = $updateStateRouting;
    }

    /**
     * Get default pattern, can override.
     *
     * @param StatableInterface $object
     * @param string $transition
     * @param string $identifier
     *
     * @return string
     */
    public function getUpdateStateRouting(StatableInterface $object, $transition, $identifier = 'id')
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        return $this->router->generate($this->updateStateRouting, array(
            'id' => $accessor->getValue($object, $identifier),
            'transtion' => $transition,
            'graph' => $object->getStateGraph(),
        ));
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
            : $this->colors['transition'][$transition]
        ;
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
     * @param string|object $state
     *
     * @return string
     */
    public function getState($state)
    {
        if ($state instanceof StatableInterface) {
            $state = $state->getState();
        }

        return $this->options['state']['translation'].'.'.$state;
    }

    /**
     * Get state color.
     *
     * @param string|object $state
     *
     * @return string
     */
    public function getStateColor($state)
    {
        if ($state instanceof StatableInterface) {
            $state = $state->getState();
        }

        return empty($this->colors['state'][$state])
            ? $this->options['state']['default_color']
            : $this->colors['state'][$state]
        ;
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
     * @deprecated implement StatableInterface in client class.
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

    /**
     * Get all of posible transitions.
     *
     * @param StatableInterface $object
     * @param string $objectIdentifier
     *
     * @return array|null
     *
     * @throws \SM\SMException
     */
    public function getPosibleTransitions(StatableInterface $object =  null, $objectIdentifier = 'id')
    {
        if (empty($object)) {
            return;
        }

        $sm = $this->factory->get($object, $object->getStateGraph());

        if (empty($sm)) {
            return;
        }

        $tasks = array();
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($object->getStateTransitions() as $transition) {
            if ($sm->can($transition)) {

                $color = $this->getTransitionColor($transition);
                $tasks[] = array(
                    'id' => $accessor->getValue($object, $objectIdentifier),
                    'name' => $transition,
                    'color' => $color,
                    'graph' => $object->getStateGraph(),
                    'label' => $this->getTransition($transition),
                    'negative' => $this->isNegativeColor($color, 'transition'),
                    'positive' => $this->isPositiveColor($color, 'transition'),
                );
            }
        }

        return empty($tasks) ? null : $tasks;
    }

    /**
     * Compare state.
     *
     * @param array|string $stateCompare State(s) to compare
     * @param StatableInterface $object
     *
     * @return bool
     */
    public function isState($stateCompare, StatableInterface $object)
    {
        return in_array(strtolower($object->getState()), (array) $stateCompare);
    }
}
