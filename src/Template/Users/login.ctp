<?php $this->assign('title', 'Log In'); ?>

<div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3">
  <section class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title"><?= __('DMS Member Log In') ?></h3>
    </div>
    <div class="panel-body">
      <?= $this->Flash->render() ?>
      <?= $this->Form->create() ?>
        <fieldset>
          <?= $this->Form->input('username', ['required' => true, 'autofocus' => 'autofocus']) ?>
          <?= $this->Form->input('password', ['required' => true]) ?>
        </fieldset>
        <?= $this->Form->button(__('Login')); ?>
      <?= $this->Form->end() ?>
      <p class="small" style="margin-top:15px">Forgot your password? Primary account holders can <?= $this->Html->link(__('change passwords in WHMCS'), 'https://accounts.dallasmakerspace.org/accounts/pwreset.php') ?>. Family members must have their primary account holder or an admin reset their password from <?= $this->Html->link(__('Maker Manager'), 'https://accounts.dallasmakerspace.org/makermanager') ?>.</p>
    </div>
  </section>
</div>
