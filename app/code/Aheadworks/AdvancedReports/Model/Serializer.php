<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\AdvancedReports\Model;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class Serializer
 * @package Aheadworks\AdvancedReports\Model
 * @codingStandardsIgnoreFile
 */
class Serializer
{
    /**
     * Class name of native Magento serializer
     */
    const JSON_SERIALIZER_CLASS_NAME = 'Magento\Framework\Serialize\Serializer\Json';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var object|null
     */
    private $serializerObject = null;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Retrieve serializer object if corresponding class exists
     * @codeCoverageIgnore
     *
     * @return null|object
     */
    private function getSerializerObject()
    {
        if (empty($this->serializerObject)) {
            if (class_exists(self::JSON_SERIALIZER_CLASS_NAME)) {
                $this->serializerObject = $this->objectManager->get(self::JSON_SERIALIZER_CLASS_NAME);
            }
        }
        return $this->serializerObject;
    }

    /**
     * Serialize data into string
     *
     * @param string|int|float|bool|array|null $data
     * @return string|bool
     */
    public function serialize($data)
    {
        $result = null;
        $serializer = $this->getSerializerObject();
        if ($serializer && is_object($serializer)) {
            //@codeCoverageIgnoreStart
            $result = $serializer->serialize($data);
            //@codeCoverageIgnoreEnd
        } else {
            $result = $this->getDefaultSerializedData($data);
        }
        return $result;
    }

    /**
     * Get data, serialized in the default way
     *
     * @param string|int|float|bool|array|null $data
     * @return string|bool
     */
    private function getDefaultSerializedData($data)
    {
        return serialize($data);
    }

    /**
     * Unserialize the given string
     *
     * @param string $string
     * @return string|int|float|bool|array|null
     */
    public function unserialize($string)
    {
        $result = null;
        //@codeCoverageIgnoreStart
        $serializer = $this->getSerializerObject();
        if ($serializer && is_object($serializer)) {
            $result = $serializer->unserialize($string);
        } else {
            //@codeCoverageIgnoreEnd
            $result = $this->getDefaultUnserializedData($string);
        }
        return $result;
    }

    /**
     * Get data, unserialized in the default way
     *
     * @param string $string
     * @return string|int|float|bool|array|null
     */
    private function getDefaultUnserializedData($string)
    {
        return unserialize($string);
    }
}
