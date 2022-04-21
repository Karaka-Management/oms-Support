<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Support
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Support\Controller;

use Model\NullSetting;
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
 * @license OMS License 1.0
 * @link    https://karaka.app
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
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewSettings(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewSupportList(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
    {
        $head = $response->get('Content')->getData('head');
        $head->addAsset(AssetType::CSS, 'Modules/Tasks/Theme/Backend/css/styles.css');

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Support/Theme/Backend/support-list');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1002901101, $request, $response));

        $mapperQuery = TicketMapper::getAll()
            ->with('task')
            ->with('task/createdBy')
            ->with('for')
            ->with('app')
            ->limit(25);

        if ($request->getData('ptype') === 'p') {
            $view->setData('tickets',
                $mapperQuery->where('id', (int) ($request->getData('id') ?? 0), '<')->execute()
            );
        } elseif ($request->getData('ptype') === 'n') {
            $view->setData('tickets',
                $mapperQuery->where('id', (int) ($request->getData('id') ?? 0), '>')->execute()
            );
        } else {
            $view->setData('tickets',
                $mapperQuery->where('id', 0, '>')->execute()
            );
        }

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewSupportTicket(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
    {
        $view = new TicketView($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/Support/Theme/Backend/support-ticket');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1002901101, $request, $response));

        $mapperQuery = TicketMapper::get()
            ->with('task')
            ->with('task/createdBy')
            ->with('ticketElements')
            ->with('ticketElements/taskElement')
            ->with('ticketElements/taskElement/createdBy')
            ->with('ticketElements/taskElement/media')
            ->with('attributes')
            ->with('for')
            ->with('app');

        /** @var \Modules\Support\Models\Ticket $ticket */
        $ticket = $request->getData('for') !== null
            ? $mapperQuery->where('task', (int) $request->getData('for'))->execute()
            : $mapperQuery->where('id', (int) $request->getData('id'))->execute();

        $view->addData('ticket', $ticket);

        $accGrpSelector = new \Modules\Profile\Theme\Backend\Components\AccountGroupSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->addData('accGrpSelector', $accGrpSelector);

        $editor = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($this->app->l11nManager, $request, $response);
        $view->addData('editor', $editor);

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewSupportCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Support/Theme/Backend/ticket-create');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1002901101, $request, $response));

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewSupportAnalysis(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Support/Theme/Backend/support-analysis');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1002901101, $request, $response));

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewSupportSettings(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Support/Theme/Backend/support-settings');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1002901101, $request, $response));

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewPrivateSupportDashboard(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Support/Theme/Backend/user-support-dashboard');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1002901101, $request, $response));

        return $view;
    }

    /**
     * Method which generates the module profile view.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface Response can be rendered
     *
     * @since 1.0.0
     */
    public function viewModuleSettings(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1000105001, $request, $response));

        $id = $request->getData('id') ?? '';

        $settings = SettingMapper::getAll()->where('module', $id)->execute();
        if (!($settings instanceof NullSetting)) {
            $view->setData('settings', !\is_array($settings) ? [$settings] : $settings);
        }

        /** @var \Modules\Support\Models\SupportApp[] $applications */
        $applications = SupportAppMapper::getAll()->execute();
        $view->setData('applications', $applications);

        if (\is_file(__DIR__ . '/../Admin/Settings/Theme/Backend/settings.tpl.php')) {
            $view->setTemplate('/Modules/' . static::NAME . '/Admin/Settings/Theme/Backend/settings');
        } else {
            $view->setTemplate('/Modules/Admin/Theme/Backend/modules-settings');
        }

        return $view;
    }
}
