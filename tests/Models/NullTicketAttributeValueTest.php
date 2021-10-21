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

use Modules\Support\Models\NullTicketAttributeValue;

/**
 * @internal
 */
final class NullTicketAttributeValueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Support\Models\NullTicketAttributeValue
     * @group framework
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Support\Models\TicketAttributeValue', new NullTicketAttributeValue());
    }

    /**
     * @covers Modules\Support\Models\NullTicketAttributeValue
     * @group framework
     */
    public function testId() : void
    {
        $null = new NullTicketAttributeValue(2);
        self::assertEquals(2, $null->getId());
    }
}
