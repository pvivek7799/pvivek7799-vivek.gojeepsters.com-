<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Model\Attribute\Option;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionBase\Api\AttributeInterface;
use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class ImageMode extends AbstractAttribute implements AttributeInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param ResourceConnection $resource
     * @param Helper $helper
     */
    public function __construct(
        ResourceConnection $resource,
        Helper $helper
    ) {
        $this->helper = $helper;
        parent::__construct($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Helper::KEY_OPTION_IMAGE_MODE;
    }

    /**
     * {@inheritdoc}
     */
    public function importTemplateMageOne($data)
    {
        if (isset($data['image_mode']) && $this->isM1ModeRelatedToReplace($data['image_mode'])) {
            return 1;
        }
        return 0;
    }

    /**
     * Check if MageOne image mode related to replace mode in MageTwo:
     * Replace Product Gallery, Append to Product Gallery, Overlay
     *
     * @param string
     * @return bool
     */
    public function isM1ModeRelatedToReplace($mode)
    {
        return in_array($mode, ['2','3','4']);
    }
}
