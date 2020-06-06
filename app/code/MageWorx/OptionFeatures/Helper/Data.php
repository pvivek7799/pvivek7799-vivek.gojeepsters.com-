<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\Factory as ImageFactory;
use MageWorx\OptionFeatures\Model\Image as ImageModel;
use MageWorx\OptionFeatures\Model\Product\Option\Value\Media\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;

class Data extends AbstractHelper
{
    // Option value attributes
    const KEY_IS_DEFAULT  = 'is_default';
    const KEY_COST        = 'cost';
    const KEY_WEIGHT      = 'weight';
    const KEY_WEIGHT_TYPE = 'weight_type';
    const KEY_DESCRIPTION = 'description';
    const KEY_IMAGE       = 'images_data';

    // Option attributes
    const KEY_QTY_INPUT          = 'qty_input';
    const KEY_ONE_TIME           = 'one_time';
    const KEY_OPTION_DESCRIPTION = 'description';

    // Product attributes
    const KEY_ABSOLUTE_COST   = 'absolute_cost';
    const KEY_ABSOLUTE_WEIGHT = 'absolute_weight';
    const KEY_ABSOLUTE_PRICE  = 'absolute_price';

    const KEY_OPTION_GALLERY_DISPLAY_MODE = 'mageworx_option_gallery';
    const KEY_OPTION_IMAGE_MODE           = 'mageworx_option_image_mode';

    const OPTION_GALLERY_TYPE_DISABLED      = 0;
    const OPTION_GALLERY_TYPE_BESIDE_OPTION = 1;
    const OPTION_GALLERY_TYPE_ONCE_SELECTED = 2;

    // Value map
    const IS_DEFAULT_TRUE       = '1';
    const IS_DEFAULT_FALSE      = '0';
    const QTY_INPUT_TRUE        = '1';
    const QTY_INPUT_FALSE       = '0';
    const ONE_TIME_TRUE         = '1';
    const ONE_TIME_FALSE        = '0';
    const ABSOLUTE_COST_TRUE    = '1';
    const ABSOLUTE_COST_FALSE   = '0';
    const ABSOLUTE_WEIGHT_TRUE  = '1';
    const ABSOLUTE_WEIGHT_FALSE = '0';
    const ABSOLUTE_PRICE_TRUE   = '1';
    const ABSOLUTE_PRICE_FALSE  = '0';

    // Config
    const XML_PATH_USE_WEIGHT                    = 'mageworx_apo/optionfeatures/use_weight';
    const XML_PATH_USE_COST                      = 'mageworx_apo/optionfeatures/use_cost';
    const XML_PATH_USE_ABSOLUTE_COST             = 'mageworx_apo/optionfeatures/use_absolute_cost';
    const XML_PATH_USE_ABSOLUTE_WEIGHT           = 'mageworx_apo/optionfeatures/use_absolute_weight';
    const XML_PATH_USE_ABSOLUTE_PRICE            = 'mageworx_apo/optionfeatures/use_absolute_price';
    const XML_PATH_USE_ONE_TIME                  = 'mageworx_apo/optionfeatures/use_one_time';
    const XML_PATH_USE_QTY_INPUT                 = 'mageworx_apo/optionfeatures/use_qty_input';
    const XML_PATH_DEFAULT_QTY_LABEL             = 'mageworx_apo/optionfeatures/default_qty_label';
    const XML_PATH_USE_DESCRIPTION               = 'mageworx_apo/optionfeatures/use_description';
    const XML_PATH_USE_OPTION_DESCRIPTION        = 'mageworx_apo/optionfeatures/use_option_description';
    const XML_PATH_USE_IS_DEFAULT                = 'mageworx_apo/optionfeatures/use_is_default';
    const XML_PATH_TOOLTIP_IMAGE                 = 'mageworx_apo/optionfeatures/tooltip_image';
    const XML_PATH_USE_WYSIWYG_FOR_DESCRIPTION   = 'mageworx_apo/optionfeatures/use_wysiwyg_for_description';
    const XML_PATH_USE_ABSOLUTE_PRICE_BY_DEFAULT = 'mageworx_apo/optionfeatures/use_absolute_price_by_default';

    const OPTION_DESCRIPTION_DISABLED = 0;
    const OPTION_DESCRIPTION_TOOLTIP  = 1;
    const OPTION_DESCRIPTION_TEXT     = 2;

