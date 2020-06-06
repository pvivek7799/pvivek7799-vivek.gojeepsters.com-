<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\Component;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Ui\Component\Container;

/**
 * Class OptionsContainer
 *
 * @package Aheadworks\AdvancedReports\Ui\Component
 */
class OptionsContainer extends Container
{
    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $this->prepareOptions();

        parent::prepare();
    }

    /**
     * Prepare options
     *
     * @return $this
     */
    protected function prepareOptions()
    {
        $config = $this->getData('config');
        if (isset($config['options'])) {
            if ($config['options'] instanceof OptionSourceInterface) {
                $config['options'] = $config['options']->toOptionArray();
            }
        }
        if (!isset($config['options']) || !is_array($config['options'])) {
            $config['options'] = [];
        }
        $this->setData('config', $config);

        return $this;
    }
}
