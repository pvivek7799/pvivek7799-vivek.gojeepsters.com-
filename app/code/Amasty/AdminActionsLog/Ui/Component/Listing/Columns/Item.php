<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_AdminActionsLog
 */


namespace Amasty\AdminActionsLog\Ui\Component\Listing\Columns;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Amasty\AdminActionsLog\Helper\Data;

class Item extends Column
{
    /**
     * @var DataObject
     */
    private $dataObject;

    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        DataObject $dataObject,
        Data $helper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->dataObject = $dataObject;
        $this->helper = $helper;
    }

    /**
     * @param array $dataSource
     *
     * @return array $dataSource
     */
    public function prepareDataSource(array $dataSource)
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if (isset($item['item'])) {
                $row = $this->dataObject->unsetData()->addData($item);
                $item['item'] = $this->helper->showOpenElementUrl($row);
            }
        }

        return $dataSource;
    }
}
