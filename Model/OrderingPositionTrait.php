<?php

namespace DoS\ResourceBundle\Model;

trait OrderingPositionTrait
{
    protected $orderingPosition = 0;

    /**
     * @return int
     */
    public function getOrderingPosition()
    {
        return $this->orderingPosition;
    }

    /**
     * @param int $orderingPosition
     */
    public function setOrderingPosition($orderingPosition)
    {
        $this->orderingPosition = $orderingPosition;
    }
}
