<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Provider;

use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityConfigBundle\Provider\PropertyConfigContainer;
use Oro\Bundle\EntityConfigBundle\Provider\SerializedFieldProvider;

class SerializedFieldProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $extendConfigProvider;
    /**
     * @var SerializedFieldProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $serializedFieldProvider;

    protected function setUp()
    {
        $this->extendConfigProvider = $this->getMockBuilder(ConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializedFieldProvider = new SerializedFieldProvider($this->extendConfigProvider);
    }

    /**
     * @return FieldConfigModel
     */
    private function checkIsSerializedWrongType()
    {
        $fieldConfigModel = new FieldConfigModel('name', 'wrong_type');
        $this->extendConfigProvider->expects($this->never())
            ->method('getPropertyConfig');

        return $fieldConfigModel;
    }

    public function testIsSerializedWrongType()
    {
        $fieldConfigModel = $this->checkIsSerializedWrongType();
        $this->assertFalse($this->serializedFieldProvider->isSerialized($fieldConfigModel));
    }

    public function testIsSerializedByDataWrongType()
    {
        $fieldConfigModel = $this->checkIsSerializedWrongType();
        $this->assertFalse($this->serializedFieldProvider->isSerializedByData($fieldConfigModel, []));
    }

    /**
     * @return FieldConfigModel
     */
    private function expectsEmptyPropertiesValues()
    {
        $fieldConfigModel = new FieldConfigModel('name', 'string');
        $propertyConfigContainer = $this->getMockBuilder(PropertyConfigContainer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $propertyConfigContainer->expects($this->once())
            ->method('getRequiredPropertiesValues')
            ->with(PropertyConfigContainer::TYPE_FIELD)
            ->willReturn([]);
        $this->extendConfigProvider->expects($this->once())
            ->method('getPropertyConfig')
            ->willReturn($propertyConfigContainer);

        return $fieldConfigModel;
    }

    public function testIsSerializedException()
    {
        $fieldConfigModel = $this->expectsEmptyPropertiesValues();
        $this->assertFalse($this->serializedFieldProvider->isSerialized($fieldConfigModel));
    }

    public function testIsSerializedByDataException()
    {
        $fieldConfigModel = $this->expectsEmptyPropertiesValues();
        $this->assertFalse($this->serializedFieldProvider->isSerializedByData($fieldConfigModel, []));
    }

    public function testIsSerializedByModelFalse()
    {
        $fieldConfigModel = new FieldConfigModel('name', 'string');
        $fieldConfigModel->fromArray('attribute', ['sortable' => true]);
        $propertyConfigContainer = $this->getMockBuilder(PropertyConfigContainer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $requiredPropertyValues = [
            'is_serialized' => [
                [
                    'config_id' => ['scope' => 'attribute'],
                    'code' => 'sortable',
                    'value' => false,
                ],
            ],
        ];
        $propertyConfigContainer->expects($this->once())
            ->method('getRequiredPropertiesValues')
            ->with(PropertyConfigContainer::TYPE_FIELD)
            ->willReturn($requiredPropertyValues);
        $this->extendConfigProvider->expects($this->once())
            ->method('getPropertyConfig')
            ->willReturn($propertyConfigContainer);

        $isSerialized = $this->serializedFieldProvider->isSerialized($fieldConfigModel);

        $this->assertFalse($isSerialized);
    }

    public function testIsSerializedByModelTrue()
    {
        $fieldConfigModel = new FieldConfigModel('name', 'string');
        $fieldConfigModel->fromArray('attribute', ['sortable' => false, 'enabled' => true]);
        $propertyConfigContainer = $this->getMockBuilder(PropertyConfigContainer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $requiredPropertyValues = [
            'is_serialized' => [
                [
                    'config_id' => ['scope' => 'attribute'],
                    'code' => 'sortable',
                    'value' => false,
                ],
            ],
        ];
        $propertyConfigContainer->expects($this->once())
            ->method('getRequiredPropertiesValues')
            ->with(PropertyConfigContainer::TYPE_FIELD)
            ->willReturn($requiredPropertyValues);
        $this->extendConfigProvider->expects($this->once())
            ->method('getPropertyConfig')
            ->willReturn($propertyConfigContainer);

        $isSerialized = $this->serializedFieldProvider->isSerialized($fieldConfigModel);

        $this->assertTrue($isSerialized);
    }

    public function testIsSerializedByDataFalse()
    {
        $fieldConfigModel = new FieldConfigModel('name', 'string');
        $data = ['attribute' => ['sortable' => true]];
        $propertyConfigContainer = $this->getMockBuilder(PropertyConfigContainer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $requiredPropertyValues = [
            'is_serialized' => [
                [
                    'config_id' => ['scope' => 'attribute'],
                    'code' => 'sortable',
                    'value' => false,
                ],
            ],
        ];
        $propertyConfigContainer->expects($this->once())
            ->method('getRequiredPropertiesValues')
            ->with(PropertyConfigContainer::TYPE_FIELD)
            ->willReturn($requiredPropertyValues);
        $this->extendConfigProvider->expects($this->once())
            ->method('getPropertyConfig')
            ->willReturn($propertyConfigContainer);

        $isSerialized = $this->serializedFieldProvider->isSerializedByData($fieldConfigModel, $data);

        $this->assertFalse($isSerialized);
    }

    public function testIsSerializedByDataTrue()
    {
        $fieldConfigModel = new FieldConfigModel('name', 'string');
        $data = ['attribute' => ['sortable' => false, 'enabled' => true]];
        $propertyConfigContainer = $this->getMockBuilder(PropertyConfigContainer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $requiredPropertyValues = [
            'is_serialized' => [
                [
                    'config_id' => ['scope' => 'attribute'],
                    'code' => 'sortable',
                    'value' => false,
                ],
            ],
        ];
        $propertyConfigContainer->expects($this->once())
            ->method('getRequiredPropertiesValues')
            ->with(PropertyConfigContainer::TYPE_FIELD)
            ->willReturn($requiredPropertyValues);
        $this->extendConfigProvider->expects($this->once())
            ->method('getPropertyConfig')
            ->willReturn($propertyConfigContainer);

        $isSerialized = $this->serializedFieldProvider->isSerializedByData($fieldConfigModel, $data);
        
        $this->assertTrue($isSerialized);
    }
}
