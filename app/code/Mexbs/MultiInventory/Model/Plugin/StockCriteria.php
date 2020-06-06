<?php
namespace Mexbs\MultiInventory\Model\Plugin;

use Closure;

class StockCriteria{
    public function aroundSetScopeFilter(
        \Magento\CatalogInventory\Model\ResourceModel\Stock\StockCriteria $subject,
        Closure $proceed,
        $scope
    )
    {
        $result = $proceed($scope);
        $subject->addFilter('website_filter', 'website_id', $scope);
        return $result;
    }
}