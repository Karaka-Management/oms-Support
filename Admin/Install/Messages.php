<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\Support\Admin\Install
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Support\Admin\Install;

use Modules\Support\Models\SettingsEnum;
use phpOMS\Application\ApplicationAbstract;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;

/**
 * Media class.
 *
 * @package Modules\Support\Admin\Install
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class Messages
{
    /**
     * Install media providing
     *
     * @param ApplicationAbstract $app  Application
     * @param string              $path Module path
     *
     * @return void
     *
     * @since 1.0.0
     */
    public static function install(ApplicationAbstract $app, string $path) : void
    {
        $messages = \Modules\Messages\Admin\Installer::installExternal($app, ['path' => __DIR__ . '/Messages.install.json']);

        /** @var \Modules\Admin\Controller\ApiController $module */
        $module = $app->moduleManager->get('Admin');

        $settings = [
            [
                'id'      => null,
                'name'    => SettingsEnum::SUPPORT_EMAIL_TEMPLATE,
                'content' => (string) $messages['email_template'][0]['id'],
                'module'  => 'Support',
            ],
        ];

        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('settings', \json_encode($settings));

        $module->apiSettingsSet($request, $response);
    }
}
