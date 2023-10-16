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

use Modules\Support\Models\NullTicket;

/**
 * @internal
 */
final class NullTicketTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Support\Models\NullTicket
     * @group module
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Support\Models\Ticket', new NullTicket());
    }

    /**
     * @covers Modules\Support\Models\NullTicket
     * @group module
     */
    public function testId() : void
    {
        $null = new NullTicket(2);
        self::assertEquals(2, $null->id);
    }

    /**
     * @covers Modules\Support\Models\NullTicket
     * @group module
     */
    public function testJsonSerialize() : void
    {
        $null = new NullTicket(2);
        self::assertEquals(['id' => 2], $null);
    }
}
