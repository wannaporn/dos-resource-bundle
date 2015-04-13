<?php

namespace DoS\ResourceBundle\Doctrine\ORM;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository as BaseEntityRepository;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityRepository extends BaseEntityRepository
{
    /**
     * {@inheritdoc}
     */
    public function createUserList(array $criteria = null, array $orderBy = null)
    {
        if (empty($criteria['user'])) {
            throw new NotFoundHttpException('Not found user.');
        }

        return parent::createPaginator($criteria, $orderBy);
    }

    /**
     * {@inheritdoc}
     */
    public function createUserPaginator(array $criteria = null, array $orderBy = null)
    {
        if (empty($criteria['user'])) {
            throw new NotFoundHttpException('Not found user.');
        }

        return parent::createPaginator($criteria, $orderBy);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array        $sorting
     */
    protected function applySorting(QueryBuilder $queryBuilder, array $sorting = null)
    {
        if (null === $sorting) {
            return;
        }

        foreach ($sorting as $property => $order) {
            if (!empty($order) && !$this->addAssociateOrder($property, $order, $queryBuilder)) {
                $queryBuilder->addOrderBy($this->getPropertyName($property), $order);
            }
        }
    }

    /**
     * @param string       $name
     * @param string       $order
     * @param QueryBuilder $queryBuilder
     *
     * @return bool
     */
    private function addAssociateOrder($name, $order, QueryBuilder $queryBuilder)
    {
        // simple join
        if (false !== strpos($name, '.')) {
            // single level
            list($key, $fild) = explode('.', $name);

            if ($key !== $this->getAlias()) {
                $associations = $this->getClassMetadata()->getAssociationMappings();

                if ($associations[$key]['type'] === ClassMetadataInfo::MANY_TO_MANY) {
                    throw new NotAcceptableHttpException('Cannot order associtated Many-To-Many object type.');
                }

                $join = $this->getAlias().'.'.$key;
                $alias = '_'.$key;
                $name = '__'.$key;

                $queryBuilder
                    ->join($join, $alias)
                    ->addSelect(sprintf('%s.%s AS HIDDEN %s', $alias, $fild, $name))
                    ->orderBy($name, $order)
                ;

                return true;
            }
        }

        return false;
    }
}
