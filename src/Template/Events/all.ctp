<div class="pending index">
    <h2>All Events Archive</h2>
    <p>This is a complete listing of every event which has been submitted to the calendar system, regardless of them being cancelled or rejected.</p>
    <form action="/events/all" method="get">
        <fieldset>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group text">
                        <label class="control-label" for="start-date">Start Date</label>
                        <input type="date" value="<?= date('Y-m-d', strtotime('-1 month')) ?>" name="start_date" required="required" id="start-date" class="form-control">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group text">
                        <label class="control-label" for="end-date">End Date</label>
                        <input type="date" value="<?= date('Y-m-d') ?>" name="end_date" required="required" id="end-date" class="form-control">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-default">Narrow Results</button>
        </fieldset>
    </form>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Short Description</th>
                <th>Event Start</th>
                <th>Created</th>
                <th>Created By</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($events as $event): ?>
                <tr>
                    <td><?= $this->Html->link(h($event->name), ['action' => 'view', $event->id]) ?></td>
                    <td><?= h($event->short_description) ?></td>
                    <td>
                        <?= $this->Time->format(
                            $event->event_start,
                            'MM/dd/yy h:mm a',
                            null,
                            'America/Chicago'
                        ) ?>
                    </td>
                    <td>
                        <?= $this->Time->format(
                            $event->created,
                            'MM/dd/yy h:mm a',
                            null,
                            'America/Chicago'
                        ) ?>
                    </td>
                    <td><?= h($event->created_by) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        </ul>
        <p>Page <?= $this->Paginator->counter() ?></p>
    </div>
</div>
