<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\Component\Listing\Chart\Location;

use Aheadworks\AdvancedReports\Model\Source\Country as CountrySource;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Aheadworks\AdvancedReports\Ui\DataProvider\Compare\Merger\Manager as CompareMergerManager;

/**
 * Class Chart
 *
 * @package Aheadworks\AdvancedReports\Ui\Component\Listing\Chart\Location
 */
class Chart extends \Aheadworks\AdvancedReports\Ui\Component\Listing\Chart\Chart
{
    /**
     * @var CountrySource
     */
    private $countrySource;

    /**
     * @param ContextInterface $context
     * @param CompareMergerManager $mergerManager
     * @param CountrySource $countrySource
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        CompareMergerManager $mergerManager,
        CountrySource $countrySource,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $mergerManager, $components, $data);
        $this->countrySource = $countrySource;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        $dataSource = parent::prepareDataSource($dataSource);
        $rows = $dataSource['data']['chart']['rows'];
        foreach ($rows as &$row) {
            $row['country'] = $this->countrySource->getOptionByValue($row['country_id']);
        }
        $dataSource['data']['chart']['rows'] = $rows;
        $dataSource['data']['chart']['options'] = [
            'region' => 'world',
            'resolution' => 'countries'
        ];

        return $dataSource;
    }
}
