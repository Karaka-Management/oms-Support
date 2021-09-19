<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Support\Admin
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Support\Admin;

use Modules\Support\Models\SupportApp;
use Modules\Support\Models\SupportAppMapper;
use phpOMS\Module\InstallerAbstract;
use phpOMS\DataStorage\Database\DatabasePool;
use phpOMS\Module\ModuleInfo;
use phpOMS\Config\SettingsInterface;

/**
 * Installer class.
 *
 * @package Modules\Support\Admin
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
final class Installer extends InstallerAbstract
{
    /**
     * {@inheritdoc}
     */
    public static function install(DatabasePool $dbPool, ModuleInfo $info, SettingsInterface $cfgHandler) : void
    {
        parent::install($dbPool, $info, $cfgHandler);

        $app       = new SupportApp();
        $app->name = 'Backend';

        $id = SupportAppMapper::create($app);
    }
}
