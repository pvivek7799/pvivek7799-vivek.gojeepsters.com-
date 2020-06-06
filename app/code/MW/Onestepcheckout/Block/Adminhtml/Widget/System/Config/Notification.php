<?php

/**
 * *
 *  Copyright © 2016 MW. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace MW\Onestepcheckout\Block\Adminhtml\Widget\System\Config;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Notification
 * @package MW\Onestepcheckout\Block\Adminhtml\Widget\System\Config
 */
class Notification extends \MW\Onestepcheckout\Block\Adminhtml\Widget\System\Config\ConfigAbstract
{
    /**
     * @var string
     */
    protected $_template = 'MW_Onestepcheckout::system/config/notification.phtml';

    /**
     * @return bool
     */
    public function isHasLibrary()
    {
        if (class_exists('\GeoIp2\Database\Reader')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isHasGeoIpDataFile()
    {
        $directory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        if ($directory->isFile('mw/osc/GeoLite2-City.mmdb')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getGeoIpDataFile()
    {
        $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        $url = $mediaDirectory->getAbsolutePath('mw/osc/GeoLite2-City.mmdb');
        return $url;
    }
}
