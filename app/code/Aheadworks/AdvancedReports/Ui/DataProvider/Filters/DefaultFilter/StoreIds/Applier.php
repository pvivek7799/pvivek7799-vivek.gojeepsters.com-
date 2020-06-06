<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\DataProvider\Filters\DefaultFilter\StoreIds;

use Aheadworks\AdvancedReports\Ui\DataProvider\Filters\FilterApplierInterface;

/**
 * Class Applier
 *
 * @package Aheadworks\AdvancedReports\Ui\DataProvider\Filters\DefaultFilter\StoreIds
 */
class Applier implements FilterApplierInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply($collection, $filterPool)
    {
        $storeIds = $filterPool->getFilter('store')->getStoreIds();
        if (!empty($storeIds)) {
            $collection->addStoreFilter($storeIds);
        }
    }
}
