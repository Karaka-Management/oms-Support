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
final class TicketAttributeTypeL11nMapper extends DataMapperAbstract
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    protected static array $columns = [
        'support_attr_type_l11n_id'        => ['name' => 'support_attr_type_l11n_id',       'type' => 'int',    'internal' => 'id'],
        'support_attr_type_l11n_title'     => ['name' => 'support_attr_type_l11n_title',    'type' => 'string', 'internal' => 'title', 'autocomplete' => true],
        'support_attr_type_l11n_type'      => ['name' => 'support_attr_type_l11n_type',      'type' => 'int',    'internal' => 'type'],
        'support_attr_type_l11n_lang'      => ['name' => 'support_attr_type_l11n_lang', 'type' => 'string', 'internal' => 'language'],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $table = 'support_attr_type_l11n';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $primaryField = 'support_attr_type_l11n_id';
}
