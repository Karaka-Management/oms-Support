<?php declare(strict_types=1);

use Modules\Support\Controller\ApiController;
use Modules\Support\Models\PermissionState;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/task(\?.*|$)' => [
        [
            'dest'       => '\Modules\Support\Controller\ApiController:apiTaskCreate',
            'verb'       => RouteVerb::PUT,
            'permission' => [
                'module' => ApiController::MODULE_NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionState::TICKET,
            ],
        ],
        [
            'dest'       => '\Modules\Support\Controller\ApiController:apiTaskSet',
            'verb'       => RouteVerb::SET,
            'permission' => [
                'module' => ApiController::MODULE_NAME,
                'type'   => PermissionType::MODIFY,
                'state'  => PermissionState::TICKET,
            ],
        ],
        [
            'dest'       => '\Modules\Support\Controller\ApiController:apiTaskGet',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => ApiController::MODULE_NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::TICKET,
            ],
        ],
    ],
    '^.*/task/element.*$' => [
        [
            'dest'       => '\Modules\Support\Controller\ApiController:apiTaskElementCreate',
            'verb'       => RouteVerb::PUT,
            'permission' => [
                'module' => ApiController::MODULE_NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionState::ELEMENT,
            ],
        ],
        [
            'dest'       => '\Modules\Support\Controller\ApiController:apiTaskElementSet',
            'verb'       => RouteVerb::SET,
            'permission' => [
                'module' => ApiController::MODULE_NAME,
                'type'   => PermissionType::MODIFY,
                'state'  => PermissionState::ELEMENT,
            ],
        ],
        [
            'dest'       => '\Modules\Support\Controller\ApiController:apiTaskElementGet',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => ApiController::MODULE_NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::TICKET,
            ],
        ],
    ],
];
