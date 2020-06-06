<?php
/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\Onestepcheckout\Plugin\Catalog\Helper;

/**
 * Class Image
 * @package MW\Onestepcheckout\Plugin\Catalog\Helper
 */
class Image extends \Magento\Catalog\Helper\Image
{
    /**
     * @var \MW\Onestepcheckout\Helper\Config
     */
    protected $_helperConfig;

    /**
     * Image constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\Product\ImageFactory $productImageFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\ConfigInterface $viewConfig
     * @param \MW\Onestepcheckout\Helper\Config $helperConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\Product\ImageFactory $productImageFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\ConfigInterface $viewConfig,
        \MW\Onestepcheckout\Helper\Config $helperConfig
    ) {
        $this->_helperConfig = $helperConfig;
        parent::__construct($context, $productImageFactory, $assetRepo, $viewConfig);
    }

    /**
     * @return int
     */
    public function afterGetWidth(\Magento\Catalog\Helper\Image $subject, $result)
    {
        if ($this->_helperConfig->getFullRequest() == 'checkout_index_index' && $this->_helperConfig->isEnabledOneStep()) {
            return 350;
        } else {
            return $result;
        }
    }

    /**
     * @return int
     */
    public function afterGetHeight(\Magento\Catalog\Helper\Image $subject, $result)
    {
        if ($this->_helperConfig->getFullRequest() == 'checkout_index_index' && $this->_helperConfig->isEnabledOneStep()) {
            return 350;
        } else {
            return $result;
        }
    }
}
