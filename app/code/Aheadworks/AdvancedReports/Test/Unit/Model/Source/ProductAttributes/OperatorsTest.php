<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Test\Unit\Model\Source\ProductAttributes;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\AdvancedReports\Model\Source\ProductAttributes\Operators;

/**
 * Test for \Aheadworks\AdvancedReports\Model\Source\ProductAttributes\Operators
 */
class OperatorsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Operators
     */
    private $model;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(Operators::class);
    }

    /**
     * Testing of toOptionArray method
     */
    public function testToOptionArray()
    {
        $this->assertTrue(is_array($this->model->toOptionArray()));
    }
}
