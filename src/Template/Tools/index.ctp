<div class="tools index">
    <h2>
        Tools
        <?= $this->Html->link('Add Tool',
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
            <?php foreach ($tools as $tool): ?>
            <tr>
                <td><?= h($tool->name) ?></td>
                <td>
                    <?= $this->Time->format(
                        $tool->created,
                        'MM/dd/yy h:mm a',
                        null,
                        'America/Chicago'
                    ) ?>
                </td>
                <td>
                    <?= $this->Time->format(
                        $tool->modified,
                        'MM/dd/yy h:mm a',
                        null,
                        'America/Chicago'
                    ) ?>
                </td>
                <td class="actions">
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $tool->id]) ?> -
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $tool->id], ['confirm' => __('Are you sure you want to delete {0}?', $tool->name)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
