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

use Modules\Support\Models\SupportApp;

/**
 * @internal
 */
final class SupportAppTest extends \PHPUnit\Framework\TestCase
{
    private SupportApp $app;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->app = new SupportApp();
    }

    /**
     * @covers Modules\Support\Models\SupportApp
     * @group module
     */
    public function testDefault() : void
    {
        self::assertEquals(0, $this->app->id);
        self::assertEquals('', $this->app->name);
    }

    /**
     * @covers Modules\Support\Models\SupportApp
     * @group module
     */
    public function testSerialize() : void
    {
        $this->app->name = 'Test Title';

        $serialized = $this->app->jsonSerialize();

        self::assertEquals(
            [
                'id'          => 0,
                'name'        => 'Test Title',
            ],
            $serialized
        );
    }
}
