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
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Support\tests\Models;

use Modules\Support\Models\TicketAttribute;

/**
 * @internal
 */
final class TicketAttributeTest extends \PHPUnit\Framework\TestCase
{
    private TicketAttribute $attribute;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->attribute = new TicketAttribute();
    }

    /**
     * @covers Modules\Support\Models\TicketAttribute
     * @group module
     */
    public function testDefault() : void
    {
        self::assertEquals(0, $this->attribute->getId());
        self::assertInstanceOf('\Modules\Support\Models\TicketAttributeType', $this->attribute->type);
        self::assertInstanceOf('\Modules\Support\Models\TicketAttributeValue', $this->attribute->value);
    }

    /**
     * @covers Modules\Support\Models\TicketAttribute
     * @group module
     */
    public function testSerialize() : void
    {
        $serialized = $this->attribute->jsonSerialize();

        self::assertEquals(
            [
                'id',
                'ticket',
                'type',
                'value',
            ],
            \array_keys($serialized)
        );
    }
}
