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

use Modules\Tasks\Models\TaskElement;

/**
 * Ticket element class.
 *
 * @package Modules\Support\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
class TicketElement implements \JsonSerializable
{
    /**
     * Id.
     *
     * @var int
     * @since 1.0.0
     */
    private int $id = 0;

    /**
     * Ticket element time
     *
     * @var int
     * @since 1.0.0
     */
    public int $time = 0;

    /**
     * Ticket.
     *
     * @var int
     * @since 1.0.0
     */
    public int $ticket = 0;

    /**
     * Task element
     *
     * @since 1.0.0
     */
    public TaskElement $taskElement;

    /**
	 * Constructor.
	 * @since 1.0.0
	 */
    public function __construct(TaskElement $taskElement = null)
    {
        $this->taskElement = $taskElement ?? new TaskElement();
    }

    /**
     * Get id
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getId() : int
    {
        return $this->id;
    }
}