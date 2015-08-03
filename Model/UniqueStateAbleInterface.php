<?php

namespace DoS\ResourceBundle\Model;

interface UniqueStateAbleInterface
{
    /**
     * @return string
     */
    public function getUniqueStateField();

    /**
     * @return bool
     */
    public function getUniqueState();

    /**
     * @param bool $state
     */
    public function setUniqueState($state);
}
