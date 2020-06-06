<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Finder\Block;

use Amasty\Finder\Model\Finder;
use Amasty\Finder\Model\Source\DisplayType;

class Form extends \Magento\Framework\View\Element\Template
{
    const SIZE_FOR_BUTTONS = 1;

    const HORIZONTAL = 'horizontal';
    const ALL_SIZE = '100';

    /**
     * @var bool
     */
    private $isApplied = false;

    /**
     * @var \Amasty\Finder\Model\Finder
     */
    private $finder;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    private $catalogLayer;

    /**
     * @var \Magento\Framework\Url\Encoder
     */
    private $urlEncoder;

    /**
     * @var int
     */
    private $parentDropdownId = 0;

    /**
     * @var \Amasty\Finder\Api\FinderRepositoryInterface
     */
    private $finderRepository;

    /**
     * @var \Amasty\Finder\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Amasty\Finder\Model\Dropdown
     */
    private $dropdownModel;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Url\Encoder $urlEncoder,
        \Amasty\Finder\Api\FinderRepositoryInterface $finderRepository,
        \Amasty\Finder\Helper\Config $configHelper,
        \Amasty\Finder\Model\Dropdown $dropdownModel,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        $this->catalogLayer = $layerResolver->get();
        $this->urlEncoder = $urlEncoder;
        $this->finderRepository = $finderRepository;
        $this->configHelper = $configHelper;
        $this->dropdownModel = $dropdownModel;
        parent::__construct($context, $data);
        $this->apply();
    }

    /** @return \Amasty\Finder\Model\Finder */
    public function getFinder()
    {
        if ($this->finder === null) {
            $this->finder = $this->finderRepository->getById($this->getId());
        }
        return $this->finder;
    }

    /**
     * @return bool
     */
    public function isButtonsVisible()
    {
        $cnt = count($this->getFinder()->getDropdowns());

        // we have just 1 dropdown. show the button
        if (self::SIZE_FOR_BUTTONS == $cnt) {
            return true;
        }

        $partialSearch = !!$this->configHelper->getConfigValue('general/partial_search');

        // at least one value is selected and we allow partial search
        if ($this->getFinder()->getSavedValue('current') && $partialSearch) {
            return true;
        }

        // all values are selected.
        if (($this->getFinder()->getSavedValue(Finder::LAST_DROPDOWN))) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    private function getAjaxUrl()
    {
        $isCurrentlySecure = (bool)$this->_storeManager->getStore()->isCurrentlySecure();
        $secure = $isCurrentlySecure ? true : false;
        $url = $this->getUrl('amfinder/index/options', ['_secure' => $secure]);

        return $url;
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        $securedFlag = $this->_storeManager->getStore()->isCurrentlySecure();
        $secured = ['_secure' => $securedFlag];

        $url = $this->getUrl('amfinder', $secured);

        if ($customUrl = $this->getCustomUrl($secured)) {
            return $this->formatUrl($customUrl);
        }

        $category = $this->coreRegistry->registry('current_category');

        if ($this->coreRegistry->registry('current_product')) {
            return $this->formatUrl($url);
        }

        if ($category && $category->getDisplayMode() == \Magento\Catalog\Model\Category::DM_PAGE) {
            return $this->formatUrl($url);
        }

        $url = $this->_urlBuilder->getCurrentUrl();

        return $this->formatUrl($url);
    }

    /**
     * @param \Amasty\Finder\Model\Dropdown $dropdown
     *
     * @return string
     */
    public function getDropdownAttributes(\Amasty\Finder\Model\Dropdown $dropdown)
    {
        $html = sprintf(
            'id="finder-%d--%d" data-dropdown-id="%d"',
            $this->getId(),
            $dropdown->getId(),
            $dropdown->getId()
        );

        if (DisplayType::DROPDOWN === (int)$dropdown->getDisplayType()) {
            $html .= sprintf(
                'name="finder[%d]"',
                $dropdown->getId()
            );
        }

        $parentValueId = $this->getFinder()->getSavedValue($this->getParentDropdownId());
        $currentValueId = $this->getFinder()->getSavedValue($dropdown->getId());

        if ($this->dropdownModel->isHidden($dropdown, $this->getFinder()) && !$parentValueId && !$currentValueId) {
            $html .= 'disabled = "disabled"';
        }

        return $html;
    }

    /**
     * @param $secured
     * @return bool|string
     */
    private function getCustomUrl($secured)
    {
        $customUrl = $this->getFinder()->getCustomUrl()
            ?: $this->configHelper->getConfigValue('general/custom_category');

        $url = false;
        if ($customUrl) {
            $url = $this->_urlBuilder->getCurrentUrl();

            if (strpos($url, $customUrl) === false) {
                $url = $this->getUrl($customUrl, $secured);
            }
        }

        if (!$customUrl && $this->_request->getFullActionName() == 'cms_index_index') {
            $url = $this->getUrl('amfinder', $secured);
        }

        return trim($url, "/");
    }

    /**
     * @return string
     */
    public function getResetUrl()
    {
        if ($this->configHelper->getConfigValue('general/reset_home') == 'current' ||
            $this->_request->getFullActionName() == 'cms_index_index'
        ) {
            return $this->formatUrl($this->_urlBuilder->getCurrentUrl());
        } else {
            return $this->getBackUrl();
        }
    }

    /**
     * @return string
     */
    public function getActionUrl()
    {
        $securedFlag = $this->_storeManager->getStore()->isCurrentlySecure();
        $url = $this->getUrl('amfinder/index/search', ['_secure' => $securedFlag]);

        return $url;
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    protected function _toHtml()
    {
        $finderId = $this->getId();
        if (!$finderId) {
            return __('Please specify the Parts Finder ID');
        }

        $finder = $this->getFinder();
        if (!$finder->getId()) {
            return __('Please specify an existing Parts Finder ID');
        }

        if (!$this->coreRegistry->registry($finderId)) {
            $this->coreRegistry->register($finderId, true);
        } else {
            return false;
        }

        $this->setLocation($this->getLocation() . $this->coreRegistry->registry('cms_amfinder'));

        return parent::_toHtml();
    }

    /**
     * @return $this
     */
    private function apply()
    {
        if ($this->isApplied) {
            return $this;
        }

        $this->_template = 'amfinder.phtml';

        $this->isApplied = true;

        $finder = $this->getFinder();
        $urlParam = $this->getRequest()->getParam('find');

        // XSS disabling
        $filter = ["<", ">"];
        $urlParam = str_replace($filter, "|", $urlParam);
        $urlParam = htmlspecialchars($urlParam);

        if ($urlParam) {
            $urlParam = $finder->parseUrlParam($urlParam);
            $current = $finder->getSavedValue('current');

            if ($urlParam && ($current != $urlParam)) {
                // url has higher priority than session
                $dropdowns = $finder->getDropdownsByCurrent($urlParam);
                $finder->saveFilter($dropdowns, $this->getCurrentCategoryId(), [$this->getCurrentApplyUrl()]);
            }
        }

        $isUniversal = (bool)$this->configHelper->getConfigValue('advanced/universal');
        $isUniversalLast = (bool)$this->configHelper->getConfigValue('advanced/universal_last');

        if ($this->paramsExist()) {
            $finder->applyFilter($this->catalogLayer, $isUniversal, $isUniversalLast);
        }

        return $this;
    }

    /**
     * @return bool
     */
    private function paramsExist()
    {
        return strpos($this->_urlBuilder->getCurrentUrl(), 'find=') !== false;
    }

    /**
     * @param $url
     * @return string
     */
    private function formatUrl($url)
    {
        if ($this->_storeManager->getStore()->isCurrentlySecure()) {
            $url = str_replace("http://", "https://", $url);
        }

        return $this->urlEncoder->encode($url);
    }

    /**
     * @return int
     */
    public function getCurrentCategoryId()
    {
        return $this->catalogLayer->getCurrentCategory()->getId();
    }

    /**
     * @return string
     */
    public function getJsonConfig()
    {
        return $this->jsonEncoder->encode([
            'ajaxUrl' => $this->getAjaxUrl(),
            'isPartialSearch' => (int)$this->configHelper->getConfigValue('general/partial_search'),
            'autoSubmit' => (int)$this->configHelper->getConfigValue('advanced/auto_submit'),
            'isChosenEnable' => (int)$this->configHelper->getConfigValue('advanced/is_chosen_enable'),
            'containerId' => 'amfinder_' . $this->getFinder()->getId(),
            'loadingText' => __('Loading...')
        ]);
    }

    /**
     * @return array|string
     */
    private function getCurrentApplyUrl()
    {
        $currentUrl = $this->_urlBuilder->getCurrentUrl();
        $currentUrl = explode('?', $currentUrl);
        $currentUrl = array_shift($currentUrl);
        return $currentUrl;
    }

    /**
     * @return string
     */
    public function getCurrentApplyUrlEncoded()
    {
        $currentUrl = $this->getCurrentApplyUrl();
        return $this->urlEncoder->encode($currentUrl);
    }

    /**
     * @return float|string
     */
    public function getDropdownWidth()
    {
        $isMobile = isset($_SERVER['HTTP_USER_AGENT']) && stristr($_SERVER['HTTP_USER_AGENT'], 'mobi') !== false;
        $finder = $this->getFinder();

        if (!$isMobile) {
            $result = $finder->getTemplate() == self::HORIZONTAL
                ? floor(100 / count($finder->getDropdowns()) - self::SIZE_FOR_BUTTONS) : '';
        }

        return isset($result) ? $result : self::ALL_SIZE;
    }

    /**
     * @param $finder
     * @param $dropdown
     * @return string
     */
    public function getDropdownHtml($finder, $dropdown)
    {
        $dropdownHtml = $this->getLayout()->createBlock(\Amasty\Finder\Block\DropdownRenderer::class)
            ->setDropdown($dropdown)
            ->setFinder($finder)
            ->setParentDropdownId($this->getParentDropdownId())
            ->toHtml();

        $this->setParentDropdownId($dropdown->getId());

        return $dropdownHtml;
    }

    /**
     * @return bool
     */
    public function getHideClassName()
    {
        return $this->getFinder()->getDefaultCategory() && $this->getFinder()->isHideFinder();
    }

    /**
     * @return int
     */
    public function getParentDropdownId()
    {
        return $this->parentDropdownId;
    }

    /**
     * @param int $parentDropdownId
     */
    public function setParentDropdownId($parentDropdownId)
    {
        $this->parentDropdownId = $parentDropdownId;
    }
}
