<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\Component\Listing\ReportSettings;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Aheadworks\AdvancedReports\Model\Config;

/**
 * Class OrderStatus
 *
 * @package Aheadworks\AdvancedReports\Ui\Component\Listing\ReportSettings
 */
class OrderStatus extends \Magento\Ui\Component\Form\Field
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Config $config
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Config $config,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $config = $this->getData('config');
        $config['default'] = explode(',', $this->config->getOrderStatus());
        $this->setData('config', $config);

        parent::prepare();
    }
}
