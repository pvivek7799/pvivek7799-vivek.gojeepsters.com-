<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\DataProvider\Filters;

use Aheadworks\AdvancedReports\Model\Filter\FilterPool;
use Aheadworks\AdvancedReports\Ui\DataProvider\MetadataPool as DataProviderMetadataPool;

/**
 * Class FilterApplierPool
 *
 * @package Aheadworks\AdvancedReports\Ui\DataProvider\Filters
 */
class FilterApplierPool
{
    /**
     * @var FilterPool
     */
    private $filterPool;

    /**
     * @var DataProviderMetadataPool
     */
    private $dataProviderMetadataPool;

    /**
     * @var FilterApplierInterface[]
     */
    private $appliers;

    /**
     * @param FilterPool $filterPool
     * @param DataProviderMetadataPool $dataProviderMetadataPool
     * @param array $appliers
     */
    public function __construct(
        FilterPool $filterPool,
        DataProviderMetadataPool $dataProviderMetadataPool,
        array $appliers = []
    ) {
        $this->filterPool = $filterPool;
        $this->dataProviderMetadataPool = $dataProviderMetadataPool;
        $this->appliers = $appliers;
    }

    /**
     * Apply default filters to report collection
     *
     * @param $collection
     * @param string $dataSourceName
     * @return void
     * @throws \Exception
     */
    public function applyFilters($collection, $dataSourceName)
    {
        $metadata = $this->dataProviderMetadataPool->getMetadata($dataSourceName);

        foreach ($metadata->getIndividualFilterAppliers() as $applierName) {
            $applier = $this->getApplierByName($applierName);
            $applier->apply($collection, $this->getFilterPool());
        }
        foreach ($metadata->getDefaultFilterAppliers() as $applierName) {
            $applier = $this->getApplierByName($applierName);
            $applier->apply($collection, $this->getFilterPool());
        }
    }

    /**
     * Retrieve filter pool
     *
     * @return FilterPool
     */
    public function getFilterPool()
    {
        return $this->filterPool;
    }

    /**
     * Retrieve applier by name
     *
     * @param string $applierName
     * @return FilterApplierInterface
     * @throws \Exception
     */
    private function getApplierByName($applierName)
    {
        if (!isset($this->appliers[$applierName])) {
            throw new \Exception(sprintf('Unknown applier: %s requested', $applierName));
        }

        return $this->appliers[$applierName];
    }
}
