<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Support
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Support\Controller;

use Modules\Admin\Models\NullAccount;
use Modules\Tag\Models\NullTag;
use Modules\Support\Models\Ticket;
use Modules\Support\Models\TicketElement;
use Modules\Support\Models\TicketElementMapper;
use Modules\Support\Models\TicketMapper;
use Modules\Support\Models\SupportApp;
use Modules\Support\Models\NullSupportApp;
use Modules\Support\Models\SupportAppMapper;
use Modules\Tasks\Models\TaskStatus;
use Modules\Tasks\Models\TaskType;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\NotificationLevel;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Model\Message\FormValidation;
use phpOMS\Utils\Parser\Markdown\Markdown;

/**
 * Api controller for the tickets module.
 *
 * @package Modules\Support
 * @license OMS License 1.0
 * @link    https://orange-management.org
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
        if (($val['title'] = empty($request->getData('title')))
            || ($val['plain'] = empty($request->getData('plain')))
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
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTicketCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!empty($val = $this->validateTicketCreate($request))) {
            $response->set($request->uri->__toString(), new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $ticket = $this->createTicketFromRequest($request);

        $this->createModel($request->header->account, $ticket, TicketMapper::class, 'ticket', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Ticket', 'Ticket successfully created.', $ticket);
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
        $task = $this->app->moduleManager->get('Tasks')->createTaskFromRequest($request);
        $task->setType(TaskType::HIDDEN);

        $ticket      = new Ticket($task);
        $ticket->app = new NullSupportApp((int) ($request->getData('app') ?? 1));

        if ($request->getData('for') !== null) {
            $ticket->for = new NullAccount((int) $request->getData('for'));
        }

        return $ticket;
    }

    /**
     * Api method to get a ticket
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTicketGet(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        $ticket = TicketMapper::get((int) $request->getData('id'));
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Ticket', 'Ticket successfully returned.', $ticket);
    }

    /**
     * Api method to update a ticket
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTicketSet(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        $old = clone TicketMapper::get((int) $request->getData('id'));
        $new = $this->updateTicketFromRequest($request);
        $this->updateModel($request->header->account, $old, $new, TicketMapper::class, 'ticket', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Ticket', 'Ticket successfully updated.', $new);
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
    private function updateTicketFromRequest(RequestAbstract $request) : Ticket
    {
        $ticket = TicketMapper::get((int) ($request->getData('id')));

        return $ticket;
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
            || ($val['forward'] = !(\is_numeric(empty($request->getData('forward')) ? $request->header->account : $request->getData('forward'))))
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
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTicketElementCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!empty($val = $this->validateTicketElementCreate($request))) {
            $response->set('ticket_element_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $ticket  = TicketMapper::get((int) ($request->getData('ticket')));
        $element = $this->createTicketElementFromRequest($request, $ticket);
        $ticket->task->setStatus($element->taskElement->getStatus());
        $ticket->task->setPriority($element->taskElement->getPriority());
        $ticket->task->setDue($element->taskElement->due);

        $this->createModel($request->header->account, $element, TicketElementMapper::class, 'ticketelement', $request->getOrigin());
        $this->updateModel($request->header->account, $ticket, $ticket, TicketMapper::class, 'ticket', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Ticket element', 'Ticket element successfully created.', $element);
    }

    /**
     * Method to create ticket element from request.
     *
     * @param RequestAbstract $request Request
     * @param Ticket            $ticket    Ticket
     *
     * @return TicketElement Returns the ticket created from the request
     *
     * @since 1.0.0
     */
    private function createTicketElementFromRequest(RequestAbstract $request, Ticket $ticket) : TicketElement
    {
        $taskElement = $this->app->moduleManager->get('Tasks')->createTaskElementFromRequest($request);

        $ticketElement = new TicketElement($taskElement);
        $ticketElement->time = (int) $request->getData('time') ?? 0;
        $ticketElement->ticket = $ticket->getId();

        return $ticketElement;
    }

    /**
     * Api method to get a ticket
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTicketElementGet(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        $ticket = TicketElementMapper::get((int) $request->getData('id'));
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Ticket element', 'Ticket element successfully returned.', $ticket);
    }

    /**
     * Api method to update a ticket
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTicketElementSet(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        $old = clone TicketElementMapper::get((int) $request->getData('id'));
        $new = $this->updateTicketElementFromRequest($request);
        $this->updateModel($request->header->account, $old, $new, TicketElementMapper::class, 'ticketelement', $request->getOrigin());

        //$this->updateModel($request->header->account, $ticket, $ticket, TicketMapper::class, 'ticket', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Ticket element', 'Ticket element successfully updated.', $new);
    }

    /**
     * Method to update an ticket element from a request
     *
     * @param RequestAbstract $request Request
     *
     * @return TaskElement Returns the updated ticket element from the request
     *
     * @since 1.0.0
     */
    private function updateTicketElementFromRequest(RequestAbstract $request) : TaskElement
    {
        $element = TicketElementMapper::get((int) ($request->getData('id')));
        $element->setDue(new \DateTime((string) ($request->getData('due') ?? $element->getDue()->format('Y-m-d H:i:s'))));
        $element->setStatus((int) ($request->getData('status') ?? $element->getStatus()));
        $element->description    = Markdown::parse((string) ($request->getData('plain') ?? $element->descriptionRaw));
        $element->descriptionRaw = (string) ($request->getData('plain') ?? $element->descriptionRaw);

        $tos = $request->getData('to') ?? $request->header->account;
        if (!\is_array($tos)) {
            $tos = [$tos];
        }

        $ccs = $request->getData('cc') ?? [];
        if (!\is_array($ccs)) {
            $ccs = [$ccs];
        }

        foreach ($tos as $to) {
            $element->addTo($to);
        }

        foreach ($ccs as $cc) {
            $element->addCC($cc);
        }

        return $element;
    }

    /**
     * Api method to create a category
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiSupportAppCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!empty($val = $this->validateSupportAppCreate($request))) {
            $response->set('qa_app_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $app = $this->createSupportAppFromRequest($request);
        $this->createModel($request->header->account, $app, SupportAppMapper::class, 'app', $request->getOrigin());

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'App', 'App successfully created.', $app);
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
        $app = new SupportApp();
        $app->name = $request->getData('name') ?? '';

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
        if (($val['name'] = empty($request->getData('name')))) {
            return $val;
        }

        return [];
    }
}
