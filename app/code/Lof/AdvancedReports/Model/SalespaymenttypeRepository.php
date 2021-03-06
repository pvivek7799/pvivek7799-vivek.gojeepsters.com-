<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Lof_AdvancedReports
 * @copyright  Copyright (c) 2017 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\AdvancedReports\Model;
use Lof\AdvancedReports\Api\SalespaymenttypeInterface;
use Lof\AdvancedReports\Model\AbstractReport;
use Magento\Framework\Api\SortOrder;
 
class SalespaymenttypeRepository extends AbstractReport implements SalespaymenttypeInterface
{
    protected $_limit = 10;
    /**
     * GROUP BY criteria
     *
     * @var string
     */
    protected $_columnDate = 'main_table.created_at';
     protected $_defaultSort = 'orders_count';
    protected $_defaultDir = 'DESC';

    public function __construct(   
        \Lof\AdvancedReports\Helper\Data $helperData, 
        \Magento\Framework\ObjectManagerInterface $objectManager, 
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Lof\AdvancedReports\Helper\Api\Datefield $helperDatefield, 
        \Lof\AdvancedReports\Api\Data\SalespaymenttypedataInterfaceFactory $searchResultsFactory
        )
    {
        $this->searchResultsFactory = $searchResultsFactory;
        parent::__construct($helperData, $objectManager, $storeManager, $localeCurrency, $searchCriteriaBuilder, $localeLists, $helperDatefield);
    }
    /**
     * {@inheritdoc}
     */
    public function getResourceCollectionName()
    {
        return 'Lof\AdvancedReports\Model\ResourceModel\Sales\Collection';
    }

    /**
    * @return \Magento\Framework\DataObject
    */
    public function initFilterData($filter_field = []) {
        $requestData = [];
        $lofFilter = isset($filter_field['lofFilter'])?isset($filter_field['lofFilter']):null;
        $storeIds = isset($filter_field['storeIds'])?isset($filter_field['storeIds']):null;

        if($lofFilter) {
            $requestData = $this->_objectManager->get(
                'Magento\Backend\Helper\Data'
            )->prepareFilterString(
                $lofFilter
            );
        }

        $requestData['store_ids'] = $storeIds;

        if(!isset($requestData['report_field']) || !$requestData['report_field']) {
          $requestData['report_field'] = isset($filter_field['report_field'])?$filter_field['report_field']:$this->_columnDate;
        }
        if(!isset($requestData['filter_from']) || !$requestData['filter_from']) {
          $requestData['filter_from'] = isset($filter_field['filter_from'])?$filter_field['filter_from']:"";
        }
        if(!isset($requestData['filter_to']) || !$requestData['filter_to']) {
          $requestData['filter_to'] = isset($filter_field['filter_to'])?$filter_field['filter_to']:"";
        }
        
        if(!isset($requestData['group_by']) || !$requestData['group_by']) {
          $requestData['group_by'] = isset($filter_field['group_by'])?$filter_field['group_by']:"month";
        }

        if(!isset($requestData['product_sku']) || !$requestData['product_sku']) {
          $requestData['product_sku'] = isset($filter_field['product_sku'])?$filter_field['product_sku']:"";
        }

        if(!isset($requestData['show_actual_columns']) || !$requestData['show_actual_columns']) {
          $requestData['show_actual_columns'] = isset($filter_field['show_actual_columns'])?$filter_field['show_actual_columns']:0;
        }
       
        if(!isset($requestData['show_order_statuses']) || ($requestData['show_order_statuses'] == NULL && $requestData['show_order_statuses'] == "")) {
          $requestData['show_order_statuses'] = isset($filter_field['show_order_statuses'])?(int)$filter_field['show_order_statuses']:1;;
        }

        if(!isset($requestData['order_statuses'])) {
            $requestData['order_statuses'] =  isset($filter_field['order_statuses'])?$filter_field['order_statuses']:"complete";
        }
        if($requestData['show_order_statuses'] == 0) {
            $requestData['order_statuses'] = "";
        }

        $params = new \Magento\Framework\DataObject();

        foreach ($requestData as $key => $value) {
            if (!empty($value)) {
                $params->setData($key, $value);
            }
        }
        $this->setFilterData($params);

        return $params;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Lof\AdvancedReports\Api\Data\SalespaymenttypedataInterface
     */
    public function getSalesPaymenttype(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria) {
        $cur_month = date("m");
        $cur_year = date("Y");
        $filter_fields = [
                        "show_order_statuses"=>1,
                        "filter_from"=>$cur_month."/01/".$cur_year,
                        "filter_to"=>date("m/d/Y"),
                        "order_statuses"=>"complete",
                        "group_by"=>"month",
                        "product_sku" =>"",
                        "show_actual_columns" => 0,
                        "report_field"=>"main_table.created_at",
                        "lofFilter"=>"",
                        "storeIds"=>""];

        //Convert search criteria to specify filter params
        
        foreach ($searchCriteria->getFilterGroups() as $group) {
            if(!$group)
                continue;
            //var \Magento\Framework\Api\Search\FilterGroup $group
            foreach ($group->getFilters() as $filter) {
                $field = $filter->getField();
                $value = $filter->getValue();
                if($field != "filter_groups" && $field != "sort_orders" && isset($filter_fields[$field])) {
                    $filter_fields[$field] = $value;
                }
            }
        }

        //Init filter data to convert into a filter object of the report
        $this->initFilterData($filter_fields);

        $filterData = $this->getFilterData();
        $store_ids = $this->_getStoreIds();
  
        $resourceCollection = $this->_objectManager->create($this->getResourceCollectionName())
            ->preparePaymentReportCollection()
            ->setDateColumnFilter($this->_columnDate)
            ->addDateFromFilter($filterData->getData('filter_from', null))
            ->addDateToFilter($filterData->getData('filter_to', null))
            ->addStoreFilter($store_ids);

        $this->_addOrderStatusFilter($resourceCollection, $filterData);
        $resourceCollection->getSelect() 
                            ->group('method');
        /** @var SortOrder $sortOrder */
        $set_order = false;
        if((array)$searchCriteria->getSortOrders()){
            foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
                $field = $sortOrder->getField();
                if($field) {
                    $set_order = true;
                    $resourceCollection->addOrder(
                        $field,
                        ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                    );
                }
            }
        }

        if(!$set_order) {
            $resourceCollection->getSelect() 
                            ->order(new \Zend_Db_Expr($this->_defaultSort." ".$this->_defaultDir));
        }

        if($currentPage = $searchCriteria->getCurrentPage()) {
            $resourceCollection->setCurPage($currentPage);
        }
        if($pageSize = $searchCriteria->getPageSize()) {
            $resourceCollection->setPageSize($pageSize);
        }

        $resourceCollection->applyCustomFilter();
        $resourceCollection->load();

        $this->_convertGridData($resourceCollection);
        //init \Lof\AdvancedReports\Api\Data\SalesproducttypedataInterface
        $searchResult = $this->searchResultsFactory->create();

        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($resourceCollection->getItems());
        $searchResult->setTotalCount($resourceCollection->getSize());
        return $searchResult;
    }

