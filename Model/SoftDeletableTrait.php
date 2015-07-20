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
