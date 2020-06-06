<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model\ResourceModel\Indexer\Module;

/**
 * Interface ExpressionInterface
 * @package Aheadworks\AdvancedReports\Model\ResourceModel\Indexer\Module
 */
interface ExpressionInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const MODULE_NAME   = 'module_name';
    const VALUE         = 'value';
    /**#@-*/

    /**
     * Get module name
     *
     * @return string
     */
    public function getModuleName();

    /**
     * Set module name
     *
     * @param string $name
     * @return $this
     */
    public function setModuleName($name);

    /**
     * Get value
     *
     * @return string
     */
    public function getValue();

    /**
     * Set value
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value);
}
