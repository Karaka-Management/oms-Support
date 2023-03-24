<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Support\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Support\Models;

use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Ticket mapper class.
 *
 * @package Modules\Support\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class TicketAttributeMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'support_ticket_attr_id'      => ['name' => 'support_ticket_attr_id',    'type' => 'int', 'internal' => 'id'],
        'support_ticket_attr_ticket'  => ['name' => 'support_ticket_attr_ticket',  'type' => 'int', 'internal' => 'ticket'],
        'support_ticket_attr_type'    => ['name' => 'support_ticket_attr_type',  'type' => 'int', 'internal' => 'type'],
        'support_ticket_attr_value'   => ['name' => 'support_ticket_attr_value', 'type' => 'int', 'internal' => 'value'],
    ];

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:class-string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    public const OWNS_ONE = [
        'type' => [
            'mapper'            => TicketAttributeTypeMapper::class,
            'external'          => 'support_ticket_attr_type',
        ],
        'value' => [
            'mapper'            => TicketAttributeValueMapper::class,
            'external'          => 'support_ticket_attr_value',
        ],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'support_ticket_attr';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'support_ticket_attr_id';
}
