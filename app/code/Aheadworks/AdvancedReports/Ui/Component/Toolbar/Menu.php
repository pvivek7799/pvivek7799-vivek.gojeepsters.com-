<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\Component\Toolbar;

use Aheadworks\AdvancedReports\Model\Toolbar\Menu\Item\Modifier as MenuItemModifier;
use Aheadworks\AdvancedReports\Model\Toolbar\MenuPool;
use Aheadworks\AdvancedReports\Ui\Component\OptionsContainer;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Store
 *
 * @package Aheadworks\AdvancedReports\Ui\Component\Toolbar
 */
class Menu extends OptionsContainer
{
    /**
     * @var MenuPool
     */
    private $menuPool;

    /**
     * @var MenuItemModifier
     */
    private $menuItemModifier;

    /**
     * @param ContextInterface $context
     * @param MenuPool $menuPool
     * @param MenuItemModifier $menuItemModifier
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        MenuPool $menuPool,
        MenuItemModifier $menuItemModifier,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->menuPool = $menuPool;
        $this->menuItemModifier = $menuItemModifier;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $this->prepareConfig();

        parent::prepare();
    }

    /**
     * Prepare config
     *
     * @return $this
     * @throws \Exception
     */
    private function prepareConfig()
    {
        $options = $this->getMenuOptions();
        $config = $this->getData('config');
        $config['options'] = $options;
        $config['currentValue'] = $this->getCurrentValue($options);
        $this->setData('config', $config);

        return $this;
    }

    /**
     * Retrieve menu options
     *
     * @return array
     * @throws \Exception
     */
    private function getMenuOptions()
    {
        $options = [];
        foreach ($this->menuPool->getMenuItems() as $menuItem) {
            $options[] = $this->menuItemModifier->modify($menuItem);
        }

        return $options;
    }

    /**
     * Retrieve current value
     *
     * @param array $options
     * @return null|string
     */
    private function getCurrentValue($options)
    {
        foreach ($options as $option) {
            if ($option['isCurrent']) {
                return $option['value'];
            }
        }

        return null;
    }
}
