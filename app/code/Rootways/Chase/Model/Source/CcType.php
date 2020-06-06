<?php
namespace Rootways\Chase\Model\Source;

class CcType extends \Magento\Payment\Model\Source\Cctype
{
    public function getAllowedTypes()
    {
        return array('VI', 'MC', 'AE', 'DI', 'JCB', 'OT');
    }
}
