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

use Modules\Tasks\Models\TaskElementMapper;
use phpOMS\DataStorage\Database\DataMapperAbstract;

/**
 * Mapper class.
 *
 * @package Modules\Support\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
final class TicketElementMapper extends DataMapperAbstract
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    protected static array $columns = [
        'support_ticket_element_id'           => ['name' => 'support_ticket_element_id',   'type' => 'int', 'internal' => 'id'],
        'support_ticket_element_task_element' => ['name' => 'support_ticket_element_task_element', 'type' => 'int', 'internal' => 'taskElement'],
        'support_ticket_element_time'         => ['name' => 'support_ticket_element_time', 'type' => 'int', 'internal' => 'time'],
        'support_ticket_element_ticket'       => ['name' => 'support_ticket_element_ticket', 'type' => 'int', 'internal' => 'ticket'],
    ];

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    protected static array $ownsOne = [
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
    protected static string $table = 'support_ticket_element';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $primaryField = 'support_ticket_element_id';
}
