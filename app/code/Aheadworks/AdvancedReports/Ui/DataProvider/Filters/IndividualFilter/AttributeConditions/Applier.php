<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Ui\DataProvider\Filters\IndividualFilter\AttributeConditions;

use Aheadworks\AdvancedReports\Ui\DataProvider\Filters\FilterApplierInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class Applier
 *
 * @package Aheadworks\AdvancedReports\Ui\DataProvider\Filters\IndividualFilter\AttributeConditions
 */
class Applier implements FilterApplierInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function apply($collection, $filterPool)
    {
        $conditions = $this->getAttributeConditions();
        if (!empty($conditions) && is_array($conditions)) {
            $collection->addAttributeFilter($conditions);
        }
    }

    /**
     * Retrieve attribute conditions
     *
     * @return array
     */
    private function getAttributeConditions()
    {
        $filters = [];
        if ($conditions = $this->request->getParam('conditions')) {
            foreach ($conditions as $key => $condition) {
                if ($key != 'placeholder') {
                    if (isset($condition['inputValue']) && $condition['inputValue'] != '') {
                        $value = $condition['inputValue'];
                    } elseif (isset($condition['selectValue']) && $condition['selectValue'] != '') {
                        $value = $condition['selectValue'];
                    } elseif (isset($condition['dateValue']) && $condition['dateValue'] != '') {
                        $value = $condition['dateValue'];
                    } else {
                        $value = '';
                    }
                    $filters[] = [
                        'attribute' => $condition['attribute'],
                        'condition' => [$condition['condition'] => $value],
                        'operator'  => $condition['operator']
                    ];
                }
            }
        }

        return $filters;
    }
}
