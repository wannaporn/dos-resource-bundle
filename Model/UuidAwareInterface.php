<?php

namespace DoS\ResourceBundle\Model;

interface UuidAwareInterface
{
    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param $id
     */
    public function setId($id);
}
