<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeGroup;
use Oro\Bundle\EntityConfigBundle\Validator\Constraints\AttributeFamilyGroups;
use Oro\Bundle\EntityConfigBundle\Validator\Constraints\AttributeFamilyGroupsValidator;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AttributeFamilyGroupsValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var AttributeFamilyGroupsValidator
     */
    protected $validator;

    /**
     * @var AttributeFamilyGroups
     */
    protected $constraint;

    protected function setUp()
    {
        $this->context = $this->getMockBuilder(ExecutionContextInterface::class)
            ->getMock();
        $this->validator = new AttributeFamilyGroupsValidator();
        $this->validator->initialize($this->context);
        $this->constraint = new AttributeFamilyGroups();
    }

    public function testValidateWrongEntity()
    {
        $entity = new \stdClass();
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate($entity, $this->constraint);
    }

    public function testValidateEmptyGroups()
    {
        $entity = new AttributeFamily();
        $this->context->expects($this->once())
            ->method('addViolation')
            ->with($this->constraint->emptyGroupsMessage);

        $this->validator->validate($entity, $this->constraint);
    }

    public function testValidateManyDefaultGroups()
    {
        $entity = new AttributeFamily();
        $group1 = new AttributeGroup();
        $group1->setIsDefault(true);
        $entity->addAttributeGroup($group1);
        $group2 = new AttributeGroup();
        $group2->setIsDefault(true);
        $entity->addAttributeGroup($group2);

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with($this->constraint->manyDefaultGroupsMessage);

        $this->validator->validate($entity, $this->constraint);
    }

    public function testValidateDefaultGroupIsNotExsist()
    {
        $entity = new AttributeFamily();
        $group = new AttributeGroup();
        $entity->addAttributeGroup($group);

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with($this->constraint->defaultGroupIsNotExistMessage);

        $this->validator->validate($entity, $this->constraint);
    }

    public function testValidateValid()
    {
        $entity = new AttributeFamily();
        $entity->setEntityClass('entityClass');
        $group1 = new AttributeGroup();
        $group1->setIsDefault(true);
        $entity->addAttributeGroup($group1);
        $group2 = new AttributeGroup();
        $entity->addAttributeGroup($group2);

        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate($entity, $this->constraint);
    }
}