    const IMAGE_MEDIA_ATTRIBUTE_BASE_IMAGE    = 'base_image';
    const IMAGE_MEDIA_ATTRIBUTE_TOOLTIP_IMAGE = 'tooltip_image';
    const IMAGE_MEDIA_ATTRIBUTE_SWATCH_IMAGE  = 'swatch_image';

    const XML_BASE_IMAGE_THUMBNAIL_SIZE    = 'mageworx_apo/optionfeatures/base_image_thumbnail_size';
    const XML_TOOLTIP_IMAGE_THUMBNAIL_SIZE = 'mageworx_apo/optionfeatures/tooltip_image_thumbnail_size';

    // Option value image attributes
    protected $imageAttributes = [
        self::IMAGE_MEDIA_ATTRIBUTE_BASE_IMAGE        => 'Base',
        self::IMAGE_MEDIA_ATTRIBUTE_TOOLTIP_IMAGE     => 'Tooltip',
        ImageModel::COLUMN_REPLACE_MAIN_GALLERY_IMAGE => 'Replace Main Gallery Image'
    ];

    /**
     * @var Config
     */
    protected $mediaConfig;

    /**
     * @var ImageFactory
     */
    protected $imageFactory;

    /**
     * Filesystem instance
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Option swatch's height config path
     *
     * @var string
     */
    protected $configPathSwatchHeight;

    /**
     * Option swatch's width config path
     *
     * @var string
     */
    protected $configPathSwatchWidth;

    /**
     * Text swatch's max width config path
     *
     * @var string
     */
    protected $configPathTextSwatchMaxWidth;

    /**
     * Show swatch title config path
     *
     * @var string
     */
    protected $configPathShowSwatchTitle;

    /**
     * Show swatch price config path
     *
     * @var string
     */
    protected $configPathShowSwatchPrice;

    /**
     * @var State
     */
    protected $state;

    /**
     * Additional product attributes for product_attributes table
     *
     * @var array
     */
    protected $additionalProductAttributes;

    /**
     * @param Context $context
     * @param Config $mediaConfig
     * @param ImageFactory $imageFactory
     * @param Filesystem $filesystem
     * @param State $state
     * @param array $additionalProductAttributes
     * @param string $configPathSwatchHeight
     * @param string $configPathSwatchWidth
     * @param string $configPathTextSwatchMaxWidth
     * @param string $configPathShowSwatchTitle
     * @param string $configPathShowSwatchPrice
     */
    public function __construct(
        Context $context,
        Config $mediaConfig,
        ImageFactory $imageFactory,
        Filesystem $filesystem,
        State $state,
        $additionalProductAttributes = [],
        $configPathSwatchHeight = '',
        $configPathSwatchWidth = '',
        $configPathTextSwatchMaxWidth = '',
        $configPathShowSwatchTitle = '',
        $configPathShowSwatchPrice = ''
    ) {
        parent::__construct($context);
        $this->mediaConfig                  = $mediaConfig;
        $this->imageFactory                 = $imageFactory;
        $this->filesystem                   = $filesystem;
        $this->configPathSwatchHeight       = $configPathSwatchHeight;
        $this->configPathSwatchWidth        = $configPathSwatchWidth;
        $this->configPathTextSwatchMaxWidth = $configPathTextSwatchMaxWidth;
        $this->configPathShowSwatchTitle    = $configPathShowSwatchTitle;
        $this->configPathShowSwatchPrice    = $configPathShowSwatchPrice;
        $this->state                        = $state;
        $this->additionalProductAttributes  = $additionalProductAttributes;
    }

