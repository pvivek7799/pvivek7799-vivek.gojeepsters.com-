<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\DataProvider\Filters\IndividualFilter\CustomerSales;

use Aheadworks\AdvancedReports\Ui\DataProvider\Filters\FilterApplierInterface;
use Aheadworks\AdvancedReports\Model\ResourceModel\CustomerSales\Range as CustomerSalesRangeResource;

/**
 * Class WebsiteIdApplier
 *
 * @package Aheadworks\AdvancedReports\Ui\DataProvider\Filters\IndividualFilter\ReportSettings
 */
class WebsiteIdApplier implements FilterApplierInterface
{
    /**
     * @var CustomerSalesRangeResource
     */
    private $customerSalesRangeResource;

    /**
     * @param CustomerSalesRangeResource $customerSalesRangeResource
     */
    public function __construct(CustomerSalesRangeResource $customerSalesRangeResource)
    {
        $this->customerSalesRangeResource = $customerSalesRangeResource;
    }

    /**
     * {@inheritdoc}
     */
    public function apply($collection, $filterPool)
    {
        $websiteId = $filterPool->getFilter('store')->getWebsiteId();
        if (!$this->customerSalesRangeResource->hasConfigValuesForWebsite($websiteId)) {
            $websiteId = 0;
        }

        $collection->setWebsiteId($websiteId);
    }
}
