<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Support\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Support\Models;

use phpOMS\Stdlib\Base\Enum;

/**
 * Support status enum.
 *
 * @package Modules\Support\Models
 * @license OMS License 2.2
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class SupportStatus extends Enum
{
    public const OPEN = 0;

    public const REVIEW = 1;

    public const LIVE = 2;

    public const HOLD = 3;

    public const UNSOLVABLE = 4;

    public const SOLVED = 5;

    public const CLOSED = 6;
}
