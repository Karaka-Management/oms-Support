<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Support\tests\Models;

use Modules\Support\Models\TicketElement;

/**
 * @internal
 */
final class TicketElementTest extends \PHPUnit\Framework\TestCase
{
    private TicketElement $element;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->element = new TicketElement();
    }

    /**
     * @covers Modules\Support\Models\TicketElement
     * @group module
     */
    public function testDefault() : void
    {
        self::assertEquals(0, $this->element->getId());
        self::assertEquals(0, $this->element->time);
        self::assertEquals(0, $this->element->ticket);
    }

    /**
     * @covers Modules\Support\Models\TicketElement
     * @group module
     */
    public function testSerialize() : void
    {
        $this->element->time   = 10;
        $this->element->ticket = 2;

        $serialized = $this->element->jsonSerialize();
        unset($serialized['taskElement']);

        self::assertEquals(
            [
                'id'      => 0,
                'time'    => 10,
                'ticket'  => 2,
            ],
            $serialized
        );
    }
}
