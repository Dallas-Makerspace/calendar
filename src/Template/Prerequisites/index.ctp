<div class="prerequisites index">
    <h2>
        Prerequisites
        <?= $this->Html->link('Add Prerequisite',
            ['action' => 'add'],
            ['class' => 'btn btn-success pull-right']
        ) ?>
    </h2>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Name</th>
                <th>AD Group</th>
                <th>Created</th>
                <th>Modified</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($prerequisites as $prerequisite): ?>
            <tr>
                <td><?= h($prerequisite->name) ?></td>
                <td><?= h($prerequisite->ad_group) ?></td>
                <td>
                    <?= $this->Time->format(
                        $prerequisite->created,
                        'MM/dd/yy h:mm a',
                        null,
                        'America/Chicago'
                    ) ?>
                </td>
                <td>
                    <?= $this->Time->format(
                        $prerequisite->modified,
                        'MM/dd/yy h:mm a',
                        null,
                        'America/Chicago'
                    ) ?>
                </td>
                <td class="actions">
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $prerequisite->id]) ?> -
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $prerequisite->id], ['confirm' => __('Are you sure you want to delete {0}?', $prerequisite->name)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
