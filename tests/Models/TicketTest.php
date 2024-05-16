<?php
/**
 * Jingga
 *
 * PHP Version 8.2
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

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Modules\Support\Models\Ticket::class)]
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

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testDefault() : void
    {
        self::assertEquals(0, $this->ticket->id);
        self::assertNull($this->ticket->task->for);
        self::assertInstanceOf('\Modules\Tasks\Models\Task', $this->ticket->task);
        self::assertInstanceOf('\Modules\Support\Models\SupportApp', $this->ticket->app);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testSerialize() : void
    {
        $serialized = $this->ticket->jsonSerialize();
        unset($serialized['task']);
        unset($serialized['app']);

        self::assertEquals(
            [
                'id' => 0,
            ],
            $serialized
        );
    }
}
