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

use Modules\Tasks\Models\TaskElement;

/**
 * Ticket element class.
 *
 * @package Modules\Support\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
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
    public int $id = 0;

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
     * @var TaskElement
     * @since 1.0.0
     */
    public TaskElement $taskElement;

    /**
     * Constructor.
     *
     * @param null|TaskElement $taskElement Creates a ticket elmenet from a task element
     *
     * @since 1.0.0
     */
    public function __construct(?TaskElement $taskElement = null)
    {
        $this->taskElement = $taskElement ?? new TaskElement();
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'          => $this->id,
            'ticket'      => $this->ticket,
            'taskElement' => $this->taskElement,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize() : mixed
    {
        return $this->toArray();
    }
}
