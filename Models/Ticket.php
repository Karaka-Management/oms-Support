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

use Modules\Admin\Models\Account;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskType;

/**
 * Ticket class.
 *
 * @package Modules\Support\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
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
    protected int $id = 0;

    public Task $task;

    public SupportApp $app;

    private array $ticketElements = [];

    public ?Account $for = null;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct(Task $task = null)
    {
        $this->task = $task ?? new Task();
        $this->task->setType(TaskType::HIDDEN);
        $this->app = new SupportApp();
    }

    /**
     * Get id.
     *
     * @return int Model id
     *
     * @since 1.0.0
     */
    public function getId() : int
    {
        return $this->id;
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
}
