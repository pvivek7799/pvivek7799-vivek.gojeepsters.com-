<?php

/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\Onestepcheckout\Model\System\Config\Source;

/**
 * Class Shipping
 *
 * @category MW
 * @package  MW_Onestepcheckout
 * @module   Onestepcheckout
 * @author   MW Developer
 */
class Blocks implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Cms\Model\ResourceModel\Block\CollectionFactory
     */
    protected $_blockColFactory;

    /**
     * Blocks constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockColFactory
     * @param \Magento\Framework\ObjectManagerInterface          $objectManager
     * @param array                                              $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockColFactory,
        array $data = []
    ) {
        $this->_blockColFactory = $blockColFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('-- Please select --'),
                'value' => '',
            ],
        ];
        $blockCollection = $this->_blockColFactory->create();
        foreach ($blockCollection as $block) {
            $options[] = [
                'label' => $block->getTitle(),
                'value' => $block->getBlockId(),
                'identifier' => $block->getIdentifier(),
            ];
        }
        return $options;
    }
}
