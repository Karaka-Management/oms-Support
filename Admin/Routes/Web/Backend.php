<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

use Modules\Support\Controller\BackendController;
use Modules\Support\Models\PermissionState;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/admin/module/settings\?id=Support.*$' => [
        [
            'dest'       => '\Modules\Support\Controller\BackendController:viewModuleSettings',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => \Modules\Admin\Models\PermissionState::MODULE,
            ],
        ],
    ],
    '^.*/support/list.*$' => [
        [
            'dest'       => '\Modules\Support\Controller\BackendController:viewSupportList',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::TICKET,
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
                'state'  => PermissionState::TICKET,
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
                'state'  => PermissionState::TICKET,
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
                'state'  => PermissionState::ANALYSIS,
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
                'state'  => PermissionState::SETTINGS,
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
                'state'  => PermissionState::DASHBOARD,
            ],
        ],
    ],
];
