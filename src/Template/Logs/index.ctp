<div class="pending index">
    <h2>Logs</h2>
    <form action="/logs" method="get">
        <fieldset>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group text">
                        <label class="control-label" for="start-date">Start Date</label>
                        <input type="date" name="start_date"  id="start-date" class="form-control">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group text">
                        <label class="control-label" for="end-date">End Date</label>
                        <input type="date" name="end_date" id="end-date" class="form-control">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group text">
                        <label class="control-label" for="end-date">Search For Username</label>
                        <input type="text" value="" name="user_name"  id="user-name" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group text">
                        <label class="control-label" for="end-date">Search For String</label>
                        <input type="text" name="search_string" id="search-string" class="form-control">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-default">Narrow Results</button>
        </fieldset>
    </form>
    <br><br>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Date/Time</th>
                <th>User</th>
                <th>Description</th>
                <th>URL</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
		<tr>
			<td><?= $log->date_time; ?></td>
			<td><?= $log->user; ?></td>
			<td><?= $log->description; ?></td>
			<td><?= $log->url; ?></td>
			<td><?= $log->ip_address; ?></td>
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
