<?php

namespace DoS\ResourceBundle\Model;

/**
 * @author liverbool <nukboon@gmail.com>
 */
trait OriginatorTrait
{
    /**
     * @var int
     */
    protected $originId;

    /**
     * @var string
     */
    protected $originType;

    /**
     * @var string
     */
    protected $originAlias;

    /**
     * {@inheritdoc}
     */
    public function getOriginId()
    {
        return $this->originId;
    }

    /**
     * {@inheritdoc}
     */
    public function setOriginId($originId)
    {
        $this->originId = $originId;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginType()
    {
        return $this->originType;
    }

    /**
     * {@inheritdoc}
     */
    public function setOriginType($originType)
    {
        $this->originType = $originType;

        if (method_exists($originType, 'getOriginalAlias')) {
            $this->originAlias = $originType::getOriginalAlias();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginAlias()
    {
        return $this->originAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function setOriginAlias($originAlias)
    {
        $this->originAlias = $originAlias;

        return $this;
    }
}
