<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Support\tests\Models;

use Modules\Support\Models\TicketElement;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Modules\Support\Models\TicketElement::class)]
final class TicketElementTest extends \PHPUnit\Framework\TestCase
{
    private TicketElement $element;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->element = new TicketElement();
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testDefault() : void
    {
        self::assertEquals(0, $this->element->id);
        self::assertEquals(0, $this->element->ticket);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testSerialize() : void
    {
        $this->element->ticket = 2;

        $serialized = $this->element->jsonSerialize();
        unset($serialized['taskElement']);

        self::assertEquals(
            [
                'id'     => 0,
                'ticket' => 2,
            ],
            $serialized
        );
    }
}