    /**
     * Get option description display mode
     *
     * @param int|null $storeId
     * @return bool
     */
    public function getOptionDescriptionMode($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_USE_OPTION_DESCRIPTION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get tooltip image from config
     *
     * @param null $storeId
     * @return string
     */
    public function getTooltipImage($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TOOLTIP_IMAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if 'use description' for options is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isOptionDescriptionEnabled($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_USE_OPTION_DESCRIPTION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get additional product attributes for product_attributes table
     *
     * @return array
     */
    public function getAdditionalProductAttributes()
    {
        return $this->additionalProductAttributes;
    }

    /**
     * Get image attributes
     *
     * @return array
     */
    public function getImageAttributes()
    {
        return $this->imageAttributes;
    }

    /**
     * Check if option value description enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isOptionValueDescriptionEnabled($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_USE_DESCRIPTION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get thumb image url
     *
     * @param string $path
     * @param string $type
     *
     * @return string
     */
    public function getThumbImageUrl($path, $type)
    {
        if (!$path) {
            return '';
        }

        $keepFrame = false;
        switch ($type) {
            case self::IMAGE_MEDIA_ATTRIBUTE_BASE_IMAGE:
                $thumbHeight = $thumbWidth = $this->getBaseImageThumbnailSize();
                break;
            case self::IMAGE_MEDIA_ATTRIBUTE_TOOLTIP_IMAGE:
                $thumbHeight = $thumbWidth = $this->getTooltipImageThumbnailSize();
                $keepFrame = false;
                break;
            case self::IMAGE_MEDIA_ATTRIBUTE_SWATCH_IMAGE:
                $thumbHeight = $this->getSwatchHeight();
                $thumbWidth  = $this->getSwatchWidth();
                break;
            default:
                $thumbHeight = $thumbWidth = 0;
                break;
        }

        if ($thumbHeight <= 0 || $thumbWidth <= 0) {
            return $this->mediaConfig->getMediaUrl($path);
        }

        $filePath      = $this->mediaConfig->getMediaPath($path);
        $pathArray     = explode('/', $filePath);
        $fileName      = array_pop($pathArray);
        $directoryPath = implode('/', $pathArray);
        $thumbPath     = $directoryPath . '/' . $thumbHeight . 'x' . $thumbWidth . '/';

        $mediaDirectory    = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $thumbAbsolutePath = $mediaDirectory->getAbsolutePath($thumbPath);
        $fileAbsolutePath  = $mediaDirectory->getAbsolutePath($filePath);

        $thumbFilePath = $thumbAbsolutePath . $fileName;
        if (!file_exists($thumbFilePath)) {
            $this->createThumbFile(
                $fileAbsolutePath,
                $thumbAbsolutePath,
                $fileName,
                $thumbHeight,
                $thumbWidth,
                $keepFrame
            );
        }

        return $this->mediaConfig->getUrl($thumbPath . $fileName);
    }

    /**
     * Get swatch base image thumbnail size
     *
     * @return int
     */
    public function getBaseImageThumbnailSize()
    {
        return intval($this->scopeConfig->getValue(self::XML_BASE_IMAGE_THUMBNAIL_SIZE));
    }

    /**
     * Get swatch tooltip image thumbnail size
     *
     * @return int
     */
    public function getTooltipImageThumbnailSize()
    {
        return intval($this->scopeConfig->getValue(self::XML_TOOLTIP_IMAGE_THUMBNAIL_SIZE));
    }

    /**
     * Get swatch height
     *
     * @return int
     */
    public function getSwatchHeight()
    {
        return intval($this->scopeConfig->getValue($this->configPathSwatchHeight));
    }

    /**
     * Get swatch width
     *
     * @return int
     */
    public function getSwatchWidth()
    {
        return intval($this->scopeConfig->getValue($this->configPathSwatchWidth));
    }

    /**
     * Get text swatch max width
     *
     * @return int
     */
    public function getTextSwatchMaxWidth()
    {
        return intval($this->scopeConfig->getValue($this->configPathTextSwatchMaxWidth));
    }

    /**
     * Is show swatch title under swatch
     *
     * @return bool
     */
    public function isShowSwatchTitle()
    {
        return boolval($this->scopeConfig->getValue($this->configPathShowSwatchTitle));
    }

    /**
     * Is show swatch price under swatch
     *
     * @return bool
     */
    public function isShowSwatchPrice()
    {
        return boolval($this->scopeConfig->getValue($this->configPathShowSwatchPrice));
    }

    /**
     * Create thumb image based on thumbnail size
     *
     * @param string $origFilePath
     * @param string $thumbPath
     * @param string $newFileName
     * @param int $thumbHeight
     * @param int $thumbWidth
     * @param bool $keepFrame
     *
     * @return void
     */
    public function createThumbFile($origFilePath, $thumbPath, $newFileName, $thumbHeight, $thumbWidth, $keepFrame)
    {
        try {
            $image      = $this->imageFactory->create($origFilePath);
            $origHeight = $image->getOriginalHeight();
            $origWidth  = $image->getOriginalWidth();
            $ratio      = $origWidth / $origHeight;

            if ($keepFrame) {
                $image->keepFrame(true);
            }

            $image->keepAspectRatio(true);
            $image->keepTransparency(true);
            $image->constrainOnly(false);
            $image->backgroundColor([255, 255, 255]);
            $image->quality(100);

            $width  = null;
            $height = null;

            if ($origHeight > $origWidth) {
                $height = $thumbHeight;
                if (!$keepFrame) {
                    $width = $ratio * $height;
                }
            } else {
                $width = $thumbWidth;
                if (!$keepFrame) {
                    $height = $ratio * $width;
                }
            }

            $image->resize($width, $height);

            $image->constrainOnly(true);
            $image->keepAspectRatio(true);
            if ($keepFrame) {
                $image->keepFrame(false);
            }
            $image->save($thumbPath, $newFileName);
        } catch (\Exception $e) {
            $this->_logger->error($e);
        }
    }

    /**
     * Get image url for specified type, width or height
     *
     * @param $path
     * @param int $height
     * @param int $width
     * @return string
     */
    public function getImageUrl($path, $height = 300, $width = 300)
    {
        if (!$path) {
            return '';
        }

        $filePath      = $this->mediaConfig->getMediaPath($path);
        $pathArray     = explode('/', $filePath);
        $fileName      = array_pop($pathArray);
        $directoryPath = implode('/', $pathArray);
        $imagePath     = $directoryPath . '/' . $width . 'x' . $height . '/';

        $mediaDirectory   = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $imgAbsolutePath  = $mediaDirectory->getAbsolutePath($imagePath);
        $fileAbsolutePath = $mediaDirectory->getAbsolutePath($filePath);

        $imgFilePath = $imgAbsolutePath . $fileName;
        if (!file_exists($imgFilePath)) {
            $this->createImageFile($fileAbsolutePath, $imgAbsolutePath, $fileName, $width, $height);
        }

        return $this->mediaConfig->getUrl($imagePath . $fileName);
    }

    /**
     * Create image based on size
     *
     * @param string $origFilePath
     * @param string $imagePath
     * @param string $newFileName
     * @param $width
     * @param $height
     *
     */
    public function createImageFile($origFilePath, $imagePath, $newFileName, $width, $height)
    {
        try {
            $image = $this->imageFactory->create($origFilePath);
            $image->keepAspectRatio(true);
            $image->keepFrame(true);
            $image->keepTransparency(true);
            $image->constrainOnly(false);
            $image->backgroundColor([255, 255, 255]);
            $image->quality(100);
            $image->resize($width, $height);
            $image->constrainOnly(true);
            $image->keepAspectRatio(true);
            $image->keepFrame(false);
            $image->save($imagePath, $newFileName);
        } catch (\Exception $e) {
            $this->_logger->error($e);
        }
    }

    /**
     * Sort options in array using theirs sort order
     * returns a new array with sorted options
     *
     * @param \Magento\Catalog\Model\Product\Option[] $options
     * @return array|\Magento\Catalog\Model\Product\Option[]
     */
    public function sortOptions(array $options)
    {
        if (count($options) == 1) {
            return $options;
        }

        $sortedOptions = [];
        foreach ($options as $index => $option) {
            $sortOrder                 = $option->getSortOrder() * 100;
            $sortedOptions[$sortOrder] = $option;
        }

        return $sortedOptions;
    }

    /**
     * Check if 'use weight' are enable
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isWeightEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_WEIGHT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if 'use absolute weight' are enable
     * Depends on the 'use weight' flag
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isAbsoluteWeightEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
                self::XML_PATH_USE_ABSOLUTE_WEIGHT,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ) && $this->isWeightEnabled($storeId);
    }

    /**
     * Check if 'use cost' are enable
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isCostEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_COST,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if 'use absolute cost' are enable
     * Depends on the 'use cost' flag
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isAbsoluteCostEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
                self::XML_PATH_USE_ABSOLUTE_COST,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ) && $this->isCostEnabled($storeId);
    }

    /**
     * Check if 'use absolute price' are enable
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isAbsolutePriceEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_ABSOLUTE_PRICE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if 'one time' are enable
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isOneTimeEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_ONE_TIME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if 'qty input' is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isQtyInputEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_QTY_INPUT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get default option qty field label
     *
     * @param int|null $storeId
     * @return string
     */
    public function getDefaultQtyLabel($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_QTY_LABEL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if 'use value description' is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isValueDescriptionEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_DESCRIPTION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if 'use is default' is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isDefaultEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_IS_DEFAULT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if wysiwyg editor enabled for descriptions
     *
     * @return bool
     */
    public function isEnabledWysiwygForDescription()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_WYSIWYG_FOR_DESCRIPTION
        );
    }

    /**
     * Check if absolute price is enabled by default
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isAbsolutePriceEnabledByDefault($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_ABSOLUTE_PRICE_BY_DEFAULT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
