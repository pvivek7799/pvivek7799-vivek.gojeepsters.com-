<?php
namespace Magecomp\Customprice\Model\Config\Product;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
class Dropdowntype extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
		public function getAllOptions()
		{
			$this->_options = [
				['label' => __('No'), 'value' => 0],
				['label' => __('Yes'), 'value' => 1]
			];
			return $this->_options;
		}
}