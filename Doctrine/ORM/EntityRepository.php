<?php

namespace DoS\ResourceBundle\Doctrine\ORM;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\QueryBuilder;
use DoS\ResourceBundle\Doctrine\RepositoryInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository as BaseEntityRepository;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityRepository extends BaseEntityRepository implements RepositoryInterface
{
    /**
     * @return \Doctrine\ORM\Query\Expr
     */
    public function expr()
    {
        return $this->_em->getExpressionBuilder();
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getManager()
    {
        return $this->_em;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->_entityName;
    }

    /**
     * @deprecated Remove all resource creation logics to ResourceFactory.
     */
    public function createNew()
    {
        $className = $this->getClassName();

        return new $className();
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param $value
     * @param $properties
     */
    protected function applySearchCriteria(QueryBuilder $queryBuilder, $value, $properties)
    {
        if (empty($value) || empty($properties)) {
            return;
        }

        // TODO: use SearchBundle
        $xor = array();
        $properties = is_array($properties) ? $properties : array($properties);

        foreach($properties as $field) {
            if ($assoc = $this->addAssociation($queryBuilder, $field, false)) {
                $fieldPath = sprintf('%s.%s', $assoc[0], $holder = $assoc[1]);
            } else {
                $fieldPath = $this->getPropertyName($holder = $field);
            }

            $xor[] = $this->expr()->like(sprintf('LOWER(%s)', $fieldPath), sprintf(':%s', $holder));
            $queryBuilder->setParameter($holder, '%'.strtolower($value).'%');
        }

        $queryBuilder->andWhere(call_user_func_array(array($this->expr(), 'orX'), $xor));
    }

    /**
     * {@inheritdoc}
     */
    protected function applyCriteria(QueryBuilder $queryBuilder, array $criteria = array())
    {
        // TODO: can be config `_search_` key
        if (array_key_exists('_search_', $criteria)) {
            $search = $criteria['_search_'];
            unset($criteria['_search_']);

            $this->applySearchCriteria($queryBuilder, $search['value'], $search['properties']);
        }

        parent::applyCriteria($queryBuilder, $criteria);
    }

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
            if (!empty($order) && !$this->addAssociateOrder($queryBuilder, $property, $order)) {
                $queryBuilder->addOrderBy($this->getPropertyName($property), $order);
            }
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param $propertyPath
     * @param $usingHidden
     *
     * @return bool|string
     */
    private function addAssociation(QueryBuilder $queryBuilder, $propertyPath, $usingHidden = true)
    {
        // simple join
        if (false !== strpos($propertyPath, '.')) {
            // single level
            list($key, $field) = explode('.', $propertyPath);

            if ($key !== $this->getAlias()) {
                $associations = $this->getClassMetadata()->getAssociationMappings();

                if ($associations[$key]['type'] === ClassMetadataInfo::MANY_TO_MANY) {
                    throw new NotAcceptableHttpException('Cannot order associtated Many-To-Many object type.');
                }

                $join = $this->getPropertyName($key);
                $alias = '_'.$key;
                $hidden = '__'.$key;

                $queryBuilder
                    ->join($join, $alias)
                ;

                if ($usingHidden) {
                    $queryBuilder
                        ->addSelect(sprintf('%s.%s AS HIDDEN %s', $alias, $field, $hidden))
                    ;
                }

                return array($alias, $field , $hidden);
            }
        }

        return false;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string       $name
     * @param string       $order
     *
     * @return bool|string
     */
    private function addAssociateOrder(QueryBuilder $queryBuilder, $name, $order)
    {
        if ($hidden = $this->addAssociation($queryBuilder, $name)) {
            $queryBuilder->orderBy($name, $order);
            return $hidden;
        }

        return false;
    }

    public function bulkUpdate(array $paths, array $criteria = array())
    {
        $queryBuilder = $this->_em->createQueryBuilder()
            ->update($this->_entityName, $this->getAlias())
        ;

        $parameters = array();

        foreach ($paths as $path => $value) {
            $queryBuilder->set($this->getPropertyName($path), ':'.$path);
            $parameters[$path] = $value;
        }

        foreach ($criteria as $key => $value) {
            $parameters[$key] = $value;
        }

        $this->applyCriteria($queryBuilder, $criteria);

        return $this->_em
            ->createQuery($queryBuilder->getDQL())
            ->execute($parameters)
        ;
    }
}
