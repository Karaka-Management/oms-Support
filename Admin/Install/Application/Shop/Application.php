<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Web\{APPNAME}
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Web\{APPNAME};

use Model\CoreSettings;
use Modules\Admin\Models\AccountMapper;
use Modules\Admin\Models\LocalizationMapper;
use Modules\Admin\Models\NullAccount;
use phpOMS\Account\Account;
use phpOMS\Account\AccountManager;
use phpOMS\Asset\AssetType;
use phpOMS\Auth\Auth;
use phpOMS\DataStorage\Cache\CachePool;
use phpOMS\DataStorage\Cookie\CookieJar;
use phpOMS\DataStorage\Database\Connection\ConnectionAbstract;
use phpOMS\DataStorage\Database\DatabasePool;
use phpOMS\DataStorage\Database\DatabaseStatus;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;
use phpOMS\DataStorage\Session\HttpSession;
use phpOMS\Dispatcher\Dispatcher;
use phpOMS\Event\EventManager;
use phpOMS\Localization\L11nManager;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestMethod;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Model\Html\Head;
use phpOMS\Module\ModuleManager;
use phpOMS\Router\RouteVerb;
use phpOMS\Router\WebRouter;
use phpOMS\System\File\PathException;
use phpOMS\Uri\UriFactory;
use phpOMS\Views\View;
use Web\WebApplication;
use Web\{APPNAME}\ShopView;

/**
 * Application class.
 *
 * @package Web\{APPNAME}
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 * @codeCoverageIgnore
 */
final class Application
{
    /**
     * WebApplication.
     *
     * @var WebApplication
     * @since 1.0.0
     */
    private WebApplication $app;

    /**
     * Temp config.
     *
     * @var array{db:array{core:array{masters:array{select:array{db:string, host:string, port:int, login:string, password:string, database:string}}}}, log:array{file:array{path:string}}, app:array{path:string, default:array{id:string, app:string, org:int, lang:string}, domains:array}, page:array{root:string, https:bool}, language:string[]}
     * @since 1.0.0
     */
    private array $config;

    /**
     * Constructor.
     *
     * @param WebApplication                                                                                                                                                                                                                                                                                                                            $app    WebApplication
     * @param array{db:array{core:array{masters:array{select:array{db:string, host:string, port:int, login:string, password:string, database:string}}}}, log:array{file:array{path:string}}, app:array{path:string, default:array{id:string, app:string, org:int, lang:string}, domains:array}, page:array{root:string, https:bool}, language:string[]} $config Application config
     *
     * @since 1.0.0
     */
    public function __construct(WebApplication $app, array $config)
    {
        $this->app          = $app;
        $this->app->appName = '{APPNAME}';
        $this->config       = $config;
        UriFactory::setQuery('/app', \strtolower($this->app->appName));
    }

    /**
     * Rendering app.
     *
     * @param HttpRequest  $request  Request
     * @param HttpResponse $response Response
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function run(HttpRequest $request, HttpResponse $response) : void
    {
        $this->app->l11nManager    = new L11nManager();
        $this->app->dbPool         = new DatabasePool();
        $this->app->sessionManager = new HttpSession(36000);
        $this->app->cookieJar      = new CookieJar();
        $this->app->moduleManager  = new ModuleManager($this->app, __DIR__ . '/../../Modules');
        $this->app->dispatcher     = new Dispatcher($this->app);

        $this->app->dbPool->create('select', $this->config['db']['core']['masters']['select']);

        $this->app->router = new WebRouter();
        $this->app->router->importFromFile(__DIR__ . '/Routes.php');
        $this->app->router->add(
            '/{APPNAME}/e403',
            function() use ($request, $response) {
                $view = new View($this->app->l11nManager, $request, $response);
                $view->setTemplate('/Web/{APPNAME}/Error/403_inline');
                $response->header->status = RequestStatusCode::R_403;


                return $view;
            },
            RouteVerb::GET
        );

        /* CSRF token OK? */
        if ($request->hasData('CSRF')
            && !\hash_equals($this->app->sessionManager->data['CSRF'] ?? '', $request->getDataString('CSRF'))
        ) {
            $response->header->status = RequestStatusCode::R_403;

            return;
        }