    protected function _getPeriodType () {
        $filterData = $this->getFilterData();
        return $filterData->getData("group_by");
    }

    protected function _convertGridData(&$collection = null) {

        if($collection && $collection->getSize()) {
            $payment_methods = $this->_getPaymentMethods();
            foreach($collection as &$item) {
                $payment_method = $item->getMethod();
                $method_label = isset($payment_methods[$payment_method])?$payment_methods[$payment_method]:$payment_method;
                $item->setData("method_label", $method_label);
                $total_income_currency = $this->formatCurrency($item->getTotalIncomeAmount());
                $item->setData("total_income_currency", $total_income_currency);
                $total_revenue_currency = $this->formatCurrency($item->getTotalRevenueAmount());
                $item->setData("total_revenue_currency", $total_revenue_currency);
                $total_profit_currency = $this->formatCurrency($item->getTotalProfitAmount());
                $item->setData("total_profit_currency", $total_profit_currency);
                $total_invoiced_currency = $this->formatCurrency($item->getTotalInvoicedAmount());
                $item->setData("total_invoiced_currency", $total_invoiced_currency);
                $total_paid_currency = $this->formatCurrency($item->getTotalPaidAmount());
                $item->setData("total_paid_currency", $total_paid_currency);
                $total_refunded_currency = $this->formatCurrency($item->getTotalRefundedAmount());
                $item->setData("total_refunded_currency", $total_refunded_currency);
                $total_tax_currency = $this->formatCurrency($item->getTotalTaxAmount());
                $item->setData("total_tax_currency", $total_tax_currency);
                $total_tax_actual_currency = $this->formatCurrency($item->getTotalTaxAmountActual());
                $item->setData("total_tax_actual_currency", $total_tax_actual_currency);
                $total_shipping_currency = $this->formatCurrency($item->getTotalShippingAmount());
                $item->setData("total_shipping_currency", $total_shipping_currency);
                $total_shipping_actual_currency = $this->formatCurrency($item->getTotalShippingAmountActual());
                $item->setData("total_shipping_actual_currency", $total_shipping_actual_currency);
                $total_discount_currency = $this->formatCurrency($item->getTotalDiscountAmount());
                $item->setData("total_discount_currency", $total_discount_currency);
                $total_discount_actual_currency = $this->formatCurrency($item->getTotalDiscountAmountActual());
                $item->setData("total_discount_actual_currency", $total_discount_actual_currency);
                $total_canceled_currency = $this->formatCurrency($item->getTotalCanceledAmount());
                $item->setData("total_canceled_currency", $total_canceled_currency);
            }
        }
        return $collection;
    }

    protected function _getPaymentMethods() {
        $payments = $this->_objectManager->create('Magento\Payment\Model\Config')->getActiveMethods();
        $method = array();
        foreach($payments as $paymentCode => $paymentModel){
            $paymentTitle = $this->_storeManager->getStore()->getConfig('payment/'.$paymentCode.'/title');
            $methods[$paymentCode] = $paymentTitle;
        }
        return $methods;
    }
}