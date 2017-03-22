<div class="contacts index">
    <h2>
        Contacts
        <?= $this->Html->link('Add Contact',
            ['action' => 'add'],
            ['class' => 'btn btn-success pull-right']
        ) ?>
    </h2>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Username</th>
                <th>W9 On File</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($contacts as $contact): ?>
            <tr>
                <?php if (empty($contact->ad_username)): ?>
                    <td><?= $contact->name ?></td>
                <?php else: ?>
                    <td><?= $this->Html->link($contact->name, ['action' => 'view', $contact->ad_username]) ?></td>
                <?php endif; ?>
                <td><?= h($contact->email) ?></td>
                <td><?= h($contact->phone) ?></td>
                <td><?= h($contact->ad_username) ?></td>
                <td><?= ($contact->w9_on_file ? 'Yes' : 'No') ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $contact->id]) ?>
                    <?php if (empty($contact->ad_username)): ?>
                      - <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $contact->id], ['confirm' => __('Are you sure you want to delete {0}?', $contact->name)]) ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
