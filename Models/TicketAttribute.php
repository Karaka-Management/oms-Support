<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Support\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Support\Models;

use phpOMS\Contract\ArrayableInterface;

/**
 * Ticket class.
 *
 * @package Modules\Support\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
class TicketAttribute implements \JsonSerializable, ArrayableInterface
{
    /**
     * Id.
     *
     * @var int
     * @since 1.0.0
     */
    protected int $id = 0;

    /**
     * Ticket this attribute belongs to
     *
     * @var int
     * @since 1.0.0
     */
    public int $ticket = 0;

    /**
     * Attribute type the attribute belongs to
     *
     * @var TicketAttributeType
     * @since 1.0.0
     */
    public TicketAttributeType $type;

    /**
     * Attribute value the attribute belongs to
     *
     * @var TicketAttributeValue
     * @since 1.0.0
     */
    public TicketAttributeValue $value;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->type  = new TicketAttributeType();
        $this->value = new TicketAttributeValue();
    }

    /**
     * Get id
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'      => $this->id,
            'ticket'  => $this->ticket,
            'type'    => $this->type,
            'value'   => $this->value,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
