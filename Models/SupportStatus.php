<?php
/**
 * Orange Management
 *
 * PHP Version 7.2
 *
 * @package    Modules\Support
 * @copyright  Dennis Eichhorn
 * @license    OMS License 1.0
 * @version    1.0.0
 * @link       http://website.orange-management.de
 */
declare(strict_types=1);

namespace Modules\Support;

use phpOMS\Stdlib\Base\Enum;

/**
 * Support status enum.
 *
 * @package    Modules\Support
 * @license    OMS License 1.0
 * @link       http://website.orange-management.de
 * @since      1.0.0
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
