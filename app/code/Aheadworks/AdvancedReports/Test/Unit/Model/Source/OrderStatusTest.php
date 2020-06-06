<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Test\Unit\Model\Source;

use Aheadworks\AdvancedReports\Model\Source\OrderStatus;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Config;

/**
 * Test for \Aheadworks\AdvancedReports\Model\Source\OrderStatus
 */
class OrderStatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OrderStatus
     */
    private $model;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderConfigMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->orderConfigMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getStateStatuses'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            OrderStatus::class,
            ['orderConfig' => $this->orderConfigMock]
        );
    }

    /**
     * Testing of toOptionArray method
     */
    public function testToOptionArray()
    {
        $this->orderConfigMock->expects($this->once())
            ->method('getStateStatuses')
            ->willReturn([['code' => 'code', 'label' => 'label']]);
        $this->assertTrue(is_array($this->model->toOptionArray()));
    }
}
