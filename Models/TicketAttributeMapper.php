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

use phpOMS\DataStorage\Database\DataMapperAbstract;

/**
 * Ticket mapper class.
 *
 * @package Modules\Support\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
final class TicketAttributeMapper extends DataMapperAbstract
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    protected static array $columns = [
        'support_ticket_attr_id'    => ['name' => 'support_ticket_attr_id',    'type' => 'int', 'internal' => 'id'],
        'support_ticket_attr_ticket'  => ['name' => 'support_ticket_attr_ticket',  'type' => 'int', 'internal' => 'ticket'],
        'support_ticket_attr_type'  => ['name' => 'support_ticket_attr_type',  'type' => 'int', 'internal' => 'type'],
        'support_ticket_attr_value' => ['name' => 'support_ticket_attr_value', 'type' => 'int', 'internal' => 'value'],
    ];

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    protected static array $ownsOne = [
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
    protected static string $table = 'support_ticket_attr';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $primaryField = 'support_ticket_attr_id';
}
