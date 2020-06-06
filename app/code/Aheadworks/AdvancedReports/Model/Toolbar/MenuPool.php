<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Toolbar;

use Aheadworks\AdvancedReports\Model\Toolbar\Menu\ItemInterface;
use Aheadworks\AdvancedReports\Model\Toolbar\Menu\ItemFactory;
use Magento\Framework\AuthorizationInterface;

/**
 * Class MenuPool
 *
 * @package Aheadworks\AdvancedReports\Model\Toolbar
 */
class MenuPool
{
    /**
     * @var array
     */
    private $menuItems = [];

    /**
     * @var array
     */
    private $menuItemsInstances = [];

    /**
     * @var ItemFactory
     */
    private $menuItemFactory;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @param ItemFactory $menuItemFactory
     * @param AuthorizationInterface $authorization
     * @param array $menuItems
     */
    public function __construct(
        ItemFactory $menuItemFactory,
        AuthorizationInterface $authorization,
        array $menuItems
    ) {
        $this->menuItemFactory = $menuItemFactory;
        $this->authorization = $authorization;
        $this->menuItems = $menuItems;
    }

    /**
     * Retrieve menu items
     *
     * @return ItemInterface[]
     * @throws \Exception
     */
    public function getMenuItems()
    {
        $menuItemsInstances = [];
        foreach ($this->menuItems as $menuItemKey => $menuItemData) {
            $menuItem = $this->getMenuItem($menuItemKey);
            if ($menuItem->getResource() && !$this->authorization->isAllowed($menuItem->getResource())) {
                continue;
            }

            $menuItemsInstances[] = $menuItem;
        }

        return $menuItemsInstances;
    }

    /**
     * Get menuItems instance
     *
     * @param string $menuItem
     * @return ItemInterface
     * @throws \Exception
     */
    public function getMenuItem($menuItem)
    {
        if (!isset($this->menuItemsInstances[$menuItem])) {
            if (!isset($this->menuItems[$menuItem])) {
                throw new \Exception(sprintf('Unknown menu item: %s requested', $menuItem));
            }
            $menuItemInstance = $this->menuItemFactory->create(['data' => $this->menuItems[$menuItem]]);
            if (!$menuItemInstance instanceof ItemInterface) {
                throw new \Exception(
                    sprintf('Configuration instance %s does not implement required interface.', $menuItem)
                );
            }
            $this->menuItemsInstances[$menuItem] = $menuItemInstance;
        }
        return $this->menuItemsInstances[$menuItem];
    }
}
