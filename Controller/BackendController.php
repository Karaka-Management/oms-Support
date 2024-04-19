<?php
/**
 * Jingga
 *
 * PHP Version 8.2
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
use Modules\Media\Models\MediaMapper;
use Modules\Profile\Models\SettingsEnum as ProfileSettingsEnum;
use Modules\Support\Models\SupportAppMapper;
use Modules\Support\Models\TicketMapper;
use Modules\Support\Views\TicketView;
use Modules\Tasks\Models\AccountRelationMapper;
use Modules\Tasks\Models\TaskElementMapper;
use Modules\Tasks\Models\TaskMapper;
use Modules\Tasks\Models\TaskStatus;
use Modules\Tasks\Models\TaskType;
use phpOMS\Asset\AssetType;
use phpOMS\Contract\RenderableInterface;
use phpOMS\DataStorage\Database\Query\Builder;
use phpOMS\DataStorage\Database\Query\OrderType;
use phpOMS\Message\Http\RequestStatusCode;
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
     * Routing end-point for application behavior.
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
     * Routing end-point for application behavior.
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
        $head->addAsset(AssetType::CSS, 'Modules/Tasks/Theme/Backend/css/styles.css?v=' . self::VERSION);

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Support/Theme/Backend/support-list');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1002901101, $request, $response);

        // @todo Use ticket implementation "getAnyRelatedToUser($request->header->account)
        $view->data['tickets'] = TicketMapper::getAnyRelatedToUser($request->header->account)
            ->with('task')
            ->with('task/createdBy')
            ->with('task/for')
            ->with('task/taskElements')
            ->with('task/taskElements/accRelation')
            ->with('task/taskElements/accRelation/relation')
            ->with('app')
            ->sort('task/createdAt', OrderType::DESC)
            ->limit(50)
            ->paginate(
                'id',
                $request->getData('ptype'),
                $request->getDataInt('offset')
            )
            ->executeGetArray();

        $openQuery = new Builder($this->app->dbPool->get(), true);
        $openQuery->innerJoin(TaskMapper::TABLE, TaskMapper::TABLE . '_d2_task')
            ->on(TicketMapper::TABLE . '_d1.support_ticket_task', '=', TaskMapper::TABLE . '_d2_task.task_id')
            ->innerJoin(TaskElementMapper::TABLE)
                ->on(TaskMapper::TABLE . '_d2_task.' . TaskMapper::PRIMARYFIELD, '=', TaskElementMapper::TABLE . '.task_element_task')
            ->innerJoin(AccountRelationMapper::TABLE)
                ->on(TaskElementMapper::TABLE . '.' . TaskElementMapper::PRIMARYFIELD, '=', AccountRelationMapper::TABLE . '.task_account_task_element')
            ->andWhere(AccountRelationMapper::TABLE . '.task_account_account', '=', $request->header->account);

        $view->data['open'] = TicketMapper::getAll()
            ->with('task')
            ->with('task/createdBy')
            ->where('task/type', TaskType::TEMPLATE, '!=')
            ->where('task/status', TaskStatus::OPEN)
            ->sort('task/createdAt', OrderType::DESC)
            ->query($openQuery)
            ->executeGetArray();

        $view->data['stats'] = TicketMapper::getStatOverview();

        return $view;
    }

    /**
     * Routing end-point for application behavior.
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

        $mapperQuery = TicketMapper::get()
            ->with('task')
            ->with('task/createdBy')
            ->with('task/tags')
            ->with('task/tags/title')
            ->with('task/taskElements')
            ->with('task/taskElements/createdBy')
            ->with('task/taskElements/media')
            ->with('task/attributes')
            ->with('task/for')
            ->with('app')
            ->where('task/tags/title/language', $request->header->l11n->language);

        $view->data['ticket'] = $request->hasData('for')
            ? $mapperQuery->where('task', (int) $request->getData('for'))->execute()
            : $mapperQuery->where('id', (int) $request->getData('id'))->execute();

        if ($view->data['ticket']->id === 0) {
            $response->header->status = RequestStatusCode::R_404;
            $view->setTemplate('/Web/Backend/Error/404');

            return $view;
        }

        $view->setTemplate('/Modules/Support/Theme/Backend/support-ticket');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1002901101, $request, $response);

        /** @var \Model\Setting $profileImage */
        $profileImage = $this->app->appSettings->get(names: ProfileSettingsEnum::DEFAULT_PROFILE_IMAGE, module: 'Profile');

        /** @var \Modules\Media\Models\Media $image */
        $image                     = MediaMapper::get()->where('id', (int) $profileImage->content)->execute();
        $view->defaultProfileImage = $image;

        $accGrpSelector               = new \Modules\Profile\Theme\Backend\Components\AccountGroupSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->data['accGrpSelector'] = $accGrpSelector;

        $editor               = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($this->app->l11nManager, $request, $response);
        $view->data['editor'] = $editor;

        $view->data['tickets'] = TicketMapper::getAll()
            ->with('task')
            ->where('task/for', $view->data['ticket']->task->for?->id)
            ->sort('createdAt', OrderType::DESC)
            ->offset(1)
            ->limit(5)
            ->executeGetArray();

        $dt = new \DateTime();

        $view->data['hasContractManagement'] = $this->app->moduleManager->isActive('ContractManagement');
        if ($view->data['hasContractManagement']) {
            $view->data['contracts'] = \Modules\ContractManagement\Models\ContractMapper::getAll()
                ->where('account', $view->data['ticket']->task->for?->id)
                ->where('end', $dt, '>=') // @todo consider to also allow $end === null
                ->sort('createdAt', OrderType::DESC)
                ->limit(5)
                ->executeGetArray();
        } else {
            $view->data['contracts'] = [];
        }

        return $view;
    }

    /**
     * Routing end-point for application behavior.
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
     * Routing end-point for application behavior.
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

        $settings               = SettingMapper::getAll()->where('module', $id)->executeGetArray();
        $view->data['settings'] = $settings;

        /** @var \Modules\Support\Models\SupportApp[] $applications */
        $applications               = SupportAppMapper::getAll()->executeGetArray();
        $view->data['applications'] = $applications;

        $view->setTemplate('/Modules/' . static::NAME . '/Admin/Settings/Theme/Backend/settings');

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
    public function viewSupportAnalysis(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        return new View($this->app->l11nManager, $request, $response);
    }
}
