<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Support\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Support\Models;

use Modules\Tasks\Models\AccountRelationMapper;
use Modules\Tasks\Models\TaskElementMapper;
use Modules\Tasks\Models\TaskMapper;
use Modules\Tasks\Models\TaskStatus;
use Modules\Tasks\Models\TaskType;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;
use phpOMS\DataStorage\Database\Mapper\ReadMapper;
use phpOMS\DataStorage\Database\Query\Builder;
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

    /**
     * Get general ticket stats
     *
     * @return array{total:int, unassigned:int, open:int, closed:int, inprogress:int}
     *
     * @since 1.0.0
     */
    public static function getStatOverview() : array
    {
        $start = SmartDateTime::startOfMonth();

        return [
            'total'      => self::count()->with('task')->where('task/createdAt', $start, '>=')->executeCount(),
            'unassigned' => self::count()->with('task')->where('for', null)->executeCount(),
            'open'       => self::count()->with('task')->where('task/status', TaskStatus::OPEN)->executeCount(),
            'closed'     => self::count()->with('task')->where('task/createdAt', $start, '>=')->where('task/status', TaskStatus::DONE)->where('task/status', TaskStatus::CANCELED, '=', 'OR')->where('task/status', TaskStatus::SUSPENDED, '=', 'OR')->executeCount(),
            'inprogress' => self::count()->with('task')->where('task/status', TaskStatus::WORKING)->executeCount(),
        ];
    }

    /**
     * Get tasks that have something to do with the user
     *
     * @param int $user User
     *
     * @return ReadMapper
     *
     * @since 1.0.0
     */
    public static function getAnyRelatedToUser(int $user) : ReadMapper
    {
        $query = new Builder(self::$db, true);
        $query->innerJoin(TaskMapper::TABLE, TaskMapper::TABLE . '_d2_task')
            ->on(self::TABLE . '_d1.support_ticket_task', '=', TaskMapper::TABLE . '_d2_task.task_id')
            ->innerJoin(TaskElementMapper::TABLE)
                ->on(TaskMapper::TABLE . '_d2_task.task_id', '=', TaskElementMapper::TABLE . '.task_element_task')
                ->on(TaskMapper::TABLE . '_d2_task.task_type', '!=', TaskType::TEMPLATE)
            ->innerJoin(AccountRelationMapper::TABLE)
                ->on(TaskElementMapper::TABLE . '.task_element_id', '=', AccountRelationMapper::TABLE . '.task_account_task_element')
            ->where(AccountRelationMapper::TABLE . '.task_account_account', '=', $user)
            ->orWhere(TaskMapper::TABLE . '_d2_task.task_created_by', '=', $user)
            ->groupBy(self::PRIMARYFIELD);

        // @todo Improving query performance by using raw queries and result arrays for large responses like this
        $sql = <<<SQL
        SELECT DISTINCT task.*, account.*
        FROM task
        INNER JOIN task_element ON task.task_id = task_element.task_element_task
        INNER JOIN task_account ON task_element.task_element_id = task_account.task_account_task_element
        INNER JOIN account ON task.task_created_by = account.account_id
        WHERE
            task.task_status != 1
            AND (
                task_account.task_account_account = {$user}
                OR task.task_created_by = {$user}
            )
        LIMIT 25;
        SQL;

        return self::getAll()->query($query);
    }
}
