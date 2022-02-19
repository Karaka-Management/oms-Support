<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   Modules\Admin\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

use phpOMS\Uri\UriFactory;

$settings     = $this->getData('settings') ?? [];
$applications = $this->getData('applications') ?? [];

echo $this->getData('nav')->render(); ?>

<div id="iSettings" class="tabview tab-2 url-rewrite">
    <div class="box wf-100 col-xs-12">
        <ul class="tab-links">
            <li><label for="c-tab-1"><?= $this->getHtml('Applications'); ?></label></li>
            <li><label for="c-tab-2"><?= $this->getHtml('List'); ?></label></li>
        </ul>
    </div>
    <div class="tab-content">
        <input type="radio" id="c-tab-1" name="tabular-2"<?= $this->request->uri->fragment === 'c-tab-1' ? ' checked' : ''; ?>>
        <div class="tab">
            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <div class="portlet">
                        <form id="iGeneralSettings" action="<?= UriFactory::build('{/api}admin/settings/module?id={?id}'); ?>" method="post">
                            <div class="portlet-head"><?= $this->getHtml('Settings'); ?></div>
                            <div class="portlet-body">
                                <div class="form-group">
                                    <label for="iOname"><?= $this->getHtml('Application'); ?></label>
                                    <select id="iOname" name="settings_1000000009">

                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="iLogPath"><?= $this->getHtml('Email'); ?></label>
                                    <input id="iLogPath" name="settings_1000000007" type="email" value="" placeholder="">
                                </div>

                                <div class="form-group">
                                    <label for="iLogPath"><?= $this->getHtml('MailServer'); ?></label>
                                    <input id="iLogPath" name="settings_1000000007" type="email" value="" placeholder="">
                                </div>

                                <div class="form-group">
                                    <label for="iLogPath"><?= $this->getHtml('MailPort'); ?></label>
                                    <input id="iLogPath" name="settings_1000000007" type="email" value="" placeholder="">
                                </div>

                                <div class="form-group">
                                    <label for="iLogPath"><?= $this->getHtml('MailUser'); ?></label>
                                    <input id="iLogPath" name="settings_1000000007" type="email" value="" placeholder="">
                                </div>

                                <div class="form-group">
                                    <label for="iLogPath"><?= $this->getHtml('MailPassword'); ?></label>
                                    <input id="iLogPath" name="settings_1000000007" type="email" value="" placeholder="">
                                </div>

                                <div class="form-group">
                                    <label for="iLogPath"><?= $this->getHtml('MailEncryption'); ?></label>
                                    <input id="iLogPath" name="settings_1000000007" type="email" value="" placeholder="">
                                </div>
                            </div>
                            <div class="portlet-foot">
                                <input id="iSubmitGeneral" name="submitGeneral" type="submit" value="<?= $this->getHtml('Save', '0', '0'); ?>">
                                <input id="iSubmitGeneral" name="submitGeneral" type="submit" value="<?= $this->getHtml('Create', '0', '0'); ?>">
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-xs-12 col-md-6">
                    <div class="portlet">
                        <div class="portlet-head"><?= $this->getHtml('Applications'); ?><i class="fa fa-download floatRight download btn"></i></div>
                        <div class="slider">
                            <table id="settingsList" class="default sticky">
                                <thead>
                                <tr>
                                    <td>
                                    <td><?= $this->getHtml('ID', '0', '0'); ?>
                                        <label for="settingsList-sort-1">
                                            <input type="radio" name="settingsList-sort" id="settingsList-sort-1">
                                            <i class="sort-asc fa fa-chevron-up"></i>
                                        </label>
                                        <label for="settingsList-sort-2">
                                            <input type="radio" name="settingsList-sort" id="settingsList-sort-2">
                                            <i class="sort-desc fa fa-chevron-down"></i>
                                        </label>
                                        <label>
                                            <i class="filter fa fa-filter"></i>
                                        </label>
                                    <td class="wf-100"><?= $this->getHtml('Name'); ?>
                                        <label for="settingsList-sort-3">
                                            <input type="radio" name="settingsList-sort" id="settingsList-sort-3">
                                            <i class="sort-asc fa fa-chevron-up"></i>
                                        </label>
                                        <label for="settingsList-sort-4">
                                            <input type="radio" name="settingsList-sort" id="settingsList-sort-4">
                                            <i class="sort-desc fa fa-chevron-down"></i>
                                        </label>
                                        <label>
                                            <i class="filter fa fa-filter"></i>
                                        </label>
                                <tbody>
                                <?php $count = 0;
                                    foreach ($applications as $key => $application) : ++$count;
                                ?>
                                <tr tabindex="0">
                                    <td><i class="fa fa-cogs"></i>
                                    <td data-label="<?= $this->getHtml('ID', '0', '0'); ?>"><?= $application->getId(); ?>
                                    <td data-label="<?= $this->getHtml('Name'); ?>"><?= $this->printHtml($application->name); ?>
                                <?php endforeach; ?>
                                <?php if ($count === 0) : ?>
                                    <tr><td colspan="3" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <input type="radio" id="c-tab-2" name="tabular-2"<?= $this->request->uri->fragment === 'c-tab-2' ? ' checked' : ''; ?>>
        <div class="tab">
            <div class="row">
                <div class="col-xs-12">
                    <div class="portlet">
                        <div class="portlet-head"><?= $this->getHtml('Settings'); ?><i class="fa fa-download floatRight download btn"></i></div>
                        <div class="slider">
                        <table id="settingsList" class="default sticky">
                            <thead>
                            <tr>
                                <td>
                                <td><?= $this->getHtml('ID', '0', '0'); ?>
                                    <label for="settingsList-sort-1">
                                        <input type="radio" name="settingsList-sort" id="settingsList-sort-1">
                                        <i class="sort-asc fa fa-chevron-up"></i>
                                    </label>
                                    <label for="settingsList-sort-2">
                                        <input type="radio" name="settingsList-sort" id="settingsList-sort-2">
                                        <i class="sort-desc fa fa-chevron-down"></i>
                                    </label>
                                    <label>
                                        <i class="filter fa fa-filter"></i>
                                    </label>
                                <td><?= $this->getHtml('Name'); ?>
                                    <label for="settingsList-sort-3">
                                        <input type="radio" name="settingsList-sort" id="settingsList-sort-3">
                                        <i class="sort-asc fa fa-chevron-up"></i>
                                    </label>
                                    <label for="settingsList-sort-4">
                                        <input type="radio" name="settingsList-sort" id="settingsList-sort-4">
                                        <i class="sort-desc fa fa-chevron-down"></i>
                                    </label>
                                    <label>
                                        <i class="filter fa fa-filter"></i>
                                    </label>
                                <td class="wf-100"><?= $this->getHtml('Value'); ?>
                                <td><?= $this->getHtml('Group'); ?>
                                    <label for="settingsList-sort-7">
                                        <input type="radio" name="settingsList-sort" id="settingsList-sort-7">
                                        <i class="sort-asc fa fa-chevron-up"></i>
                                    </label>
                                    <label for="settingsList-sort-8">
                                        <input type="radio" name="settingsList-sort" id="settingsList-sort-8">
                                        <i class="sort-desc fa fa-chevron-down"></i>
                                    </label>
                                    <label>
                                        <i class="filter fa fa-filter"></i>
                                    </label>
                                <td><?= $this->getHtml('Account'); ?>
                                    <label for="settingsList-sort-9">
                                        <input type="radio" name="settingsList-sort" id="settingsList-sort-9">
                                        <i class="sort-asc fa fa-chevron-up"></i>
                                    </label>
                                    <label for="settingsList-sort-10">
                                        <input type="radio" name="settingsList-sort" id="settingsList-sort-10">
                                        <i class="sort-desc fa fa-chevron-down"></i>
                                    </label>
                                    <label>
                                        <i class="filter fa fa-filter"></i>
                                    </label>
                            <tbody>
                            <?php $count = 0;
                                foreach ($settings as $key => $setting) : ++$count;
                            ?>
                            <tr tabindex="0">
                                <td><i class="fa fa-cogs"></i>
                                <td data-label="<?= $this->getHtml('ID', '0', '0'); ?>"><?= $setting->getId(); ?>
                                <td data-label="<?= $this->getHtml('Name'); ?>"><?= $this->printHtml($setting->name); ?>
                                <td data-label="<?= $this->getHtml('Value'); ?>"><?= $this->printHtml($setting->content); ?>
                                <td data-label="<?= $this->getHtml('Group'); ?>"><?= $this->printHtml($setting->group); ?>
                                <td data-label="<?= $this->getHtml('Account'); ?>"><?= $this->printHtml($setting->account); ?>
                            <?php endforeach; ?>
                            <?php if ($count === 0) : ?>
                                <tr><td colspan="6" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
                            <?php endif; ?>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