        /** @var ConnectionAbstract $con */
        $con = $this->app->dbPool->get();
        DataMapperFactory::db($con);

        $this->app->cachePool      = new CachePool();
        $this->app->appSettings    = new CoreSettings();
        $this->app->eventManager   = new EventManager($this->app->dispatcher);
        $this->app->accountManager = new AccountManager($this->app->sessionManager);
        $this->app->l11nServer     = LocalizationMapper::get()->where('id', 1)->execute();
        $this->app->unitId          = $this->getApplicationOrganization($request, $this->config['app']);

        $aid                       = Auth::authenticate($this->app->sessionManager);
        $request->header->account  = $aid;
        $response->header->account = $aid;

        $account = $this->loadAccount($request);

        if ($account->id > 0) {
            $response->header->l11n = $account->l11n;
        } elseif (isset($this->app->sessionManager->data['language'])
            && $response->header->l11n->language !== $this->app->sessionManager->data['language']
        ) {
            $response->header->l11n
                ->loadFromLanguage(
                    $this->app->sessionManager->data['language'],
                    $this->app->sessionManager->data['country'] ?? '*'
                );
        } else {
            $this->app->setResponseLanguage($request, $response, $this->config);
        }

        if (!\in_array($response->header->l11n->language, $this->config['language'])) {
            $response->header->l11n->language = $this->app->l11nServer->language;
        }

        $pageView = new ShopView($this->app->l11nManager, $request, $response);
        $head     = new Head();

        $pageView->setData('unitId', $this->app->unitId);
        $pageView->head = $head;
        $response->set('Content', $pageView);

        /* App only allows GET */
        if ($request->getMethod() !== RequestMethod::GET) {
            $this->create406Response($response, $pageView);

            return;
        }

        /* Database OK? */
        if ($this->app->dbPool->get()->getStatus() !== DatabaseStatus::OK) {
            $this->create503Response($response, $pageView);

            return;
        }

        UriFactory::setQuery('/lang', $response->header->l11n->language);

        $this->loadLanguageFromPath(
            $response->header->l11n->language,
            __DIR__ . '/lang/' . $response->header->l11n->language . '.lang.php'
        );

        $response->header->set('content-language', $response->header->l11n->language, true);

        /* Create html head */
        $this->initResponseHead($head, $request, $response);

        $this->app->moduleManager->initRequestModules($request);
        $this->createDefaultPageView($request, $response, $pageView);

