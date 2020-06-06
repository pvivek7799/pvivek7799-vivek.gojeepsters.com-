<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Toolbar\Menu\Item;

use Aheadworks\AdvancedReports\Ui\Component\Listing\Breadcrumbs;
use Aheadworks\AdvancedReports\Model\Toolbar\Menu\ItemInterface;
use Magento\Framework\UrlInterface;

/**
 * Class Modifier
 *
 * @package Aheadworks\AdvancedReports\Model\Toolbar\Menu\Item
 */
class Modifier
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Breadcrumbs
     */
    private $breadcrumbs;

    /**
     * @param UrlInterface $urlBuilder
     * @param Breadcrumbs $breadcrumbs
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Breadcrumbs $breadcrumbs
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->breadcrumbs = $breadcrumbs;
    }

    /**
     * Modify item data
     *
     * @param ItemInterface $item
     * @return array
     */
    public function modify($item)
    {
        $options = [];
        $options['linkAttributes'] = $this->prepareLinkAttributes($item);
        $options['additionalClasses'] = $this->prepareAdditionalClasses($item);
        $options['label'] = $item->getLabel();
        $options['value'] = $this->getUrl($item->getPath());
        $options['isCurrent'] = $this->isCurrent($item);

        return $options;
    }

    /**
     * Prepare attributes of the link
     *
     * @param ItemInterface $item
     * @return array
     */
    private function prepareLinkAttributes($item)
    {
        $linkAttributes = is_array($item->getLinkAttributes()) ? $item->getLinkAttributes() : [];
        if (!isset($linkAttributes['href'])) {
            $linkAttributes['href'] = $this->getUrl($item->getPath());
        }

        return $linkAttributes;
    }

    /**
     * Prepare additional classes
     *
     * @param ItemInterface $item
     * @return array
     */
    private function prepareAdditionalClasses($item)
    {
        $classes = [];
        if (is_array($item->getAdditionalClasses())) {
            $classes = $item->getAdditionalClasses();
        }

        if ($this->isCurrent($item)) {
            $classes['current'] = true;
        }

        return $classes;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    private function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    /**
     * Checks whether the item is current
     *
     * @param ItemInterface $item
     * @return bool
     */
    private function isCurrent($item)
    {
        $firstCrumb = $this->breadcrumbs->getFirstLastCrumb();
        $controller = $item->getController();
        return ($firstCrumb == 'productperformance_variantperformance' && $controller == 'productperformance')
            || $firstCrumb == $controller;
    }
}
