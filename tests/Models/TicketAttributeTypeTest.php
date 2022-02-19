<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Support\tests\Models;

use Modules\Support\Models\TicketAttributeType;
use Modules\Support\Models\TicketAttributeTypeL11n;

/**
 * @internal
 */
final class TicketAttributeTypeTest extends \PHPUnit\Framework\TestCase
{
    private TicketAttributeType $type;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->type = new TicketAttributeType();
    }

    /**
     * @covers Modules\Support\Models\TicketAttributeType
     * @group module
     */
    public function testDefault() : void
    {
        self::assertEquals(0, $this->type->getId());
        self::assertEquals('', $this->type->getL11n());
    }

    /**
     * @covers Modules\Support\Models\TicketAttributeType
     * @group module
     */
    public function testL11nInputOutput() : void
    {
        $this->type->setL11n('Test');
        self::assertEquals('Test', $this->type->getL11n());

        $this->type->setL11n(new TicketAttributeTypeL11n(0, 'NewTest'));
        self::assertEquals('NewTest', $this->type->getL11n());
    }

    /**
     * @covers Modules\Support\Models\TicketAttributeType
     * @group module
     */
    public function testSerialize() : void
    {
        $this->type->name                = 'Title';
        $this->type->fields              = 2;
        $this->type->custom              = true;
        $this->type->validationPattern   = '\d*';
        $this->type->isRequired          = true;

        self::assertEquals(
            [
                'id'                => 0,
                'name'              => 'Title',
                'fields'            => 2,
                'custom'            => true,
                'validationPattern' => '\d*',
                'isRequired'        => true,
            ],
            $this->type->jsonSerialize()
        );
    }
}
