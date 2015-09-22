<?php

namespace DoS\ResourceBundle\Doctrine\ODM\MongoDB;

use DoS\ResourceBundle\Doctrine\RepositoryInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ODM\MongoDB\DocumentRepository as BaseDocumentRepository;

class DocumentRepository extends BaseDocumentRepository implements RepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->dm;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityName()
    {
        return $this->documentName;
    }

}