        $dispatched = $this->app->dispatcher->dispatch(
            $this->app->router->route(
                $request->uri->getRoute(),
                $request->getDataString('CSRF'),
                $request->getRouteVerb(),
                $this->app->appId,
                $this->app->unitId,
                $account,
                $request->getData()
            ),
            $request,
            $response
        );
        $pageView->addData('dispatch', $dispatched);
    }

    /**
     * Get application organization
     *
     * @param HttpRequest $request Client request
     * @param array       $config  App config
     *
     * @return int Organization id
     *
     * @since 1.0.0
     */
    private function getApplicationOrganization(HttpRequest $request, array $config) : int
    {
        return (int) (
            $request->getData('u') ?? (
                $config['domains'][$request->uri->host]['org'] ?? $config['default']['org']
            )
        );
    }

    /**
     * Create 406 response.
     *
     * @param HttpResponse $response Response
     * @param View         $pageView View
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function create406Response(HttpResponse $response, View $pageView) : void
    {
        $response->header->status = RequestStatusCode::R_406;
        $pageView->setTemplate('/Web/{APPNAME}/Error/406');
        $this->loadLanguageFromPath(
            $response->header->l11n->language,
            __DIR__ . '/Error/lang/' . $response->header->l11n->language . '.lang.php'
        );
    }

    /**
     * Create 406 response.
     *
     * @param HttpResponse $response Response
     * @param View         $pageView View
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function create503Response(HttpResponse $response, View $pageView) : void
    {
        $response->header->status = RequestStatusCode::R_503;
        $pageView->setTemplate('/Web/{APPNAME}/Error/503');
        $this->loadLanguageFromPath(
            $response->header->l11n->language,
            __DIR__ . '/Error/lang/' . $response->header->l11n->language . '.lang.php'
        );
    }

    /**
     * Load theme language from path
     *
     * @param string $language Language name
     * @param string $path     Language path
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function loadLanguageFromPath(string $language, string $path) : void
    {
        /* Load theme language */
        if (($absPath = \realpath($path)) === false) {
            throw new PathException($path);
        }

        /** @noinspection PhpIncludeInspection */
        $themeLanguage = include $absPath;
        $this->app->l11nManager->loadLanguage($language, '0', $themeLanguage);
    }

    /**
     * Load permission
     *
     * @param HttpRequest $request Current request
     *
     * @return Account
     *
     * @since 1.0.0
     */
    private function loadAccount(HttpRequest $request) : Account
    {
        $account = AccountMapper::getWithPermissions($request->header->account);
        $this->app->accountManager->add($account);

        return $account;
    }

    /**
     * Create 406 response.
     *
     * @param HttpResponse $response Response
     * @param View         $pageView View
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function create403Response(HttpResponse $response, View $pageView) : void
    {
        $response->header->status = RequestStatusCode::R_403;
        $pageView->setTemplate('/Web/{APPNAME}/Error/403');
        $this->loadLanguageFromPath(
            $response->header->l11n->language,
            __DIR__ . '/Error/lang/' . $response->header->l11n->language . '.lang.php'
        );
    }

    /**
     * Initialize response head
     *
     * @param Head         $head     Head to fill
     * @param HttpRequest  $request  Request
     * @param HttpResponse $response Response
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function initResponseHead(Head $head, HttpRequest $request, HttpResponse $response) : void
    {
        /* Load assets */
        $head->addAsset(AssetType::CSS, '../Resources/fonts/fontawesome/css/font-awesome.min.css');
        $head->addAsset(AssetType::CSS, '../Resources/fonts/Roboto/roboto.css');
        $head->addAsset(AssetType::CSS, '../Web/{APPNAME}/css/shop.css?v=1.0.0');

        // Framework
        $head->addAsset(AssetType::JS, '../jsOMS/Utils/oLib.js?v=1.0.0');
        $head->addAsset(AssetType::JS, '../jsOMS/UnhandledException.js?v=1.0.0');
        $head->addAsset(AssetType::JS, '../Web/{APPNAME}/js/shop.js?v=1.0.0', ['type' => 'module']);

        $script = '';
        $response->header->set(
            'content-security-policy',
            'base-uri \'self\'; script-src \'self\' blob: \'sha256-'
            . \base64_encode(\hash('sha256', $script, true))
            . '\'; worker-src \'self\'',
            true
        );

        if ($request->hasData('debug')) {
            $head->addAsset(AssetType::CSS, 'cssOMS/debug.css?v=1.0.0');
        }

        $css = \file_get_contents(__DIR__ . '/css/shop-small.css');
        if ($css === false) {
            $css = '';
        }

        $css = \preg_replace('!\s+!', ' ', $css);
        $head->setStyle('core', $css ?? '');
        $head->title = 'Demo Shop';
    }

    /**
     * Create default page view
     *
     * @param HttpRequest  $request  Request
     * @param HttpResponse $response Response
     * @param ShopView     $pageView View
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function createDefaultPageView(HttpRequest $request, HttpResponse $response, ShopView $pageView) : void
    {
        $pageView->setTemplate('/Web/{APPNAME}/index');
    }
}
