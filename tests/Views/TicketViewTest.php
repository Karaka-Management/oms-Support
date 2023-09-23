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

namespace Modules\Support\tests\Views;

use Modules\Support\Views\TicketView;

/**
 * @internal
 */
class TicketViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Support\Views\TicketView
     * @group framework
     */
    public function testDefault() : void
    {
        $view = new TicketView();

        self::assertStringContainsString('', $view->getAccountImage(999));
    }

    /**
     * @covers Modules\Support\Views\TicketView
     * @group framework
     */
    public function testAccountImageUrl() : void
    {
        $view = new TicketView();

        self::assertEquals('Web/Backend/img/default-user.jpg', $view->getAccountImage(1));
    }
}
