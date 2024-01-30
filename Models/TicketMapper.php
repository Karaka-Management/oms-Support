<?php
/**
 * Jingga
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

use Modules\Admin\Models\AccountMapper;
use Modules\Tasks\Models\TaskMapper;
use Modules\Tasks\Models\TaskStatus;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;
use phpOMS\Stdlib\Base\SmartDateTime;

/**
 * Mapper class.
 *
 * @package Modules\Support\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of Ticket
 * @extends DataMapperFactory<T>
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
            'mapper'   => TaskMapper::class,
            'external' => 'support_ticket_task',
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
            'mapper'   => TicketElementMapper::class,
            'table'    => 'support_ticket_element',
            'self'     => 'support_ticket_element_ticket',
            'external' => null,
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
        ]
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

    public static function getStatOverview(int $account) : array
    {
        $start = SmartDateTime::startOfMonth();

        return [
            'total'      => self::count()->with('task')->where('task/createdAt', $start, '>=')->execute(),
            'unassigned' => self::count()->with('task')->where('for', null)->execute(),
            'open'       => self::count()->with('task')->where('task/status', TaskStatus::OPEN)->execute(),
            'closed'     => self::count()->with('task')->where('task/createdAt', $start, '>=')->where('task/status', TaskStatus::DONE)->where('task/status', TaskStatus::CANCELED, '=', 'OR')->where('task/status', TaskStatus::SUSPENDED, '=', 'OR')->execute(),
            'inprogress' => self::count()->with('task')->where('task/status', TaskStatus::WORKING)->execute(),
        ];
    }
}
