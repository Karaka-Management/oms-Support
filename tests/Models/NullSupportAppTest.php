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

use Modules\Support\Models\NullSupportApp;

/**
 * @internal
 */
final class NullSupportAppTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Support\Models\NullSupportApp
     * @group framework
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Support\Models\SupportApp', new NullSupportApp());
    }

    /**
     * @covers Modules\Support\Models\NullSupportApp
     * @group framework
     */
    public function testId() : void
    {
        $null = new NullSupportApp(2);
        self::assertEquals(2, $null->getId());
    }
}
