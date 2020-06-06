<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\ResourceModel\Indexer\Module;

use Magento\Framework\DataObject;

/**
 * Class Expression
 * @package Aheadworks\AdvancedReports\Model\ResourceModel\Indexer\Module
 */
class Expression extends DataObject implements ExpressionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getModuleName()
    {
        return $this->getData(self::MODULE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setModuleName($name)
    {
        return $this->setData(self::MODULE_NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }
}
