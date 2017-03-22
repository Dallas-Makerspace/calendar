<div class="rooms index">
    <h2>
        Rooms
        <?= $this->Html->link('Add Room',
            ['action' => 'add'],
            ['class' => 'btn btn-success pull-right']
        ) ?>
    </h2>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Name</th>
                <th>Exclusive Use</th>
                <th>Created</th>
                <th>Modified</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rooms as $room): ?>
            <tr>
                <td><?= h($room->name) ?></td>
                <td><?= $room->exclusive == true ? 'Yes' : 'No' ?></td>
                <td>
                    <?= $this->Time->format(
                        $room->created,
                        'MM/dd/yy h:mm a',
                        null,
                        'America/Chicago'
                    ) ?>
                </td>
                <td>
                    <?= $this->Time->format(
                        $room->modified,
                        'MM/dd/yy h:mm a',
                        null,
                        'America/Chicago'
                    ) ?>
                </td>
                <td class="actions">
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $room->id]) ?> -
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $room->id], ['confirm' => __('Are you sure you want to delete {0}?', $room->name)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
