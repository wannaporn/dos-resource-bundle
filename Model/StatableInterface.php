<?php

namespace DoS\ResourceBundle\Model;

interface StatableInterface
{
    /**
     * @return string
     */
    public function getState();

    /**
     * @param string $state
     */
    public function setState($state);

    /**
     * @return array all states
     */
    public function getAllStates();

    /**
     * @return string
     */
    public function getStateGraph();

    /**
     * @return array
     */
    public function getStateTransitions();
}
