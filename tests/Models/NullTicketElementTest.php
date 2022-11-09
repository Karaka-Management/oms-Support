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

use Modules\Support\Models\NullTicketElement;

/**
 * @internal
 */
final class NullTicketElementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Support\Models\NullTicketElement
     * @group framework
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Support\Models\TicketElement', new NullTicketElement());
    }

    /**
     * @covers Modules\Support\Models\NullTicketElement
     * @group framework
     */
    public function testId() : void
    {
        $null = new NullTicketElement(2);
        self::assertEquals(2, $null->getId());
    }
}
