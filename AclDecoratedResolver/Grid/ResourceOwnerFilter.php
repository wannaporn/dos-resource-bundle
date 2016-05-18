<?php

namespace DoS\ResourceBundle\AclDecoratedResolver\Grid;

use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ResourceOwnerFilter implements FilterInterface
{
    const TYPE = 'resource_owner';
    const FIELD = 'user';

    /**
     * {@inheritdoc}
     */
    public function apply(DataSourceInterface $dataSource, $name, $data, array $options)
    {
        if (empty($data)) {
            return;
        }

        $field = isset($options['field']) ? $options['field'] : $name;

        $dataSource->restrict($dataSource->getExpressionBuilder()->equals($field, $data));
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }
}
