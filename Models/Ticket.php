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

use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskType;

/**
 * Ticket class.
 *
 * @package Modules\Support\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class Ticket
{
    /**
     * ID.
     *
     * @var int
     * @since 1.0.0
     */
    public int $id = 0;

    /**
     * The ticket is using a task.
     *
     * @var Task
     * @since 1.0.0
     */
    public Task $task;

    /**
     * App this ticket belongs to.
     *
     * @var SupportApp
     * @since 1.0.0
     */
    public SupportApp $app;

    /**
     * Ticket elements.
     *
     * @var TicketElement[]
     * @since 1.0.0
     */
    public array $ticketElements = [];

    /**
     * Constructor.
     *
     * @param null|Task $task Creates the ticket from a task
     *
     * @since 1.0.0
     */
    public function __construct(?Task $task = null)
    {
        $this->task       = $task ?? new Task();
        $this->task->type = TaskType::HIDDEN;
        $this->app        = new SupportApp();
    }

    /**
     * Adding new task element.
     *
     * @param TicketElement $element Ticket element
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function addElement(TicketElement $element) : int
    {
        $this->ticketElements[] = $element;

        \end($this->ticketElements);
        $key = (int) \key($this->ticketElements);
        \reset($this->ticketElements);

        return $key;
    }

    /**
     * Remove Element from list.
     *
     * @param int $id Ticket element
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function removeElement($id) : bool
    {
        if (isset($this->ticketElements[$id])) {
            unset($this->ticketElements[$id]);

            return true;
        }

        return false;
    }

    /**
     * Get ticket elements.
     *
     * @return TicketElement[]
     *
     * @since 1.0.0
     */
    public function getTicketElements() : array
    {
        return $this->ticketElements;
    }

    /**
     * Get ticket elements in inverted order.
     *
     * @return TicketElement[]
     *
     * @since 1.0.0
     */
    public function invertTicketElements() : array
    {
        return \array_reverse($this->ticketElements);
    }

    /**
     * Get ticket elements.
     *
     * @param int $id Element id
     *
     * @return TicketElement
     *
     * @since 1.0.0
     */
    public function getTicketElement(int $id) : TicketElement
    {
        return $this->ticketElements[$id] ?? new NullTicketElement();
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'             => $this->id,
            'task'           => $this->task,
            'app'            => $this->app,
            'ticketElements' => $this->ticketElements,
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
