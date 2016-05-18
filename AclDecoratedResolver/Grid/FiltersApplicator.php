<?php

namespace DoS\ResourceBundle\AclDecoratedResolver\Grid;

use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Filtering\FiltersApplicatorInterface;
use Sylius\Component\Grid\Parameters;
use Sylius\Component\Registry\ServiceRegistryInterface;

class FiltersApplicator implements FiltersApplicatorInterface
{
    /**
     * @var FiltersApplicatorInterface
     */
    private $filtersApplicator;

    /**
     * @var ServiceRegistryInterface
     */
    private $filtersRegistry;

    public function __construct(
        FiltersApplicatorInterface $filtersApplicator,
        ServiceRegistryInterface $filtersRegistry
    ) {
        $this->filtersApplicator = $filtersApplicator;
        $this->filtersRegistry = $filtersRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(DataSourceInterface $dataSource, Grid $grid, Parameters $parameters)
    {
        if ($parameters->has(ResourceOwnerFilter::TYPE)) {
            $this->filtersRegistry
                ->get(ResourceOwnerFilter::TYPE)
                ->apply($dataSource, ResourceOwnerFilter::FIELD, $parameters->get(ResourceOwnerFilter::TYPE), [])
            ;
        }

        return $this->filtersApplicator->apply($dataSource, $grid, $parameters);
    }
}
