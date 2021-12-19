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

use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Ticket mapper class.
 *
 * @package Modules\Support\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
final class TicketAttributeValueMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'support_attr_value_id'       => ['name' => 'support_attr_value_id',       'type' => 'int',    'internal' => 'id'],
        'support_attr_value_default'  => ['name' => 'support_attr_value_default',  'type' => 'bool', 'internal' => 'isDefault'],
        'support_attr_value_type'     => ['name' => 'support_attr_value_type',     'type' => 'int',    'internal' => 'type'],
        'support_attr_value_valueStr' => ['name' => 'support_attr_value_valueStr', 'type' => 'string', 'internal' => 'valueStr'],
        'support_attr_value_valueInt' => ['name' => 'support_attr_value_valueInt', 'type' => 'int', 'internal' => 'valueInt'],
        'support_attr_value_valueDec' => ['name' => 'support_attr_value_valueDec', 'type' => 'float', 'internal' => 'valueDec'],
        'support_attr_value_valueDat' => ['name' => 'support_attr_value_valueDat', 'type' => 'DateTime', 'internal' => 'valueDat'],
        'support_attr_value_lang'     => ['name' => 'support_attr_value_lang',     'type' => 'string', 'internal' => 'language'],
        'support_attr_value_country'  => ['name' => 'support_attr_value_country',  'type' => 'string', 'internal' => 'country'],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'support_attr_value';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD ='support_attr_value_id';
}
