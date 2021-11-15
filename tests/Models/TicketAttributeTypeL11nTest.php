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

use Modules\Support\Models\TicketAttributeTypeL11n;
use phpOMS\Localization\ISO639x1Enum;

/**
 * @internal
 */
final class TicketAttributeTypeL11nTest extends \PHPUnit\Framework\TestCase
{
    private TicketAttributeTypeL11n $l11n;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->l11n = new TicketAttributeTypeL11n();
    }

    /**
     * @covers Modules\Support\Models\TicketAttributeTypeL11n
     * @group module
     */
    public function testDefault() : void
    {
        self::assertEquals(0, $this->l11n->getId());
        self::assertEquals('', $this->l11n->title);
        self::assertEquals(0, $this->l11n->type);
        self::assertEquals(ISO639x1Enum::_EN, $this->l11n->getLanguage());
    }

    /**
     * @covers Modules\Support\Models\TicketAttributeTypeL11n
     * @group module
     */
    public function testNameInputOutput() : void
    {
        $this->l11n->title = 'TestName';
        self::assertEquals('TestName', $this->l11n->title);
    }

    /**
     * @covers Modules\Support\Models\TicketAttributeTypeL11n
     * @group module
     */
    public function testLanguageInputOutput() : void
    {
        $this->l11n->setLanguage(ISO639x1Enum::_DE);
        self::assertEquals(ISO639x1Enum::_DE, $this->l11n->getLanguage());
    }

    /**
     * @covers Modules\Support\Models\TicketAttributeTypeL11n
     * @group module
     */
    public function testSerialize() : void
    {
        $this->l11n->title  = 'Title';
        $this->l11n->type   = 2;
        $this->l11n->setLanguage(ISO639x1Enum::_DE);

        self::assertEquals(
            [
                'id'        => 0,
                'title'     => 'Title',
                'type'      => 2,
                'language'  => ISO639x1Enum::_DE,
            ],
            $this->l11n->jsonSerialize()
        );
    }
}
