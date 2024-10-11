<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Support
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Support\Controller;

use Modules\Admin\Models\AccountMapper;
use Modules\Admin\Models\ContactType;
use Modules\Messages\Models\EmailMapper;
use Modules\Support\Models\NullSupportApp;
use Modules\Support\Models\SettingsEnum;
use Modules\Support\Models\SupportApp;
use Modules\Support\Models\SupportAppMapper;
use Modules\Support\Models\Ticket;
use Modules\Support\Models\TicketMapper;
use Modules\Tasks\Models\TaskElementMapper;
use Modules\Tasks\Models\TaskMapper;
use Modules\Tasks\Models\TaskStatus;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;

/**
 * Api controller for the tickets module.
 *
 * @package Modules\Support
 * @license OMS License 2.2
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

        $this->notifyEmail($ticket, $response->header->l11n->language);

        $this->createStandardCreateResponse($request, $response, $ticket);
    }

    /**
     * Create email notification regarding ticket
     *
     * @param Ticket $ticket   Ticket the notification is for
     * @param string $language Language of the email (e.g. 'en', 'de')
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function notifyEmail(Ticket $ticket, string $language) : void
    {
        // @todo decide what to send via email
        // status changes, redirects, answers?
        // Careful, don't notify own changes and internal changes (e.g. internal note)

        $email = '';

        $account = null;

        if ($this->app->moduleManager->isActive('ClientManagement')) {
            $client = \Modules\ClientManagement\Models\ClientMapper::get()
                ->with('attributes')
                ->with('attributes/types')
                ->with('attributes/value')
                ->with('account/contacts')
                ->where('account', $ticket->task->for?->id)
                ->where('attributes/types/name', ['support_emails', 'support_email_address'], 'IN')
                ->execute();

            // @todo should this really be a string? Shouldn't this be a contact element? Same goes for billing.
            $email   = $client->getAttribute('support_email_address')->value->valueStr;
            $account = $client->account;
        }

        if (empty($email)) {
            $supplier = null;

            if ($this->app->moduleManager->isActive('SupplierManagement')) {
                $supplier = \Modules\SupplierManagement\Models\SupplierMapper::get()
                    ->with('attributes')
                    ->with('attributes/types')
                    ->with('attributes/value')
                    ->with('account/contacts')
                    ->where('account', $ticket->task->for?->id)
                    ->where('attributes/types/name', ['support_emails', 'support_email_address'], 'IN')
                    ->execute();

                if ($supplier->getAttribute('support_emails')->value->getValue() === false) {
                    return;
                }
            }

            if ($supplier === null) {
                return;
            }

            // @todo should this really be a string? Shouldn't this be a contact element? Same goes for billing.
            $email   = $supplier->getAttribute('support_email_address')->value->valueStr;
            $account = $supplier->account;
        }

        if (empty($email)) {
            $account = AccountMapper::get()
                ->with('contacts')
                ->where('id', $ticket->task->for?->id)
                ->execute();

            $email = $account->getContactByType(ContactType::EMAIL)->content;
        }

        if (empty($email)) {
            return;
        }

        $handler = $this->app->moduleManager->get('Admin', 'Api')->setUpServerMailHandler();

        /** @var \Model\Setting $supportTemplate */
        $supportTemplate = $this->app->appSettings->get(
            names: SettingsEnum::SUPPORT_EMAIL_TEMPLATE,
            module: 'Support'
        );

        $baseEmail = EmailMapper::get()
            ->with('l11n')
            ->where('id', (int) $supportTemplate->content)
            ->execute();

        $mail = clone $baseEmail;

        $status = false;
        if ($mail->id !== 0) {
            $status = $this->app->moduleManager->get('Admin', 'Api')->setupEmailDefaults($mail, $this->app->l11nServer->language);
        }

        $mail->addTo($email);

        // @todo probably needs to be changed to messageId = \uniqid() . '-' . $ticket->id ?!
        // Careful, uniqueid is overwritten in the email class, will need check for if empty
        $mail->addCustomHeader('ticket_id', (string) $ticket->id);

        $lang = include __DIR__ . '/../../Tasks/Theme/Backend/Lang/' . $language . '.lang.php';

        $mail->template = \array_merge(
            $mail->template,
            [
                '{user_name}'      => $account?->login,
                '{ticket_id}'      => $ticket->id,
                '{ticket_status}'  => $lang['Tasks']['S' . $ticket->task->status],
                '{ticket_subject}' => $ticket->task->title,
            ]
        );

        if ($status) {
            $status = $handler->send($mail);
        }

        if (!$status) {
            \phpOMS\Log\FileLogger::getInstance()->error(
                \phpOMS\Log\FileLogger::MSG_FULL, [
                    'message' => 'Couldn\'t send ticket mail: ' . $mail->id . ' - ' . $ticket->id,
                    'line'    => __LINE__,
                    'file'    => self::class,
                ]
            );
        }
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
        $request->setData('redirect', 'support/ticket/view?for={$id}');
        $task = $this->app->moduleManager->get('Tasks', 'Api')->createTaskFromRequest($request);

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
        $old = TicketMapper::get()
            ->with('task')
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $new = $this->updateTicketFromRequest($request, clone $old);

        $this->updateModel($request->header->account, $old, $new, TicketMapper::class, 'ticket', $request->getOrigin());

        $this->notifyEmail($new, $response->header->l11n->language);

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

        // @question Consider to use the apiTaskElementCreate() function

        /** @var \Modules\Support\Models\Ticket $ticket */
        $ticket = TicketMapper::get()
            ->with('task')
            ->where('id', (int) ($request->getData('ticket')))
            ->execute();

        $element = $this->app->moduleManager->get('Tasks')->createTaskElementFromRequest($request, $ticket->task);

        $old = clone $ticket->task;

        $ticket->task->status   = $element->status;
        $ticket->task->priority = $element->priority;
        $ticket->task->due      = $element->due;

        $this->createModel($request->header->account, $element, TaskElementMapper::class, 'ticket_element', $request->getOrigin());
        $this->updateModel($request->header->account, $old, $ticket->task, TaskMapper::class, 'ticket', $request->getOrigin());

        $ticket->task->taskElements[] = $element;

        $this->notifyEmail($ticket, $response->header->l11n->language);

        $this->createStandardCreateResponse($request, $response, $element);
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
        $this->app->moduleManager->get('Tasks')->apiTaskElementSet($request, $response);

        /** @var \Modules\Tasks\Models\TaskElement $new */
        $new = $response->getDataArray($request->uri->__toString())['response'] ?? null;
        if ($new === null) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidUpdateResponse($request, $response, $new);
        }

        $ticket = TicketMapper::get()
            ->with('task')
            ->where('task', $new->task)
            ->execute();

        $this->notifyEmail($ticket, $response->header->l11n->language);

        //$this->updateModel($request->header->account, $ticket, $ticket, TicketMapper::class, 'ticket', $request->getOrigin());
        $this->createStandardUpdateResponse($request, $response, $new);
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

    /**
     * Api method to create Note
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
    public function apiNoteCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateNoteCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        /** @var \Modules\Support\Models\Ticket $ticket */
        $ticket = TicketMapper::get()->where('id', (int) $request->getData('ref'))->execute();

        $request->setData('virtualpath', $this->createTicketDir($ticket), true);
        $this->app->moduleManager->get('Editor', 'Api')->apiEditorCreate($request, $response, $data);

        if ($response->header->status !== RequestStatusCode::R_200) {
            return;
        }

        /** @var \Modules\Editor\Models\EditorDoc $model */
        $model = $response->getDataArray($request->uri->__toString())['response'] ?? null;
        if ($model === null) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidUpdateResponse($request, $response, $model);
        }

        $this->createModelRelation($request->header->account, $request->getDataInt('ref'), $model->id, TicketMapper::class, 'notes', '', $request->getOrigin());
    }

    /**
     * Create media directory path
     *
     * @param Ticket $ticket Ticket
     *
     * @return string
     *
     * @since 1.0.0
     */
    private function createTicketDir(Ticket $ticket) : string
    {
        return '/Modules/Support/Ticket/'
            . $this->app->unitId . '/'
            . $ticket->task->createdAt->format('Y/m/d') . '/'
            . $ticket->id;
    }

    /**
     * Validate ticket note create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateNoteCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['ref'] = !$request->hasData('ref'))) {
            return $val;
        }

        return [];
    }
}
