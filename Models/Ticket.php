<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   Modules\Support\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
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
 * @link    https://karaka.app
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
    private array $ticketElements = [];

    /**
     * Account this ticket is for
     *
     * This is not the person who is working on the ticket but the person who needs help.
     * This can be different from the person who created it.
     *
     * @var null|Account
     * @since 1.0.0
     */
    public ?Account $for = null;

    /**
     * Attributes.
     *
     * @var int[]|TicketAttribute[]
     * @since 1.0.0
     */
    private array $attributes = [];

    /**
     * Constructor.
     *
     * @param null|Task $task Creates the ticket from a task
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

    /**
     * Add attribute to item
     *
     * @param TicketAttribute $attribute Note
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function addAttribute(TicketAttribute $attribute) : void
    {
        $this->attributes[] = $attribute;
    }

    /**
     * Get attributes
     *
     * @return int[]|TicketAttribute[]
     *
     * @since 1.0.0
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'                => $this->id,
            'task'              => $this->task,
            'app'               => $this->app,
            'for'               => $this->for,
            'ticketElements'    => $this->ticketElements,
            'attributes'        => $this->attributes,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
