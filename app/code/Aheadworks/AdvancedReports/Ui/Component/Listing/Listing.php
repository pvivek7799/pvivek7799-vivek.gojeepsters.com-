<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\Component\Listing;

use Magento\Ui\Component\Listing\Columns;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class Listing
 *
 * @package Aheadworks\AdvancedReports\Ui\Component\Listing
 */
class Listing extends \Magento\Ui\Component\Listing
{
    /**
     * {@inheritdoc}
     */
    public function getDataSourceData()
    {
        $data = array_merge(
            $this->getContext()->getDataProvider()->getData(),
            ['number_columns' => $this->getNumberColumns()]
        );
        return ['data' => $data];
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        parent::prepare();
        $this->disableDisplayCompareValueInColumns();
    }

    /**
     * Disable display compare value in columns
     *
     * @return void
     * @throws \Exception
     */
    protected function disableDisplayCompareValueInColumns()
    {
        foreach ($this->getColumnsComponent()->getChildComponents() as $column) {
            $config = $column->getData('config');

            $config['displayCompareValue'] = false;

            $column->setData('config', $config);
        }
    }

    /**
     * Returns columns list
     *
     * @return UiComponentInterface[]
     * @throws \Exception
     */
    protected function getNumberColumns()
    {
        $numberColumns = [];
        foreach ($this->getColumnsComponent()->getChildComponents() as $column) {
            if ($column->getData('config/dataType') == 'number') {
                $numberColumns[] = $column->getName();
            }
        }

        return $numberColumns;
    }

    /**
     * Returns Columns component
     *
     * @return UiComponentInterface
     * @throws \Exception
     */
    private function getColumnsComponent()
    {
        foreach ($this->getChildComponents() as $childComponent) {
            if ($childComponent instanceof Columns) {
                return $childComponent;
            }
        }
        throw new \Exception('No columns found');
    }
}
