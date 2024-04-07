<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\Support\Controller\BackendController;
use Modules\Support\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^/support/list(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Support\Controller\BackendController:viewSupportList',
            'verb'       => RouteVerb::GET,
            'active' => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::TICKET,
            ],
        ],
    ],
    '^/support/ticket/view(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Support\Controller\BackendController:viewSupportTicket',
            'verb'       => RouteVerb::GET,
            'active' => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::TICKET,
            ],
        ],
    ],
    '^/support/create(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Support\Controller\BackendController:viewSupportCreate',
            'verb'       => RouteVerb::GET,
            'active' => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::TICKET,
            ],
        ],
    ],
    '^/support/analysis/dashboard(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Support\Controller\BackendController:viewSupportAnalysis',
            'verb'       => RouteVerb::GET,
            'active' => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::ANALYSIS,
            ],
        ],
    ],
    '^/support/settings(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Support\Controller\BackendController:viewSupportSettings',
            'verb'       => RouteVerb::GET,
            'active' => false,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::SETTINGS,
            ],
        ],
    ],
];
