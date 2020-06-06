<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Test\Unit\Model\Filter;

use Aheadworks\AdvancedReports\Model\Filter\GroupBy;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Aheadworks\AdvancedReports\Model\Source\Groupby as GroupbySource;

/**
 * Test for \Aheadworks\AdvancedReports\Model\Filter\GroupBy
 */
class GroupbyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GroupBy
     */
    private $model;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var SessionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->sessionMock = $this->getMockForAbstractClass(
            SessionManagerInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['setData', 'getData']
        );
        $this->model = $objectManager->getObject(
            GroupBy::class,
            [
                'request' => $this->requestMock,
                'session' => $this->sessionMock
            ]
        );
    }

    /**
     * Testing of getCurrentGroupByKey method from request
     */
    public function testGetCurrentGroupByKeyFromRequest()
    {
        $value = GroupbySource::TYPE_DAY;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('group_by')
            ->willReturn($value);
        $this->sessionMock->expects($this->once())
            ->method('setData')
            ->with(GroupBy::SESSION_KEY, $value)
            ->willReturnSelf();

        $this->assertSame($value, $this->model->getValue());
    }

    /**
     * Testing of getCurrentGroupByKey method from session
     */
    public function testGetCurrentGroupByKeyFromSession()
    {
        $value = GroupbySource::TYPE_DAY;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('group_by')
            ->willReturn(null);
        $this->sessionMock->expects($this->once())
            ->method('getData')
            ->with(GroupBy::SESSION_KEY)
            ->willReturn($value);
        $this->sessionMock->expects($this->once())
            ->method('setData')
            ->with(GroupBy::SESSION_KEY, $value)
            ->willReturnSelf();

        $this->assertSame($value, $this->model->getValue());
    }

    /**
     * Testing of getCurrentGroupByKey method from default
     */
    public function testGetCurrentGroupByKeyFromDefault()
    {
        $value = GroupbySource::TYPE_MONTH;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('group_by')
            ->willReturn(null);
        $this->sessionMock->expects($this->once())
            ->method('getData')
            ->with(GroupBy::SESSION_KEY)
            ->willReturn(null);
        $this->sessionMock->expects($this->once())
            ->method('setData')
            ->with(GroupBy::SESSION_KEY, $value)
            ->willReturnSelf();

        $this->assertSame($value, $this->model->getValue());
    }

    /**
     * Testing of getDefaultValue method
     */
    public function testGetDefaultValue()
    {
        $value = GroupbySource::TYPE_MONTH;

        $this->assertSame($value, $this->model->getDefaultValue());
    }
}
