<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Support\tests\Models;

use Modules\Support\Models\AttributeValueType;
use Modules\Support\Models\TicketAttributeValue;

/**
 * @internal
 */
final class TicketAttributeValueTest extends \PHPUnit\Framework\TestCase
{
    private TicketAttributeValue $value;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->value = new TicketAttributeValue();
    }

    /**
     * @covers Modules\Support\Models\TicketAttributeValue
     * @group module
     */
    public function testDefault() : void
    {
        self::assertEquals(0, $this->value->getId());
        self::assertNull($this->value->getValue());
        self::assertFalse($this->value->isDefault);
    }

    /**
     * @covers Modules\Support\Models\TicketAttributeValue
     * @group module
     */
    public function testValueIntInputOutput() : void
    {
        $this->value->setValue(1, AttributeValueType::_INT);
        self::assertEquals(1, $this->value->getValue());
    }

    /**
     * @covers Modules\Support\Models\TicketAttributeValue
     * @group module
     */
    public function testValueFloatInputOutput() : void
    {
        $this->value->setValue(1.1, AttributeValueType::_FLOAT);
        self::assertEquals(1.1, $this->value->getValue());
    }

    /**
     * @covers Modules\Support\Models\TicketAttributeValue
     * @group module
     */
    public function testValueStringInputOutput() : void
    {
        $this->value->setValue('test', AttributeValueType::_STRING);
        self::assertEquals('test', $this->value->getValue());
    }

    /**
     * @covers Modules\Support\Models\TicketAttributeValue
     * @group module
     */
    public function testValueDateInputOutput() : void
    {
        $dat = new \DateTime('now');
        $this->value->setValue('now', AttributeValueType::_DATETIME);
        self::assertEquals($dat->format('Y-m-d'), $this->value->getValue()->format('Y-m-d'));
    }

    /**
     * @covers Modules\Support\Models\TicketAttributeValue
     * @group module
     */
    public function testSerialize() : void
    {
        $this->value->setValue('test', AttributeValueType::_STRING);
        $this->value->isDefault = true;

        self::assertEquals(
            [
                'id'           => 0,
                'valueInt'     => null,
                'valueStr'     => 'test',
                'valueDec'     => null,
                'valueDat'     => null,
                'isDefault'    => true,
            ],
            $this->value->jsonSerialize()
        );
    }
}
