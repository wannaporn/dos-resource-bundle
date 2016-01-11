<?php

namespace DoS\ResourceBundle\Model;

/**
 * Interface OriginContextInterface.
 */
interface OriginContextInterface extends UuidAwareInterface
{
    /**
     * @return string
     */
    public static function getOriginalAlias();

    /**
     * @return string get_class($this);
     */
    public static function getOriginalType();
}
