<?php

namespace DoS\ResourceBundle\Model;

interface OrderingPositionInterface
{
    /**
     * @return int
     */
    public function getOrderingPosition();

    /**
     * @param int $position
     */
    public function setOrderingPosition($position);
}
