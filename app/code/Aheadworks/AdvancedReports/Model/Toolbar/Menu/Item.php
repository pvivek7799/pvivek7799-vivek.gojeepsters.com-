<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\Toolbar\Menu;

use Magento\Framework\DataObject;

/**
 * Class Item
 *
 * @package Aheadworks\AdvancedReports\Model\Toolbar\Menu
 */
class Item extends DataObject implements ItemInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->getData(self::PATH);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->getData(self::LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function getResource()
    {
        return $this->getData(self::RESOURCE);
    }

    /**
     * {@inheritdoc}
     */
    public function getController()
    {
        return $this->getData(self::CONTROLLER);
    }

    /**
     * {@inheritdoc}
     */
    public function getLinkAttributes()
    {
        return $this->getData(self::LINK_ATTRIBUTES);
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalClasses()
    {
        return $this->getData(self::ADDITIONAL_CLASSES);
    }
}
