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
use Modules\Support\Models\AttributeValueType;
use Modules\Support\Models\NullSupportApp;
use Modules\Support\Models\NullTicketAttributeType;
use Modules\Support\Models\NullTicketAttributeValue;
use Modules\Support\Models\SupportApp;
use Modules\Support\Models\SupportAppMapper;
use Modules\Support\Models\Ticket;
use Modules\Support\Models\TicketAttribute;
use Modules\Support\Models\TicketAttributeMapper;
use Modules\Support\Models\TicketAttributeType;
use Modules\Support\Models\TicketAttributeTypeL11n;
use Modules\Support\Models\TicketAttributeTypeL11nMapper;
use Modules\Support\Models\TicketAttributeTypeMapper;
use Modules\Support\Models\TicketAttributeValue;
use Modules\Support\Models\TicketAttributeValueMapper;
use Modules\Support\Models\TicketElement;
use Modules\Support\Models\TicketElementMapper;
use Modules\Support\Models\TicketMapper;
use Modules\Tasks\Models\TaskStatus;
use Modules\Tasks\Models\TaskType;
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
        $request->setData('redirect', '{/prefix}support/ticket?for={?id}');
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
        $ticket->task->due = $element->taskElement->due;

        $this->createModel($request->header->account, $element, TicketElementMapper::class, 'ticketelement', $request->getOrigin());
        $this->updateModel($request->header->account, $ticket, $ticket, TicketMapper::class, 'ticket', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Ticket element', 'Ticket element successfully created.', $element);
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
        $ticketElement->time   = (int) $request->getData('time') ?? 0;
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
     * @return TicketElementMapper Returns the updated ticket element from the request
     *
     * @since 1.0.0
     */
    private function updateTicketElementFromRequest(RequestAbstract $request) : TicketElementMapper
    {
        $element                              = TicketElementMapper::get((int) ($request->getData('id')));
        $element->taskElement->due            = new \DateTime((string) ($request->getData('due') ?? $element->getDue()->format('Y-m-d H:i:s')));
        $element->taskElement->description    = Markdown::parse((string) ($request->getData('plain') ?? $element->taskElement->descriptionRaw));
        $element->taskElement->descriptionRaw = (string) ($request->getData('plain') ?? $element->taskElement->descriptionRaw);
        $element->taskElement->setStatus((int) ($request->getData('status') ?? $element->taskElement->getStatus()));

        $tos = $request->getData('to') ?? $request->header->account;
        if (!\is_array($tos)) {
            $tos = [$tos];
        }

        $ccs = $request->getData('cc') ?? [];
        if (!\is_array($ccs)) {
            $ccs = [$ccs];
        }

        foreach ($tos as $to) {
            $element->taskElement->addTo($to);
        }

        foreach ($ccs as $cc) {
            $element->taskElement->addCC($cc);
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
        $app       = new SupportApp();
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
    public function apiTicketAttributeCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!empty($val = $this->validateTicketAttributeCreate($request))) {
            $response->set('attribute_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        /**
        @todo: If value data is in attribute create, create attribute value

        if () {
            $attrValue = $this->createTicketAttributeValueFromRequest($request);
            $this->createModel($request->header->account, $attrValue, TicketAttributeValueMapper::class, 'attr_value', $request->getOrigin());
        }*/

        $attribute = $this->createTicketAttributeFromRequest($request);
        $this->createModel($request->header->account, $attribute, TicketAttributeMapper::class, 'attribute', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Attribute', 'Attribute successfully created', $attribute);
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
        if (($val['type'] = empty($request->getData('type')))
            || ($val['value'] = empty($request->getData('value')))
            || ($val['ticket'] = empty($request->getData('ticket')))
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
    public function apiTicketAttributeTypeL11nCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!empty($val = $this->validateTicketAttributeTypeL11nCreate($request))) {
            $response->set('attr_type_l11n_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $attrL11n = $this->createTicketAttributeTypeL11nFromRequest($request);
        $this->createModel($request->header->account, $attrL11n, TicketAttributeTypeL11nMapper::class, 'attr_type_l11n', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Attribute type localization', 'Attribute type localization successfully created', $attrL11n);
    }

    /**
     * Method to create ticket attribute l11n from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return TicketAttributeTypeL11n
     *
     * @since 1.0.0
     */
    private function createTicketAttributeTypeL11nFromRequest(RequestAbstract $request) : TicketAttributeTypeL11n
    {
        $attrL11n = new TicketAttributeTypeL11n();
        $attrL11n->setType((int) ($request->getData('type') ?? 0));
        $attrL11n->setLanguage((string) (
            $request->getData('language') ?? $request->getLanguage()
        ));
        $attrL11n->title = (string) ($request->getData('title') ?? '');

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
        if (($val['title'] = empty($request->getData('title')))
            || ($val['type'] = empty($request->getData('type')))
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
    public function apiTicketAttributeTypeCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!empty($val = $this->validateTicketAttributeTypeCreate($request))) {
            $response->set('attr_type_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $attrType = $this->createTicketAttributeTypeFromRequest($request);
        $attrType->setL11n($request->getData('title'), $request->getData('language'));
        $this->createModel($request->header->account, $attrType, TicketAttributeTypeMapper::class, 'attr_type', $request->getOrigin());

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Attribute type', 'Attribute type successfully created', $attrType);
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
        $attrType       = new TicketAttributeType();
        $attrType->name = (string) ($request->getData('name') ?? '');
        $attrType->setFields((int) ($request->getData('fields') ?? 0));
        $attrType->setCustom((bool) ($request->getData('custom') ?? false));

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
        if (($val['name'] = empty($request->getData('name')))
            || ($val['title'] = empty($request->getData('title')))
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
    public function apiTicketAttributeValueCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!empty($val = $this->validateTicketAttributeValueCreate($request))) {
            $response->set('attr_value_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $attrValue = $this->createTicketAttributeValueFromRequest($request);
        $this->createModel($request->header->account, $attrValue, TicketAttributeValueMapper::class, 'attr_value', $request->getOrigin());

        if ($attrValue->isDefault) {
            $this->createModelRelation(
                $request->header->account,
                (int) $request->getData('attributetype'),
                $attrValue->getId(),
                TicketAttributeTypeMapper::class, 'defaults', '', $request->getOrigin()
            );
        }

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Attribute value', 'Attribute value successfully created', $attrValue);
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
        $attrValue = new TicketAttributeValue();

        $type = $request->getData('type') ?? 0;
        if ($type === AttributeValueType::_INT) {
            $attrValue->valueInt = (int) $request->getData('value');
        } elseif ($type === AttributeValueType::_STRING) {
            $attrValue->valueStr = (string) $request->getData('value');
        } elseif ($type === AttributeValueType::_FLOAT) {
            $attrValue->valueDec = (float) $request->getData('value');
        } elseif ($type === AttributeValueType::_DATETIME) {
            $attrValue->valueDat = new \DateTime($request->getData('value') ?? '');
        }

        $attrValue->type      = $type;
        $attrValue->isDefault = (bool) ($request->getData('default') ?? false);

        if ($request->hasData('language')) {
            $attrValue->setLanguage((string) ($request->getData('language') ?? $request->getLanguage()));
        }

        if ($request->hasData('country')) {
            $attrValue->setCountry((string) ($request->getData('country') ?? $request->header->l11n->getCountry()));
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
        if (($val['type'] = empty($request->getData('type')))
            || ($val['value'] = empty($request->getData('value')))
        ) {
            return $val;
        }

        return [];
    }
}
