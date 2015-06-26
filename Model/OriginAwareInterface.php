<?php

namespace DoS\ResourceBundle\Model;

use Sylius\Component\Originator\Model\OriginAwareInterface as BaseOriginAwareInterface;

interface OriginAwareInterface extends BaseOriginAwareInterface
{
    /**
     * @return string
     */
    public function getOriginAlias();

    /**
     * @param string $alias
     *
     * @return self
     */
    public function setOriginAlias($alias);
}
