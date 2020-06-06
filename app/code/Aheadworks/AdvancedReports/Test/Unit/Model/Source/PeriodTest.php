<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Test\Unit\Model\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\AdvancedReports\Model\Source\Period;
use Magento\Framework\Locale\ListsInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Test for \Aheadworks\AdvancedReports\Model\Source\Period
 */
class PeriodTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Period
     */
    private $model;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var ListsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeListMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();
        $this->localeListMock = $this->getMockBuilder(ListsInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptionWeekdays'])
            ->getMockForAbstractClass();

        $this->model = $objectManager->getObject(
            Period::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'localeList' => $this->localeListMock
            ]
        );
    }

    /**
     * Testing of toOptionArray method
     */
    public function testToOptionArray()
    {
        $localeFirstday = 0;
        $localeWeekend = '0,6';
        $optionWeekdays = [
            [
                'label' => 'Sunday',
                'value' => 0,
            ],
            [
                'label' => 'Monday',
                'value' => 1,
            ],
            [
                'label' => 'Tuesday',
                'value' => 2,
            ],
            [
                'label' => 'Wednesday',
                'value' => 3,
            ],
            [
                'label' => 'Thursday',
                'value' => 4,
            ],
            [
                'label' => 'Friday',
                'value' => 5,
            ],
            [
                'label' => 'Saturday',
                'value' => 6,
            ],
        ];

        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with('general/locale/firstday')
            ->willReturn($localeFirstday);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with('general/locale/weekend')
            ->willReturn($localeWeekend);
        $this->localeListMock->expects($this->exactly(3))
            ->method('getOptionWeekdays')
            ->willReturn($optionWeekdays);

        $this->assertTrue(is_array($this->model->toOptionArray()));
    }
}
