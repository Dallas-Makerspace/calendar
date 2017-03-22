<div class="configurations index">
    <h2>Configuration</h2>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Name</th>
                <th>Value</th>
                <th>Modified</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($configurations as $configuration): ?>
            <tr>
                <td><?= h($configuration->name) ?></td>
                <td><?= h($configuration->value) ?> Days</td>
                <td>
                    <?= $this->Time->format(
                        $configuration->modified,
                        'MM/dd/yy h:mm a',
                        null,
                        'America/Chicago'
                    ) ?>
                </td>
                <td class="actions">
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $configuration->id]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
