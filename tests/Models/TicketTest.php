<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Support\tests\Models;

use Modules\Support\Models\Ticket;
use Modules\Support\Models\TicketElement;

/**
 * @internal
 */
final class TicketTest extends \PHPUnit\Framework\TestCase
{
    private Ticket $ticket;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->ticket = new Ticket();
    }

    /**
     * @covers Modules\Support\Models\Ticket
     * @group module
     */
    public function testDefault() : void
    {
        self::assertEquals(0, $this->ticket->id);
        self::assertNull($this->ticket->for);
        self::assertEquals([], $this->ticket->getTicketElements());
        self::assertInstanceOf('\Modules\Tasks\Models\Task', $this->ticket->task);
        self::assertInstanceOf('\Modules\Support\Models\TicketElement', $this->ticket->getTicketElement(999));
        self::assertInstanceOf('\Modules\Support\Models\SupportApp', $this->ticket->app);
    }

    /**
     * @covers Modules\Support\Models\Ticket
     * @group module
     */
    public function testElementInputOutput() : void
    {
        $element1 = new TicketElement();
        $element2 = new TicketElement();

        self::assertEquals(0, $this->ticket->addElement($element1));
        self::assertEquals(1, $this->ticket->addElement($element2));
        self::assertCount(2, $this->ticket->getTicketElements());
        self::assertEquals($element1, $this->ticket->getTicketElement(0));
        self::assertEquals([$element2, $element1], $this->ticket->invertTicketElements());
    }

    /**
     * @covers Modules\Support\Models\Ticket
     * @group module
     */
    public function testElementRemove() : void
    {
        $element1 = new TicketElement();

        $this->ticket->addElement($element1);
        self::assertCount(1, $this->ticket->getTicketElements());
        self::assertTrue($this->ticket->removeElement(0));
        self::assertCount(0, $this->ticket->getTicketElements());
        self::assertFalse($this->ticket->removeElement(0));
    }

    /**
     * @covers Modules\Support\Models\Ticket
     * @group module
     */
    public function testSerialize() : void
    {
        $serialized = $this->ticket->jsonSerialize();
        unset($serialized['task']);
        unset($serialized['app']);

        self::assertEquals(
            [
                'id'             => 0,
                'for'            => null,
                'ticketElements' => [],
            ],
            $serialized
        );
    }
}
