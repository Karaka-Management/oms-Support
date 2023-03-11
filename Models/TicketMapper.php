<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Support\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Support\Models;

use Modules\Admin\Models\AccountMapper;
use Modules\Tasks\Models\TaskMapper;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Mapper class.
 *
 * @package Modules\Support\Models
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class TicketMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'support_ticket_id'   => ['name' => 'support_ticket_id',   'type' => 'int', 'internal' => 'id'],
        'support_ticket_task' => ['name' => 'support_ticket_task', 'type' => 'int', 'internal' => 'task'],
        'support_ticket_for'  => ['name' => 'support_ticket_for', 'type' => 'int', 'internal' => 'for'],
        'support_ticket_app'  => ['name' => 'support_ticket_app', 'type' => 'int', 'internal' => 'app'],
    ];

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:class-string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    public const OWNS_ONE = [
        'task' => [
            'mapper'     => TaskMapper::class,
            'external'   => 'support_ticket_task',
        ],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'ticketElements' => [
            'mapper'       => TicketElementMapper::class,
            'table'        => 'support_ticket_element',
            'self'         => 'support_ticket_element_ticket',
            'external'     => null,
        ],
        'attributes' => [
            'mapper'      => TicketAttributeMapper::class,
            'table'       => 'support_ticket_attr',
            'self'        => 'support_ticket_attr_ticket',
            'conditional' => true,
            'external'    => null,
        ],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, external:string, column?:string, by?:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
        'app' => [
            'mapper'   => SupportAppMapper::class,
            'external' => 'support_ticket_app',
        ],
        'for' => [
            'mapper'   => AccountMapper::class,
            'external' => 'support_ticket_for',
        ],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'support_ticket';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'support_ticket_id';
}
