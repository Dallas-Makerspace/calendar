<div class="contacts view">
    <?= $this->Flash->render() ?>
    <div class="page-header">
        <h1>
            <?= h($contact->name) ?>
        </h1>
    </div>
    
    <div class="row">
        <div class="col-sm-6">
            <h2>Attended Events</h2>
            <ul>
                <?php foreach ($attended as $registration): ?>
                    <li><?= $this->Html->link($registration->event->name, ['controller' => 'events', 'action' => 'view', $registration->event->id]) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="col-sm-6">
            <h2>Hosted Events</h2>
            <ul>
                <?php foreach ($hosted as $event): ?>
                    <li><?= $this->Html->link($event->name, ['controller' => 'events', 'action' => 'view', $event->id]) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>