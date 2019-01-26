<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $this->fetch('title') ?> |
        <?= __('Dallas Makerspace Calendar') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css([
        'https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700',
        'https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css',
        'bootstrap/bootstrap.min.css',
        'eonasdan-bootstrap-datetimepicker/bootstrap-datetimepicker.min.css',
        '/datatables/datatables.min.css',
        'app.css'
    ]) ?>
    <?= $this->Html->script([
        'jquery/jquery.min.js',
        'bootstrap/bootstrap.min.js',
        'moment/moment.min.js',
        'eonasdan-bootstrap-datetimepicker/bootstrap-datetimepicker.min.js',
        '/datatables/datatables.min.js',
        'DataTables.cakephp.dataTables.js',
        'app.js'
    ]) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
</head>
<body<?= $this->request->getParam('action') == 'embed' ? ' class="embed"' : '' ?>>
    <?php if ($this->request->getParam('action') != 'embed'): ?>
        <?= $this->element('Header/default') ?>
    <?php endif; ?>
    <div class="container<?= $this->request->getParam('action') == 'embed' ? '-fluid' : '' ?>">
      <?= $this->fetch('content') ?>
    </div>
    <?php if ($this->request->getParam('action') != 'embed'): ?>
        <?= $this->element('Footer/default') ?>
    <?php endif; ?>
    <?= $this->fetch('script') ?>
</body>
</html>
