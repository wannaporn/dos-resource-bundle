<?php

namespace DoS\ResourceBundle\Model;

trait StatableTrait
{
    /**
     * @var string
     */
    protected $state;

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllStates()
    {
        throw new \LogicException('Override me.');
    }

    /**
     * {@inheritdoc}
     */
    public function getStateGraph()
    {
        throw new \LogicException('Override me.');
    }

    /**
     * {@inheritdoc}
     */
    public function getStateTransitions()
    {
        throw new \LogicException('Override me.');
    }
}
