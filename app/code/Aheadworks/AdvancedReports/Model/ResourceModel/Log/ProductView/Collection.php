<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\ResourceModel\Log\ProductView;

use Aheadworks\AdvancedReports\Model\Log\ProductView as LogProductView;
use Aheadworks\AdvancedReports\Model\ResourceModel\Log\ProductView as ResourceLogProductView;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package Aheadworks\AdvancedReports\Model\ResourceModel\Log\ProductView
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init(LogProductView::class, ResourceLogProductView::class);
    }
}
