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
 * @link      https://jingga.app
 */
declare(strict_types=1);

/**
 * @var \phpOMS\Views\View $this
 */
echo $this->getData('nav')->render(); ?>

<div class="row">
    <div class="col-xs-6">
        <section class="box wf-100">
            <header><h1><?= $this->getHtml('Ticket'); ?></h1></header>
            <div class="inner">
                <form action="<?= \phpOMS\Uri\UriFactory::build('{/api}helper/template'); ?>" method="post">
                    <table class="layout wf-100">
                        <tbody>
                        <tr><td><label for="iDepartment"><?= $this->getHtml('Department'); ?></label>
                        <tr><td><select id="iDepartment" name="department"></select>
                        <tr><td><label for="iTopic"><?= $this->getHtml('Topic'); ?></label>
                        <tr><td><select id="iTopic" name="topic"></select>
                        <tr><td><label for="iTitle"><?= $this->getHtml('Title'); ?></label>
                        <tr><td><input id="iDescription" name="name" type="text" required>
                        <tr><td><label for="iDescription"><?= $this->getHtml('Description'); ?></label>
                        <tr><td><textarea required></textarea>
                        <tr><td><label for="iFile"><?= $this->getHtml('Files'); ?></label>
                        <tr><td><input id="iFile" name="fileVisual" type="file" multiple><input id="iFileHidden" name="files" type="hidden">
                        <tr><td><input type="submit" value="<?= $this->getHtml('Create', '0', '0'); ?>" name="create-ticket">
                    </table>
                </form>
            </div>
        </section>
    </div>
</div>