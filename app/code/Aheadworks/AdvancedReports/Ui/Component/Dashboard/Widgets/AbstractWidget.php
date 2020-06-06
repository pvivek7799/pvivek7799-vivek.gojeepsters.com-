<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\Component\Dashboard\Widgets;

use Aheadworks\AdvancedReports\Ui\Component\OptionsContainer;

/**
 * Class AbstractWidget
 *
 * @package Aheadworks\AdvancedReports\Ui\Component\Dashboard\Widgets
 */
abstract class AbstractWidget extends OptionsContainer
{
    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $this->addOptions();

        parent::prepare();
    }

    /**
     * Add options
     *
     * @return $this
     */
    protected function addOptions()
    {
        $config = $this->getData('config');
        $config['options'] = $this->getMetricOptions();
        $this->setData('config', $config);

        return $this;
    }

    /**
     * Retrieve metric options
     *
     * @return array
     */
    abstract protected function getMetricOptions();

    /**
     * Retrieve Sales report metric options
     *
     * @return array
     */
    protected function getSalesReportMetrics()
    {
        return [
            [
                'value' => 'sales',
                'label' => __('Sales'),
                'additionalClasses' => ['not-active' => true],
                'url' => $this->getReportBaseUrl('salesoverview'),
                'chartConfig' => [
                    'xAxis' => 'period'
                ]
            ],
            [
                'value' => 'sales.total',
                'label' => __('Total'),
                'columnType' => 'price'
            ],
            [
                'value' => 'sales.orders_count',
                'label' => __('Number of Orders')
            ],
            [
                'value' => 'sales.discount',
                'label' => __('Discounts'),
                'columnType' => 'price'
            ],
            [
                'value' => 'sales.invoiced',
                'label' => __('Invoiced'),
                'columnType' => 'price'
            ],
            [
                'value' => 'sales.refunded',
                'label' => __('Refunded'),
                'columnType' => 'price'
            ],
            [
                'value' => 'sales.order_items_count',
                'label' => __('Items Ordered')
            ],
            [
                'value' => 'sales.avg_order_amount',
                'label' => __('Avg. Order Value'),
                'columnType' => 'price'
            ],
            [
                'value' => 'sales.tax',
                'label' => __('Tax'),
                'columnType' => 'price'
            ]
        ];
    }

    /**
     * Retrieve forecast Sales report metric options
     *
     * @return array
     */
    protected function getForecastSalesReportMetrics()
    {
        return [
            [
                'value' => 'sales',
                'label' => __('Forecast: Sales'),
                'additionalClasses' => ['not-active' => true]
            ],
            [
                'value' => 'sales.total',
                'label' => __('Forecast: Total'),
                'columnType' => 'price'
            ],
            [
                'value' => 'sales.order_items_count',
                'label' => __('Forecast: Items Ordered')
            ]
        ];
    }

    /**
     * Retrieve Traffic and Conversions report metric options
     *
     * @return array
     */
    protected function getTrafficAndConversionsReportMetrics()
    {
        return [
            [
                'value' => 'traffic_and_conversions',
                'label' => __('Traffic and Conversions'),
                'additionalClasses' => ['not-active' => true],
                'url' => $this->getReportBaseUrl('conversion'),
                'chartConfig' => [
                    'xAxis' => 'period'
                ]
            ],
            [
                'value' => 'traffic_and_conversions.views_count',
                'label' => __('Unique Visitors')
            ],
            [
                'value' => 'traffic_and_conversions.conversion_rate',
                'label' => __('Conversion Rate, %'),
                'columnType' => 'percent'
            ]
        ];
    }

    /**
     * Retrieve Abandoned Carts report metric options
     *
     * @return array
     */
    protected function getAbandonedCartsReportMetrics()
    {
        return [
            [
                'value' => 'abandoned_carts',
                'label' => __('Abandoned Carts'),
                'additionalClasses' => ['not-active' => true],
                'url' => $this->getReportBaseUrl('abandonedcarts'),
                'chartConfig' => [
                    'xAxis' => 'period'
                ]
            ],
            [
                'value' => 'abandoned_carts.abandoned_carts',
                'label' => __('Abandoned Carts')
            ],
            [
                'value' => 'abandoned_carts.abandoned_carts_total',
                'label' => __('Abandoned Carts Total'),
                'columnType' => 'price'
            ],
            [
                'value' => 'abandoned_carts.abandonment_rate',
                'label' => __('Abandonment Rate'),
                'columnType' => 'percent'
            ]
        ];
    }

    /**
     * Retrieve report url from dashboard
     *
     * @param string $reportName
     * @return string
     */
    protected function getReportBaseUrl($reportName)
    {
        $filterPool = $this->context->getDataProvider()->getDefaultFilterPool();
        $customerGroupFilter = $filterPool->getFilter('customer_group');
        $periodFilter = $filterPool->getFilter('period');
        $storeFilter = $filterPool->getFilter('store');

        $params = [
            '_query' => [
                'customer_group_id' => $customerGroupFilter->getDefaultValue(),
                'period_type' => $periodFilter->getDefaultPeriodType(),
                'report_scope' => $storeFilter->getDefaultValue(),
                'compare_type' => $periodFilter->getDefaultCompareType()
            ]
        ];
        $url = $this->getContext()->getUrl('advancedreports/' . $reportName . '/index', $params);

        return $url;
    }
}
