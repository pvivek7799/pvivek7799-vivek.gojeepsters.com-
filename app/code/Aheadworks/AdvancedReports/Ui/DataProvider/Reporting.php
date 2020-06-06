<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\DataProvider;

use Aheadworks\AdvancedReports\Ui\DataProvider\Filters\FilterApplierPool;
use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;

/**
 * Class Reporting
 *
 * @package Aheadworks\AdvancedReports\Ui\DataProvider
 */
class Reporting implements ReportingInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var FilterPool
     */
    private $filterPool;

    /**
     * @var FilterApplierPool
     */
    private $reportFilterApplierPool;

    /**
     * @param CollectionFactory $collectionFactory
     * @param FilterPool $filterPool
     * @param FilterApplierPool $reportFilterApplierPool
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        FilterPool $filterPool,
        FilterApplierPool $reportFilterApplierPool
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->filterPool = $filterPool;
        $this->reportFilterApplierPool = $reportFilterApplierPool;
    }

    /**
     * {@inheritdoc}
     */
    public function search(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->getReport($searchCriteria->getRequestName());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $this->filterPool->applyFilters($collection, $searchCriteria);
        $this->reportFilterApplierPool->applyFilters($collection, $searchCriteria->getRequestName());
        foreach ($searchCriteria->getSortOrders() as $sortOrder) {
            if ($sortOrder->getField()) {
                $collection->setOrder($sortOrder->getField(), $sortOrder->getDirection());
            }
        }
        return $collection;
    }

    /**
     * Retrieve default filter pool
     *
     * @return \Aheadworks\AdvancedReports\Model\Filter\FilterPool
     */
    public function getDefaultFilterPool()
    {
        return $this->reportFilterApplierPool->getFilterPool();
    }
}
