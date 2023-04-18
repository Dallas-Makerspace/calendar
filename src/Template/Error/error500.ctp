<?php

use Cake\Core\Configure;
use Cake\Error\Debugger;

$this->layout = 'error';

if (Configure::read('debug')) {
    $this->layout = 'dev_error';

    $this->assign('title', $message);
    $this->assign('templateName', 'error500.ctp');

    $this->start('file');

    if (!empty($error->queryString)) { ?>
        <p class="notice">
            <strong>SQL Query: </strong>
            <?= h($error->queryString) ?>
        </p>
        <?php
    }

    if (!empty($error->params)) {
        ?>
        <strong>SQL Query Params: </strong>
        <?php
        Debugger::dump($error->params);
    }

    if ($error instanceof Error) {
        ?>
        <strong>Error in: </strong>
        <?= sprintf('%s, line %s', str_replace(ROOT, 'ROOT', $error->getFile()), $error->getLine()) ?>
        <?php
    }

    echo $this->element('auto_table_warning');

    if (extension_loaded('xdebug')) {
        xdebug_print_function_stack();
    }

    $this->end();
}
?>
<h1><?= __d('cake', 'An Internal Error Has Occurred') ?></h1>
<br>
<h4>Please report this error to the DMS infrastructure team via one of the following:</h4>
<ul>
    <li>Talk: <a href="https://talk.dallasmakerspace.org/groups/team_infrastructure">@team_infrastructure</a></li>
    <li>Discord: <a href="https://ptb.discord.com/channels/862552330322182165/936328352724291605">#questions</a></li>
    <li>Email: <a href="mailto:infrastructure@dallasmakerspace.org">infrastructure@dallasmakerspace.org</a></li>
</ul>
<br>
<h4>It is helpful if you include as much of the following as you can:</h4>
<ul>
    <li>If you are a guest or member (logged in)</li>
    <li>When this happened/what you were trying to do</li>
    <li>What time this occurred at: <i style="user-select: all!important;"><?= date("M j, Y H:i:s e") ?></i></li>
    <li>Operating System and Browser</li>
    <li>Any other information you think is important</li>
</ul>
