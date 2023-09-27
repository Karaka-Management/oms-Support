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

use Model\SettingMapper;
use Modules\Support\Models\SupportAppMapper;
use Modules\Support\Models\TicketMapper;
use Modules\Support\Views\TicketView;
use phpOMS\Asset\AssetType;
use phpOMS\Contract\RenderableInterface;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Views\View;

/**
 * Support controller class.
 *
 * @package Modules\Support
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 * @codeCoverageIgnore
 */
final class BackendController extends Controller
{
    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewSettings(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        return new View($this->app->l11nManager, $request, $response);
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewSupportList(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $head = $response->data['Content']->head;
        $head->addAsset(AssetType::CSS, 'Modules/Tasks/Theme/Backend/css/styles.css?v=1.0.0');

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Support/Theme/Backend/support-list');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1002901101, $request, $response);

        $mapperQuery = TicketMapper::getAll()
            ->with('task')
            ->with('task/createdBy')
            ->with('for')
            ->with('app')
            ->limit(25);

        if ($request->getData('ptype') === 'p') {
            $view->data['tickets'] = $mapperQuery->where('id', $request->getDataInt('id') ?? 0, '<')->execute();
        } elseif ($request->getData('ptype') === 'n') {
            $view->data['tickets'] = $mapperQuery->where('id', $request->getDataInt('id') ?? 0, '>')->execute();
        } else {
            $view->data['tickets'] = $mapperQuery->where('id', 0, '>')->execute();
        }

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewSupportTicket(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new TicketView($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/Support/Theme/Backend/support-ticket');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1002901101, $request, $response);

        $mapperQuery = TicketMapper::get()
            ->with('task')
            ->with('task/createdBy')
            ->with('task/tags')
            ->with('task/tags/title')
            ->with('ticketElements')
            ->with('ticketElements/taskElement')
            ->with('ticketElements/taskElement/createdBy')
            ->with('ticketElements/taskElement/media')
            ->with('attributes')
            ->with('for')
            ->with('app')
            ->where('task/tags/title/language', $request->header->l11n->language);

        /** @var \Modules\Support\Models\Ticket $ticket */
        $ticket = $request->hasData('for')
            ? $mapperQuery->where('task', (int) $request->getData('for'))->execute()
            : $mapperQuery->where('id', (int) $request->getData('id'))->execute();

        $view->data['ticket'] = $ticket;

        $accGrpSelector               = new \Modules\Profile\Theme\Backend\Components\AccountGroupSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->data['accGrpSelector'] = $accGrpSelector;

        $editor               = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($this->app->l11nManager, $request, $response);
        $view->data['editor'] = $editor;

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewSupportCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Support/Theme/Backend/ticket-create');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1002901101, $request, $response);

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewSupportAnalysis(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Support/Theme/Backend/support-analysis');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1002901101, $request, $response);

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewSupportSettings(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Support/Theme/Backend/support-settings');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1002901101, $request, $response);

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewPrivateSupportDashboard(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Support/Theme/Backend/user-support-dashboard');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1002901101, $request, $response);

        return $view;
    }

    /**
     * Method which generates the module profile view.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface Response can be rendered
     *
     * @since 1.0.0
     */
    public function viewModuleSettings(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view              = new View($this->app->l11nManager, $request, $response);
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1000105001, $request, $response);

        $id = $request->getDataString('id') ?? '';

        $settings               = SettingMapper::getAll()->where('module', $id)->execute();
        $view->data['settings'] = $settings;

        /** @var \Modules\Support\Models\SupportApp[] $applications */
        $applications               = SupportAppMapper::getAll()->execute();
        $view->data['applications'] = $applications;

        $view->setTemplate('/Modules/' . static::NAME . '/Admin/Settings/Theme/Backend/settings');

        return $view;
    }
}
