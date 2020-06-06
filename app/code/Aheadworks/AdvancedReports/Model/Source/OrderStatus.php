<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Source;

/**
 * Class OrderStatus
 *
 * @package Aheadworks\AdvancedReports\Model\Source
 */
class OrderStatus implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var string[]
     */
    private $stateStatuses = [
        \Magento\Sales\Model\Order::STATE_NEW,
        \Magento\Sales\Model\Order::STATE_PROCESSING,
        \Magento\Sales\Model\Order::STATE_COMPLETE,
        \Magento\Sales\Model\Order::STATE_CLOSED,
        \Magento\Sales\Model\Order::STATE_CANCELED,
        \Magento\Sales\Model\Order::STATE_HOLDED,
    ];

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    private $orderConfig;

    /**
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     */
    public function __construct(\Magento\Sales\Model\Order\Config $orderConfig)
    {
        $this->orderConfig = $orderConfig;
    }

    /**
     * Get options
     *
     * @return []
     */
    public function toOptionArray()
    {
        $statuses = $this->stateStatuses
            ? $this->orderConfig->getStateStatuses($this->stateStatuses)
            : $this->orderConfig->getStatuses();

        foreach ($statuses as $code => $label) {
            $options[] = ['value' => $code, 'label' => $label];
        }
        return $options;
    }

    /**
     * Get options
     *
     * @return []
     */
    public function getOptions()
    {
        $options = $this->toOptionArray();
        $result = [];

        foreach ($options as $option) {
            $result[$option['value']] = $option['label'];
        }

        return $result;
    }

    /**
     * Get option by value
     *
     * @param int $value
     * @return null
     */
    public function getOptionByValue($value)
    {
        $options = $this->getOptions();
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }
        return null;
    }
}
