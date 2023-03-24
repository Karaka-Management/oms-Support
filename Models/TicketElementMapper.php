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

use Modules\Tasks\Models\TaskElementMapper;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Mapper class.
 *
 * @package Modules\Support\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class TicketElementMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'support_ticket_element_id'           => ['name' => 'support_ticket_element_id',   'type' => 'int', 'internal' => 'id'],
        'support_ticket_element_task_element' => ['name' => 'support_ticket_element_task_element', 'type' => 'int', 'internal' => 'taskElement'],
        'support_ticket_element_time'         => ['name' => 'support_ticket_element_time', 'type' => 'int', 'internal' => 'time'],
        'support_ticket_element_ticket'       => ['name' => 'support_ticket_element_ticket', 'type' => 'int', 'internal' => 'ticket'],
    ];

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:class-string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    public const OWNS_ONE = [
        'taskElement' => [
            'mapper'     => TaskElementMapper::class,
            'external'   => 'support_ticket_element_task_element',
        ],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'support_ticket_element';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'support_ticket_element_id';
}
