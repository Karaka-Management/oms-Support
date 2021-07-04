<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Support
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

use phpOMS\Uri\UriFactory;

/**
 * @var \phpOMS\Views\View           $this
 * @var \Modules\Support\Models\Ticket[] $tickets
 */
$tickets = $this->getData('tickets');

echo $this->getData('nav')->render(); ?>

<div class="row">
    <div class="col-xs-12 col-md-9">
        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Tickets'); ?><i class="fa fa-download floatRight download btn"></i></div>
            <table class="default sticky">
                <thead>
                    <td><?= $this->getHtml('Status'); ?>
                    <td><?= $this->getHtml('Priority'); ?>
                    <td class="full"><?= $this->getHtml('Title'); ?>
                    <td><?= $this->getHtml('Creator'); ?>
                    <td><?= $this->getHtml('Assigned'); ?>
                    <td><?= $this->getHtml('For'); ?>
                    <td><?= $this->getHtml('Created'); ?>
                <tfoot>
                <tbody>
                <?php
                    $c = 0;
                foreach ($tickets as $key => $ticket) : ++$c;
                    $url = UriFactory::build('{/prefix}support/ticket?{?}&id=' . $ticket->getId());
                ?>
                    <tr data-href="<?= $url; ?>">
                        <td><a href="<?= $url; ?>"><span class="tag <?= $this->printHtml('task-status-' . $ticket->task->getStatus()); ?>"><?= $this->getHtml('S' . $ticket->task->getStatus(), 'Tasks'); ?></span></a>
                        <td><a href="<?= $url; ?>"><?= $this->getHtml('P' . $ticket->task->getPriority(), 'Tasks'); ?></a>
                        <td><a href="<?= $url; ?>"><?= $this->printHtml($ticket->task->title); ?></a>
                        <td><a class="content" href="<?= UriFactory::build('{/prefix}profile/single?for=' . $ticket->task->createdBy->getId()); ?>"><?= $this->printHtml($ticket->task->createdBy->name1); ?> <?= $this->printHtml($ticket->task->createdBy->name2); ?></a>
                        <td><a href="<?= $url; ?>"><?= $this->printHtml($ticket->task->createdBy->name1); ?> <?= $this->printHtml($ticket->task->createdBy->name2); ?></a>
                        <td><a class="content" href="<?= UriFactory::build('{/prefix}profile/single?for=' . $ticket->for->getId()); ?>"><?= $this->printHtml($ticket->for->name1); ?> <?= $this->printHtml($ticket->for->name2); ?></a>
                        <td><a href="<?= $url; ?>"><?= $this->printHtml($ticket->task->createdAt->format('Y-m-d H:i')); ?></a>
                <?php endforeach; if ($c == 0) : ?>
                    <tr><td colspan="6" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
                <?php endif; ?>
            </table>
        </section>
    </div>

    <div class="col-xs-12 col-md-3">
        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Settings'); ?></div>
            <div class="portlet-body">
                <form>
                    <table class="layout wf-100">
                        <tr><td><label for="iIntervarl"><?= $this->getHtml('Interval'); ?></label>
                        <tr><td><select id="iIntervarl" name="interval">
                                    <option><?= $this->getHtml('All'); ?>
                                    <option><?= $this->getHtml('Day'); ?>
                                    <option><?= $this->getHtml('Week'); ?>
                                    <option selected><?= $this->getHtml('Month'); ?>
                                    <option><?= $this->getHtml('Year'); ?>
                                </select>
                    </table>
                </form>
            </div>
        </section>

        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Settings'); ?></div>
            <div class="portlet-body">
                <table class="list">
                    <tr><th><?= $this->getHtml('All'); ?><td>0
                    <tr><th><?= $this->getHtml('Unassigned'); ?><td>0
                    <tr><th><?= $this->getHtml('Open'); ?><td>0
                    <tr><th><?= $this->getHtml('Unsolved'); ?><td>0
                    <tr><th><?= $this->getHtml('Closed'); ?><td>0
                    <tr><th><?= $this->getHtml('InTime'); ?><td>0
                </table>
            </div>
        </section>
    </div>
</div>