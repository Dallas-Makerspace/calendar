<div class="categories index">
    <h2>
        Categories
        <?= $this->Html->link('Add Category',
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
            <?php foreach ($categories as $category): ?>
            <tr>
                <td><?= h($category->name) ?></td>
                <td>
                    <?= $this->Time->format(
                        $category->created,
                        'MM/dd/yy h:mm a',
                        null,
                        'America/Chicago'
                    ) ?>
                </td>
                <td>
                    <?= $this->Time->format(
                        $category->modified,
                        'MM/dd/yy h:mm a',
                        null,
                        'America/Chicago'
                    ) ?>
                </td>
                <td class="actions">
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $category->id]) ?> -
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $category->id], ['confirm' => __('Are you sure you want to delete {0}?', $category->name)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
