<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Test\Unit\Block\Adminhtml\Form\Field;

use Aheadworks\AdvancedReports\Block\Adminhtml\Form\Field\Range;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\AdvancedReports\Block\Adminhtml\Form\Field\Range
 */
class RangeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Range
     */
    private $object;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->object = $objectManager->getObject(Range::class);
    }

    /**
     * Test _prepareToRender method
     */
    public function testPrepareToRender()
    {
        $class = new \ReflectionClass(Range::class);
        $method = $class->getMethod('_prepareToRender');
        $method->setAccessible(true);

        $method->invoke($this->object);

        $this->assertEquals(2, count($this->object->getColumns()));
        $this->assertEquals('range', $this->object->getHtmlId());
    }
}
