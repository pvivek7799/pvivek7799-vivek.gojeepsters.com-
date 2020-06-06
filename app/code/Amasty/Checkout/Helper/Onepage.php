<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Helper;

use Amasty\Checkout\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Json\Helper\Data;
use Amasty\Checkout\Model\Config;

/**
 * Class Onepage
 */
class Onepage extends AbstractHelper
{
    /**
     * @var CollectionFactory
     */
    protected $regionsFactory;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @var Config
     */
    private $configProvider;

    public function __construct(
        Context $context,
        CollectionFactory $regionsFactory,
        Data $jsonHelper,
        Config $configProvider
    ) {
        parent::__construct($context);
        $this->regionsFactory = $regionsFactory;
        $this->jsonHelper = $jsonHelper;
        $this->configProvider = $configProvider;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->configProvider->getTitle();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->configProvider->getDescription();
    }

    /**
     * @return bool
     */
    public function isAddressSuggestionEnabled()
    {
        return $this->configProvider->isAddressSuggestionEnabled();
    }

    /**
     * @return string
     */
    public function getGoogleMapsKey()
    {
        return $this->configProvider->getGoogleMapsKey();
    }

    /**
     * @return string
     */
    public function getRegionsJson()
    {
        return $this->jsonHelper->jsonEncode($this->getRegions());
    }

    /**
     * @return array
     */
    public function getRegions()
    {
        /** @var \Amasty\Checkout\Model\ResourceModel\Region\Collection $collection */
        $collection = $this->regionsFactory->create();

        return $collection->fetchRegions();
    }
}
