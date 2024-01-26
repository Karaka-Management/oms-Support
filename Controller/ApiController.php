<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\Support
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Support\Controller;

use Modules\Support\Models\NullSupportApp;
use Modules\Support\Models\SupportApp;
use Modules\Support\Models\SupportAppMapper;
use Modules\Support\Models\Ticket;
use Modules\Support\Models\TicketElement;
use Modules\Support\Models\TicketElementMapper;
use Modules\Support\Models\TicketMapper;
use Modules\Tasks\Models\TaskMapper;
use Modules\Tasks\Models\TaskStatus;
use Modules\Tasks\Models\TaskType;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;

/**
 * Api controller for the tickets module.
 *
 * @package Modules\Support
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class ApiController extends Controller
{
    /**
     * Validate ticket create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool> Returns the validation array of the request
     *
     * @since 1.0.0
     */
    private function validateTicketCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['title'] = !$request->hasData('title'))
            || ($val['plain'] = !$request->hasData('plain'))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to create a ticket
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTicketCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateTicketCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        $ticket = $this->createTicketFromRequest($request);
        $this->createModel($request->header->account, $ticket, TicketMapper::class, 'ticket', $request->getOrigin());
        $this->createStandardCreateResponse($request, $response, $ticket);
    }

    /**
     * Method to create ticket from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return Ticket Returns the created ticket from the request
     *
     * @since 1.0.0
     */
    private function createTicketFromRequest(RequestAbstract $request) : Ticket
    {
        $request->setData('redirect', 'support/ticket?for={$id}');
        $task       = $this->app->moduleManager->get('Tasks')->createTaskFromRequest($request);
        $task->type = TaskType::HIDDEN;

        $ticket      = new Ticket($task);
        $ticket->app = new NullSupportApp($request->getDataInt('app') ?? 1);

        return $ticket;
    }

    /**
     * Api method to get a ticket
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTicketGet(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        /** @var \Modules\Support\Models\Ticket $ticket */
        $ticket = TicketMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $this->createStandardReturnResponse($request, $response, $ticket);
    }

    /**
     * Api method to update a ticket
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTicketSet(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        /** @var \Modules\Support\Models\Ticket $old */
        $old = TicketMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $new = $this->updateTicketFromRequest($request, clone $old);

        $this->updateModel($request->header->account, $old, $new, TicketMapper::class, 'ticket', $request->getOrigin());
        $this->createStandardUpdateResponse($request, $response, $new);
    }

    /**
     * Method to update an ticket from a request
     *
     * @param RequestAbstract $request Request
     *
     * @return Ticket Returns the updated ticket from the request
     *
     * @since 1.0.0
     */
    private function updateTicketFromRequest(RequestAbstract $request, Ticket $new) : Ticket
    {
        return $new;
    }

    /**
     * Validate ticket element create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool> Returns the validation array of the request
     *
     * @since 1.0.0
     */
    private function validateTicketElementCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['status'] = !TaskStatus::isValidValue((int) $request->getData('status')))
            || ($val['due'] = !((bool) \strtotime((string) $request->getData('due'))))
            || ($val['ticket'] = !(\is_numeric($request->getData('ticket'))))
            || ($val['forward'] = !(\is_numeric($request->hasData('forward') ? $request->getData('forward') : $request->header->account)))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to create a ticket element
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTicketElementCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateTicketElementCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        /** @var \Modules\Support\Models\Ticket $ticket */
        $ticket  = TicketMapper::get()->with('task')->where('id', (int) ($request->getData('ticket')))->execute();
        $element = $this->createTicketElementFromRequest($request, $ticket);

        $old = clone $ticket->task;

        $ticket->task->status   = $element->taskElement->status;
        $ticket->task->priority = $element->taskElement->priority;
        $ticket->task->due      = $element->taskElement->due;

        $this->createModel($request->header->account, $element, TicketElementMapper::class, 'ticketelement', $request->getOrigin());
        $this->updateModel($request->header->account, $old, $ticket->task, TaskMapper::class, 'ticket', $request->getOrigin());
        $this->createStandardCreateResponse($request, $response, $element);
    }

    /**
     * Method to create ticket element from request.
     *
     * @param RequestAbstract $request Request
     * @param Ticket          $ticket  Ticket
     *
     * @return TicketElement Returns the ticket created from the request
     *
     * @since 1.0.0
     */
    private function createTicketElementFromRequest(RequestAbstract $request, Ticket $ticket) : TicketElement
    {
        $taskElement = $this->app->moduleManager->get('Tasks')->createTaskElementFromRequest($request, $ticket->task);

        $ticketElement         = new TicketElement($taskElement);
        $ticketElement->ticket = $ticket->id;

        return $ticketElement;
    }

    /**
     * Api method to get a ticket
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTicketElementGet(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        /** @var \Modules\Support\Models\TicketElement $ticket */
        $ticket = TicketElementMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $this->createStandardReturnResponse($request, $response, $ticket);
    }

    /**
     * Api method to update a ticket
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTicketElementSet(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        /** @var \Modules\Support\Models\TicketElement $old */
        $old = TicketElementMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $new = $this->updateTicketElementFromRequest($request, $response, clone $old);
        $this->updateModel($request->header->account, $old, $new, TicketElementMapper::class, 'ticketelement', $request->getOrigin());

        //$this->updateModel($request->header->account, $ticket, $ticket, TicketMapper::class, 'ticket', $request->getOrigin());
        $this->createStandardUpdateResponse($request, $response, $new);
    }

    /**
     * Method to update an ticket element from a request
     *
     * @param RequestAbstract $request Request
     *
     * @return TicketElement Returns the updated ticket element from the request
     *
     * @since 1.0.0
     */
    private function updateTicketElementFromRequest(RequestAbstract $request, ResponseAbstract $response, TicketElement $new) : TicketElement
    {
        $request->setData('id', $new->taskElement->task, true);
        $this->app->moduleManager->get('Tasks')->apiTaskElementSet($request, $response);

        return $new;
    }

    /**
     * Api method to create a category
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiSupportAppCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateSupportAppCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        $app = $this->createSupportAppFromRequest($request);
        $this->createModel($request->header->account, $app, SupportAppMapper::class, 'app', $request->getOrigin());
        $this->createStandardCreateResponse($request, $response, $app);
    }

    /**
     * Method to create app from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return SupportApp Returns the created app from the request
     *
     * @since 1.0.0
     */
    public function createSupportAppFromRequest(RequestAbstract $request) : SupportApp
    {
        $app       = new SupportApp();
        $app->name = $request->getDataString('name') ?? '';
        $app->unit = $request->getDataInt('unit');

        return $app;
    }

    /**
     * Validate app create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool> Returns the validation array of the request
     *
     * @since 1.0.0
     */
    private function validateSupportAppCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['name'] = !$request->hasData('name'))) {
            return $val;
        }

        return [];
    }
}
