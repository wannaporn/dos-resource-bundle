<?php

namespace DoS\ResourceBundle\Model;

trait SoftDeletableTrait
{
    /**
     * @var \DateTime
     */
    protected $deletedAt = null;

    /**
     * {@inheritdoc}
     */
    public function isDeleted()
    {
        return (null !== $this->deletedAt) && ((new \DateTime()) >= $this->deletedAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setDeletedAt(\DateTime $deletedAt = null)
    {
        $this->deletedAt = $deletedAt;
    }
}
