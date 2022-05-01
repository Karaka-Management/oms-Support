<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

use Modules\Support\Controller\BackendController;
use Modules\Support\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/support/list.*$' => [
        [
            'dest'       => '\Modules\Support\Controller\BackendController:viewSupportList',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::TICKET,
            ],
        ],
    ],
    '^.*/support/ticket.*$' => [
        [
            'dest'       => '\Modules\Support\Controller\BackendController:viewSupportTicket',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::TICKET,
            ],
        ],
    ],
    '^.*/support/create.*$' => [
        [
            'dest'       => '\Modules\Support\Controller\BackendController:viewSupportCreate',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::TICKET,
            ],
        ],
    ],
    '^.*/support/analysis.*$' => [
        [
            'dest'       => '\Modules\Support\Controller\BackendController:viewSupportAnalysis',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::ANALYSIS,
            ],
        ],
    ],
    '^.*/support/settings.*$' => [
        [
            'dest'       => '\Modules\Support\Controller\BackendController:viewSupportSettings',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::SETTINGS,
            ],
        ],
    ],
    '^.*/private/support/dashboard.*$' => [
        [
            'dest'       => '\Modules\Support\Controller\BackendController:viewPrivateSupportDashboard',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::DASHBOARD,
            ],
        ],
    ],
];
