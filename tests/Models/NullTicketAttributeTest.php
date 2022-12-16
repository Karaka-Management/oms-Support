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

use Modules\Support\Models\NullTicketAttribute;

/**
 * @internal
 */
final class NullTicketAttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Support\Models\NullTicketAttribute
     * @group framework
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Support\Models\TicketAttribute', new NullTicketAttribute());
    }

    /**
     * @covers Modules\Support\Models\NullTicketAttribute
     * @group framework
     */
    public function testId() : void
    {
        $null = new NullTicketAttribute(2);
        self::assertEquals(2, $null->getId());
    }
}
