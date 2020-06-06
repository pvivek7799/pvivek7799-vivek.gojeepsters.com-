<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Plugin;

use Magento\Catalog\Model\Product\Option\Value;
use MageWorx\OptionBase\Helper\Data as BaseHelper;

/**/
class SaveValuesBugfix
{
    /**
     * @var BaseHelper
     */
    protected $baseHelper;

    /**
     * @param BaseHelper $baseHelper
     */
    public function __construct(
        BaseHelper $baseHelper
    ) {
        $this->baseHelper = $baseHelper;
    }


    /**
     * @param Value $subject
     * @param \Closure $proceed
     * @return Value
     */
    public function aroundSaveValues(Value $subject, \Closure $proceed)
    {
        if ($this->baseHelper->checkModuleVersion('101.0.0', '102.0.6')) {
            foreach ($subject->getValues() as $value) {
                $optionValue = clone $subject;
                $optionValue->isDeleted(false);
                $optionValue->setData(
                    $value
                )->setData(
                    'option_id',
                    $optionValue->getOption()->getId()
                )->setData(
                    'store_id',
                    $optionValue->getOption()->getStoreId()
                );
                if ($optionValue->getData('is_delete') == '1') {
                    if ($optionValue->getId()) {
                        $optionValue->deleteValues($optionValue->getId());
                        $optionValue->delete();
                    }
                } else {
                    $optionValue->save();
                }
            }
            return $subject;
        } else {
            return $proceed();
        }
    }
}
