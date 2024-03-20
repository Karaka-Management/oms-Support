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

use Modules\Support\Models\NullSupportApp;

/**
 * @internal
 */
final class NullSupportAppTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Modules\Support\Models\NullSupportApp
     * @group module
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Support\Models\SupportApp', new NullSupportApp());
    }

    /**
     * @covers \Modules\Support\Models\NullSupportApp
     * @group module
     */
    public function testId() : void
    {
        $null = new NullSupportApp(2);
        self::assertEquals(2, $null->id);
    }

    /**
     * @covers \Modules\Support\Models\NullSupportApp
     * @group module
     */
    public function testJsonSerialize() : void
    {
        $null = new NullSupportApp(2);
        self::assertEquals(['id' => 2], $null->jsonSerialize());
    }
}
