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
 *
 * @template T of TicketAttributeType
 * @extends DataMapperFactory<T>
 */
final class TicketAttributeTypeMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
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
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'l11n' => [
            'mapper'            => TicketAttributeTypeL11nMapper::class,
            'table'             => 'support_attr_type_l11n',
            'self'              => 'support_attr_type_l11n_type',
            'column'            => 'content',
            'external'          => null,
        ],
        'defaults' => [
            'mapper'            => TicketAttributeValueMapper::class,
            'table'             => 'support_ticket_attr_default',
            'self'              => 'support_ticket_attr_default_type',
            'external'          => 'support_ticket_attr_default_value'
        ],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'support_attr_type';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'support_attr_type_id';
}
