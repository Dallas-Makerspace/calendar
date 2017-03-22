<div class="export index">
    <h2>Export Honoraria</h2>
    <form action="/events/export-honoraria" method="get">
        <fieldset>
            <legend>Export Date Range</legend>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group text required">
                        <label class="control-label" for="start-date">Start Date</label>
                        <input type="date" value="<?= date('Y-m-d', strtotime('-1 month')) ?>" name="start_date" required="required" id="start-date" class="form-control">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group text required">
                        <label class="control-label" for="end-date">End Date</label>
                        <input type="date" value="<?= date('Y-m-d') ?>" name="end_date" required="required" id="end-date" class="form-control">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-default">List Honoraria</button>
        </fieldset>
    </form>
    
    <?php if ($honoraria): ?>
        <form method="post">
        <table class="table table-hover" style="margin-top: 30px;">
            <thead>
                <tr>
					<th>ID</th>
                    <th>Event Time</th>
                    <th>Event Name</th>
                    <th>Committee</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Attendees</th>
                    <th>Pay?</th>
                    <th>Paid</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($honoraria as $honorarium): ?>
                <tr>
					<td><?= $honorarium->id ?></td>
                    <td>
                        <?=
                            $this->Time->format(
                                $honorarium->event_start,
                                'E MMM d h:mma',
                                null,
                                'America/Chicago'
                            );
                        ?>
                    </td>
                    <td><?= h($honorarium->name) ?></td>
                    <td><?= h($honorarium->honorarium->committee->name) ?></td>
                    <td><?= h($honorarium->contact->name) ?></td>
                    <td><?= h($honorarium->contact->email) ?></td>
                    <td><?= h($honorarium->contact->phone) ?></td>
                    <?php if ($honorarium->event_start > $oldCutoff): ?>
                        <td><?= count($honorarium->registrations) ?></td>
                    <?php else: ?>
                        <td><?= count($honorarium->old_registrations) ?></td>
                    <?php endif; ?>
                    <td>
                        <?php if ($honorarium->event_start > $oldCutoff): ?>
                            <?php if (count($honorarium->registrations) > 2): ?>
                                <?= ($honorarium->honorarium->pay_contact ? 'Yes' : 'No') ?>
                            <?php else: ?>
                                Don't Pay
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if (count($honorarium->old_registrations) > 2): ?>
                                <?= ($honorarium->honorarium->pay_contact ? 'Yes' : 'No') ?>
                            <?php else: ?>
                                Don't Pay
                            <?php endif; ?>
                        <?php endif; ?>                        
                    </td>
                    <td>
                        <?php if ($honorarium->event_start > $oldCutoff && count($honorarium->registrations) > 2 && $honorarium->honorarium->pay_contact): ?>
							<select name="paid[<?= $honorarium->honorarium->id ?>]">
								<option value="0"<?php if ($honorarium->honorarium->paid == 0) { echo ' selected'; } ?>>Not Paid</option>
								<option value="1"<?php if ($honorarium->honorarium->paid == 1) { echo ' selected'; } ?>>Paid</option>
								<option value="2"<?php if ($honorarium->honorarium->paid == 2) { echo ' selected'; } ?>>Pending</option>
								<option value="3"<?php if ($honorarium->honorarium->paid == 3) { echo ' selected'; } ?>>Missing Info</option>
								<option value="4"<?php if ($honorarium->honorarium->paid == 4) { echo ' selected'; } ?>>Denied</option>
								<option value="5"<?php if ($honorarium->honorarium->paid == 5) { echo ' selected'; } ?>>Paid by Script</option>
							</select>
                        <?php elseif ($honorarium->event_start <= $oldCutoff && count($honorarium->old_registrations) > 2 && $honorarium->honorarium->pay_contact): ?>
							<select name="paid[<?= $honorarium->honorarium->id ?>]">
								<option value="0"<?php if ($honorarium->honorarium->paid == 0) { echo ' selected'; } ?>>Not Paid</option>
								<option value="1"<?php if ($honorarium->honorarium->paid == 1) { echo ' selected'; } ?>>Paid</option>
								<option value="2"<?php if ($honorarium->honorarium->paid == 2) { echo ' selected'; } ?>>Pending</option>
								<option value="3"<?php if ($honorarium->honorarium->paid == 3) { echo ' selected'; } ?>>Missing Info</option>
								<option value="4"<?php if ($honorarium->honorarium->paid == 4) { echo ' selected'; } ?>>Denied</option>
								<option value="5"<?php if ($honorarium->honorarium->paid == 5) { echo ' selected'; } ?>>Paid by Script</option>
							</select>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><button type="submit" class="btn btn-primary">Save</button></td>
                </tr>
            </tbody>
        </table>
        </form>
        <a href="/events/export-honoraria-csv?start_date=<?= $_GET['start_date'] ?>&amp;end_date=<?= $_GET['end_date'] ?>" class="btn btn-success" target="_blank">Export List as CSV</a>
    <?php endif; ?>
</div>