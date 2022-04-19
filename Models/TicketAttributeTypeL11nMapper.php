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
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Support\Models;

use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Ticket mapper class.
 *
 * @package Modules\Support\Models
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 */
final class TicketAttributeTypeL11nMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
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
    public const TABLE = 'support_attr_type_l11n';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD ='support_attr_type_l11n_id';
}
