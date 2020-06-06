<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\Component;

use Magento\Ui\Component\AbstractComponent;

/**
 * Class Dashboard
 *
 * @package Aheadworks\AdvancedReports\Ui\Component
 */
class Dashboard extends AbstractComponent
{
    /**
     * @var string
     */
    const NAME = 'dashboard';

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataSourceData()
    {
        return ['data' => $this->getContext()->getDataProvider()->getData()];
    }
}
