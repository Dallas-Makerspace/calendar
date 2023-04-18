<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->fetch('title') ?> |<?= __('Dallas Makerspace Calendar') ?></title>
    <?php
    echo $this->Html->charset();
    echo $this->Html->meta('icon');
    echo $this->Html->css([
        'https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700',
        'https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css',
        'bootstrap/bootstrap.min.css',
        'eonasdan-bootstrap-datetimepicker/bootstrap-datetimepicker.min.css',
        'app.css'
    ]);
    echo $this->Html->script([
        'jquery/jquery.min.js',
        'bootstrap/bootstrap.min.js',
        'moment/moment.min.js',
        'eonasdan-bootstrap-datetimepicker/bootstrap-datetimepicker.min.js',
        'app.js'
    ]);

    echo $this->fetch('meta');
    echo $this->fetch('css');
    ?>
</head>
<body<?= $this->request->getParam('action') == 'embed' ? ' class="embed"' : '' ?>>

<?php
if ($this->request->getParam('action') != 'embed') {
    echo $this->element('Header/default');
}
?>

<div class="container<?= $this->request->getParam('action') == 'embed' ? '-fluid' : '' ?>">
    <?= $this->fetch('content') ?>
</div>

<?php
if ($this->request->getParam('action') != 'embed') {
    echo $this->element('Footer/default');
}

echo $this->fetch('script');
?>

</body>
</html>
