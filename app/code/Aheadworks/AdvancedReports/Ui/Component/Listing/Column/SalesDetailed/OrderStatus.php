<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\Component\Listing\Column\SalesDetailed;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Aheadworks\AdvancedReports\Model\Source\OrderStatus as OrderStatusSource;

/**
 * Class OrderStatus
 * @package Aheadworks\AdvancedReports\Ui\Component\Listing\Column\SalesDetailed
 */
class OrderStatus extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var OrderStatusSource
     */
    private $orderStatusSource;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param OrderStatusSource $orderStatusSource
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderStatusSource $orderStatusSource,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->orderStatusSource = $orderStatusSource;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getName()] = $this->orderStatusSource->getOptionByValue($item[$this->getName()]);
            }
        }
        return $dataSource;
    }
}
