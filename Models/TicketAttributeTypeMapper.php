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
final class TicketAttributeTypeMapper extends DataMapperAbstract
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    protected static array $columns = [
        'support_attr_type_id'       => ['name' => 'support_attr_type_id',     'type' => 'int',    'internal' => 'id'],
        'support_attr_type_name'     => ['name' => 'support_attr_type_name',   'type' => 'string', 'internal' => 'name', 'autocomplete' => true],
        'support_attr_type_fields'   => ['name' => 'support_attr_type_fields', 'type' => 'int',    'internal' => 'fields'],
        'support_attr_type_custom'   => ['name' => 'support_attr_type_custom', 'type' => 'bool', 'internal' => 'custom'],
        'support_attr_type_pattern'  => ['name' => 'support_attr_type_pattern', 'type' => 'string', 'internal' => 'validationPattern'],
        'support_attr_type_required' => ['name' => 'support_attr_type_required', 'type' => 'bool', 'internal' => 'isRequired'],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    protected static array $hasMany = [
        'l11n' => [
            'mapper'            => TicketAttributeTypeL11nMapper::class,
            'table'             => 'support_attr_type_l11n',
            'self'              => 'support_attr_type_l11n_type',
            'column'            => 'title',
            'conditional'       => true,
            'external'          => null,
        ],
        'defaults' => [
            'mapper'            => TicketAttributeValueMapper::class,
            'table'             => 'support_ticket_attr_default',
            'self'              => 'support_ticket_attr_default_type',
            'external'          => 'support_ticket_attr_default_value',
            'conditional'       => false,
        ],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $table = 'support_attr_type';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $primaryField = 'support_attr_type_id';
}
