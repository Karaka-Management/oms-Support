<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\Support
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use phpOMS\Uri\UriFactory;

/**
 * @var \phpOMS\Views\View               $this
 * @var \Modules\Support\Models\Ticket[] $tickets
 */
$tickets = $this->data['tickets'];

echo $this->data['nav']->render(); ?>

<div class="row">
    <div class="col-xs-12 col-md-9">
        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Tickets'); ?><i class="g-icon download btn end-xs">download</i></div>
            <div class="slider">
            <table class="default sticky">
                <thead>
                    <td><?= $this->getHtml('Status'); ?>
                    <td><?= $this->getHtml('Priority'); ?>
                    <td class="full"><?= $this->getHtml('Title'); ?>
                    <td><?= $this->getHtml('Creator'); ?>
                    <td><?= $this->getHtml('Assigned'); ?>
                    <td><?= $this->getHtml('For'); ?>
                    <td><?= $this->getHtml('Created'); ?>
                <tbody>
                <?php
                    $c = 0;
                foreach ($tickets as $key => $ticket) : ++$c;
                    $url = UriFactory::build('{/base}/support/ticket?{?}&id=' . $ticket->id);
                ?>
                    <tr data-href="<?= $url; ?>">
                        <td><a href="<?= $url; ?>">
                            <span class="tag <?= $this->printHtml('task-status-' . $ticket->task->status); ?>">
                                <?= $this->getHtml('S' . $ticket->task->status, 'Tasks'); ?>
                            </span></a>
                        <td><a href="<?= $url; ?>"><?= $this->getHtml('P' . $ticket->task->priority, 'Tasks'); ?></a>
                        <td><a href="<?= $url; ?>"><?= $this->printHtml($ticket->task->title); ?></a>
                        <td><a class="content" href="<?= UriFactory::build('{/base}/profile/view?for=' . $ticket->task->createdBy->id); ?>"><?= $this->printHtml($ticket->task->createdBy->name1); ?> <?= $this->printHtml($ticket->task->createdBy->name2); ?></a>
                        <td><?php $responsibles = $ticket->task->getResponsible();
                            foreach ($responsibles as $responsible) : ?>
                            <a class="content" href="<?= UriFactory::build('{/base}/profile/view?for=' . $responsible->id); ?>">
                                <?= $this->printHtml($responsible->name1); ?> <?= $this->printHtml($responsible->name2); ?>
                            </a>
                            <?php endforeach; ?>
                        <td><a class="content"><?= $this->printHtml($ticket->task->for->name1); ?> <?= $this->printHtml($ticket->task->for->name2); ?>
                        <td><a href="<?= $url; ?>"><?= $this->printHtml($ticket->task->createdAt->format('Y-m-d H:i')); ?></a>
                <?php endforeach; if ($c == 0) : ?>
                    <tr><td colspan="7" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
                <?php endif; ?>
            </table>
            </div>
        </section>
    </div>

    <div class="col-xs-12 col-md-3">
        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Stats'); ?></div>
            <div class="portlet-body">
                <table class="list">
                    <tr><th><?= $this->getHtml('Unassigned'); ?><td><?= $this->data['stats']['unassigned']; ?>
                    <tr><th><?= $this->getHtml('Open'); ?><td><?= $this->data['stats']['open']; ?>
                    <tr><th><?= $this->getHtml('InProgress'); ?><td><?= $this->data['stats']['inprogress']; ?>
                    <tr><th><?= $this->getHtml('Closed'); ?><td><?= $this->data['stats']['closed']; ?>
                    <tr><th><?= $this->getHtml('Total'); ?><td><?= $this->data['stats']['total']; ?>
                </table>
            </div>
        </section>
    </div>
</div>