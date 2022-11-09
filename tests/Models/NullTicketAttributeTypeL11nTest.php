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

use Modules\Support\Models\NullTicketAttributeTypeL11n;

/**
 * @internal
 */
final class NullTicketAttributeTypeL11nTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Support\Models\NullTicketAttributeTypeL11n
     * @group framework
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Support\Models\TicketAttributeTypeL11n', new NullTicketAttributeTypeL11n());
    }

    /**
     * @covers Modules\Support\Models\NullTicketAttributeTypeL11n
     * @group framework
     */
    public function testId() : void
    {
        $null = new NullTicketAttributeTypeL11n(2);
        self::assertEquals(2, $null->getId());
    }
}
