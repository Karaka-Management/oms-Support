<?php
/**
 * Jingga
 *
 * PHP Version 8.2
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
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'   => $this->id,
            'task' => $this->task,
            'app'  => $this->app,
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
