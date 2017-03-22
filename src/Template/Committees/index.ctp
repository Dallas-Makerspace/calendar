<div class="categories index">
    <h2>
        Committees
        <?= $this->Html->link('Add Committee',
            ['action' => 'add'],
            ['class' => 'btn btn-success pull-right']
        ) ?>
    </h2>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Name</th>
                <th>Created</th>
                <th>Modified</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($committees as $committee): ?>
            <tr>
                <td><?= h($committee->name) ?></td>
                <td>
                    <?= $this->Time->format(
                        $committee->created,
                        'MM/dd/yy h:mm a',
                        null,
                        'America/Chicago'
                    ) ?>
                </td>
                <td>
                    <?= $this->Time->format(
                        $committee->modified,
                        'MM/dd/yy h:mm a',
                        null,
                        'America/Chicago'
                    ) ?>
                </td>
                <td class="actions">
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $committee->id]) ?> -
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $committee->id], ['confirm' => __('Are you sure you want to delete {0}?', $committee->name)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
