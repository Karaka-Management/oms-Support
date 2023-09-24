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

use Modules\Admin\Models\NullAccount;
use Modules\Support\Models\NullSupportApp;
use Modules\Support\Models\NullTicketAttributeType;
use Modules\Support\Models\NullTicketAttributeValue;
use Modules\Support\Models\SupportApp;
use Modules\Support\Models\SupportAppMapper;
use Modules\Support\Models\Ticket;
use Modules\Support\Models\TicketAttribute;
use Modules\Support\Models\TicketAttributeMapper;
use Modules\Support\Models\TicketAttributeType;
use Modules\Support\Models\TicketAttributeTypeL11nMapper;
use Modules\Support\Models\TicketAttributeTypeMapper;
use Modules\Support\Models\TicketAttributeValue;
use Modules\Support\Models\TicketAttributeValueL11nMapper;
use Modules\Support\Models\TicketAttributeValueMapper;
use Modules\Support\Models\TicketElement;
use Modules\Support\Models\TicketElementMapper;
use Modules\Support\Models\TicketMapper;
use Modules\Tasks\Models\TaskMapper;
use Modules\Tasks\Models\TaskStatus;
use Modules\Tasks\Models\TaskType;
use phpOMS\Localization\BaseStringL11n;
use phpOMS\Localization\ISO639x1Enum;
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
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTicketCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
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
        $task = $this->app->moduleManager->get('Tasks')->createTaskFromRequest($request);
        $task->setType(TaskType::HIDDEN);

        $ticket      = new Ticket($task);
        $ticket->app = new NullSupportApp($request->getDataInt('app') ?? 1);

        if ($request->hasData('for')) {
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
    public function apiTicketGet(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
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
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTicketSet(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
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
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTicketElementCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
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

        $ticket->task->setStatus($element->taskElement->getStatus());
        $ticket->task->setPriority($element->taskElement->getPriority());
        $ticket->task->due = $element->taskElement->due;

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
        $ticketElement->time   = $request->getDataInt('time') ?? 0;
        $ticketElement->ticket = $ticket->id;

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
    public function apiTicketElementGet(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
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
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTicketElementSet(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
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
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiSupportAppCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
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

    /**
     * Api method to create ticket attribute
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
    public function apiTicketAttributeCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateTicketAttributeCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        /*
        @todo: If value data is in attribute create, create attribute value

        if () {
            $attrValue = $this->createTicketAttributeValueFromRequest($request);
            $this->createModel($request->header->account, $attrValue, TicketAttributeValueMapper::class, 'attr_value', $request->getOrigin());
        }
        */

        $attribute = $this->createTicketAttributeFromRequest($request);
        $this->createModel($request->header->account, $attribute, TicketAttributeMapper::class, 'attribute', $request->getOrigin());
        $this->createStandardCreateResponse($request, $response, $attribute);
    }

    /**
     * Method to create ticket attribute from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return TicketAttribute
     *
     * @since 1.0.0
     */
    private function createTicketAttributeFromRequest(RequestAbstract $request) : TicketAttribute
    {
        $attribute         = new TicketAttribute();
        $attribute->ticket = (int) $request->getData('ticket');
        $attribute->type   = new NullTicketAttributeType((int) $request->getData('type'));
        $attribute->value  = new NullTicketAttributeValue((int) $request->getData('value'));

        return $attribute;
    }

    /**
     * Validate ticket attribute create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateTicketAttributeCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['type'] = !$request->hasData('type'))
            || ($val['value'] = !$request->hasData('value'))
            || ($val['ticket'] = !$request->hasData('ticket'))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to create ticket attribute l11n
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
    public function apiTicketAttributeTypeL11nCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateTicketAttributeTypeL11nCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        $attrL11n = $this->createTicketAttributeTypeL11nFromRequest($request);
        $this->createModel($request->header->account, $attrL11n, TicketAttributeTypeL11nMapper::class, 'attr_type_l11n', $request->getOrigin());
        $this->createStandardCreateResponse($request, $response, $attrL11n);
    }

    /**
     * Method to create ticket attribute l11n from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return BaseStringL11n
     *
     * @since 1.0.0
     */
    private function createTicketAttributeTypeL11nFromRequest(RequestAbstract $request) : BaseStringL11n
    {
        $attrL11n      = new BaseStringL11n();
        $attrL11n->ref = $request->getDataInt('type') ?? 0;
        $attrL11n->setLanguage(
            $request->getDataString('language') ?? $request->header->l11n->language
        );
        $attrL11n->content = $request->getDataString('title') ?? '';

        return $attrL11n;
    }

    /**
     * Validate ticket attribute l11n create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateTicketAttributeTypeL11nCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['title'] = !$request->hasData('title'))
            || ($val['type'] = !$request->hasData('type'))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to create ticket attribute type
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
    public function apiTicketAttributeTypeCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateTicketAttributeTypeCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        $attrType = $this->createTicketAttributeTypeFromRequest($request);
        $this->createModel($request->header->account, $attrType, TicketAttributeTypeMapper::class, 'attr_type', $request->getOrigin());
        $this->createStandardCreateResponse($request, $response, $attrType);
    }

    /**
     * Method to create ticket attribute from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return TicketAttributeType
     *
     * @since 1.0.0
     */
    private function createTicketAttributeTypeFromRequest(RequestAbstract $request) : TicketAttributeType
    {
        $attrType = new TicketAttributeType();
        $attrType->setL11n($request->getDataString('title') ?? '', $request->getDataString('language') ?? ISO639x1Enum::_EN);
        $attrType->fields = $request->getDataInt('fields') ?? 0;
        $attrType->custom = $request->getDataBool('custom') ?? false;

        return $attrType;
    }

    /**
     * Validate ticket attribute create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateTicketAttributeTypeCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['title'] = !$request->hasData('title'))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to create ticket attribute value
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
    public function apiTicketAttributeValueCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateTicketAttributeValueCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        $attrValue = $this->createTicketAttributeValueFromRequest($request);
        $this->createModel($request->header->account, $attrValue, TicketAttributeValueMapper::class, 'attr_value', $request->getOrigin());

        if ($attrValue->isDefault) {
            $this->createModelRelation(
                $request->header->account,
                (int) $request->getData('type'),
                $attrValue->id,
                TicketAttributeTypeMapper::class, 'defaults', '', $request->getOrigin()
            );
        }

        $this->createStandardCreateResponse($request, $response, $attrValue);
    }

    /**
     * Method to create ticket attribute value from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return TicketAttributeValue
     *
     * @since 1.0.0
     */
    private function createTicketAttributeValueFromRequest(RequestAbstract $request) : TicketAttributeValue
    {
        /** @var TicketAttributeType $type */
        $type = TicketAttributeTypeMapper::get()
            ->where('id', $request->getDataInt('type') ?? 0)
            ->execute();

        $attrValue            = new TicketAttributeValue();
        $attrValue->isDefault = $request->getDataBool('default') ?? false;
        $attrValue->setValue($request->getDataString('value'), $type->datatype);

        if ($request->hasData('title')) {
            $attrValue->setL11n($request->getDataString('title') ?? '', $request->getDataString('language') ?? ISO639x1Enum::_EN);
        }

        return $attrValue;
    }

    /**
     * Validate ticket attribute value create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateTicketAttributeValueCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['type'] = !$request->hasData('type'))
            || ($val['value'] = !$request->hasData('value'))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to create ticket attribute l11n
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
    public function apiTicketAttributeValueL11nCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateTicketAttributeValueL11nCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        $attrL11n = $this->createTicketAttributeValueL11nFromRequest($request);
        $this->createModel($request->header->account, $attrL11n, TicketAttributeValueL11nMapper::class, 'attr_value_l11n', $request->getOrigin());
        $this->createStandardCreateResponse($request, $response, $attrL11n);
    }

    /**
     * Method to create ticket attribute l11n from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return BaseStringL11n
     *
     * @since 1.0.0
     */
    private function createTicketAttributeValueL11nFromRequest(RequestAbstract $request) : BaseStringL11n
    {
        $attrL11n      = new BaseStringL11n();
        $attrL11n->ref = $request->getDataInt('value') ?? 0;
        $attrL11n->setLanguage(
            $request->getDataString('language') ?? $request->header->l11n->language
        );
        $attrL11n->content = $request->getDataString('title') ?? '';

        return $attrL11n;
    }

    /**
     * Validate ticket attribute l11n create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateTicketAttributeValueL11nCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['title'] = !$request->hasData('title'))
            || ($val['value'] = !$request->hasData('value'))
        ) {
            return $val;
        }

        return [];
    }
}
