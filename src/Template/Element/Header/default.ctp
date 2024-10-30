<nav class="navbar navbar-inverse navbar-fixed-top"<?php if ($isDevelopment) echo " style='background: red'";?>>
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <?= $this->Html->link('Dallas Makerspace Calendar', '/', ['class' => 'navbar-brand']); ?>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
            <?php $authUser = $this->request->getSession()->read('Auth.User'); ?>
            <ul class="nav navbar-nav">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                       aria-expanded="false">Popular Links <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="https://talk.dallasmakerspace.org">Talk</a></li>
                        <li><a href="https://source.dallasmakerspace.org">Source</a></li>
                        <li><a href="https://learn.dallasmakerspace.org">Learn</a></li>
                    </ul>
                </li>
                <?php if (!($this->request->getParam('controller') == 'Events' && $this->request->getParam('action') == 'add')): ?>
                    <?php if ($authUser): ?>
                        <li>
                            <?= $this->Html->link('Submit Event', [
                                'controller' => 'Events',
                                'action' => 'add'
                            ]) ?>
                        </li>
                    <?php else: ?>
                        <li>
                            <?= $this->Html->link('Submit Event', [
                                'controller' => 'Users',
                                'action' => 'login',
                                '?' => ['redirect' => '/events/add']
                            ]) ?>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($canManageHonoraria): ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Honoraria <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li>
                                <?= $this->Html->link('Pending', [
                                    'controller' => 'Events',
                                    'action' => 'pendingHonoraria'
                                ]) ?>
                            </li>
                            <li>
                                <?= $this->Html->link('Accepted', [
                                    'controller' => 'Events',
                                    'action' => 'acceptedHonoraria'
                                ]) ?>
                            </li>
                            <li>
                                <?= $this->Html->link('Rejected', [
                                    'controller' => 'Events',
                                    'action' => 'rejectedHonoraria'
                                ]) ?>
                            </li>
                            <li>
                                <?= $this->Html->link('Counts', [
                                    'controller' => 'Events',
                                    'action' => 'upcomingHonoraria'
                                ]) ?>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if ($hasFinancialMenu): ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Financials <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <?php if ($canManageW9s): ?>
                                <!--<li>
                                    <?= $this->Html->link('Unprocessed W9s', [
                                        'controller' => 'W9s',
                                        'action' => 'index'
                                    ]) ?>
                                </li>-->
                            <?php endif; ?>
                            <?php if ($canManageFinanceReports): ?>
                                <!--<li>
                                    <?= $this->Html->link('Reports', [
                                        'controller' => 'reports',
                                        'action' => 'index'
                                    ]) ?>
                                </li>-->
                            <?php endif; ?>
                            <?php if ($canExportHonoraria): ?>
                              <li>
                                  <?= $this->Html->link('Export Honoraria', [
                                      'controller' => 'Events',
                                      'action' => 'exportHonoraria'
                                  ]) ?>
                              </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>
		        <?php if ($hasCalendarAdminMenu || $canDisableHonoraria): ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Super Calendar Admin <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                           <li><?= $this->Html->link('Settings', [
                               'controller' => 'CalendarAdmin',
                               'action' => 'edit'
                           ]) ?></li>
			            </ul>
		            </li>
		        <?php endif; ?>
                <?php if ($hasAdminMenu): ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Admin <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <?php if ($canManageEvents): ?>
                                <li>
                                    <?= $this->Html->link('Pending Events', [
                                        'controller' => 'events',
                                        'action' => 'pending'
                                    ]) ?>
                                </li>
                                <li>
                                    <?= $this->Html->link('Events Archive', [
                                        'controller' => 'events',
                                        'action' => 'all'
                                    ]) ?>
                                </li>
                                <li role="separator" class="divider"></li>
                            <?php endif; ?>
                            <?php if ($canManageCategories): ?>
                                <li>
                                    <?= $this->Html->link('Categories', [
                                        'controller' => 'categories',
                                        'action' => 'index'
                                    ]) ?>
                                </li>
                            <?php endif; ?>
                            <?php if ($canManageCommittees): ?>
                                <li>
                                    <?= $this->Html->link('Committees', [
                                        'controller' => 'committees',
                                        'action' => 'index'
                                    ]) ?>
                                </li>
                            <?php endif; ?>
                            <?php if ($canManageContacts): ?>
                                <li>
                                    <?= $this->Html->link('Contacts', [
                                        'controller' => 'contacts',
                                        'action' => 'index'
                                    ]) ?>
                                </li>
                            <?php endif; ?>
                            <?php if ($canManagePreqequisites): ?>
                                <li>
                                    <?= $this->Html->link('Prerequisites', [
                                        'controller' => 'prerequisites',
                                        'action' => 'index'
                                    ]) ?>
                                </li>
                            <?php endif; ?>
                            <?php if ($canManageRooms): ?>
                                <li>
                                    <?= $this->Html->link('Rooms', [
                                        'controller' => 'rooms',
                                        'action' => 'index'
                                    ]) ?>
                                </li>
                            <?php endif; ?>
                            <?php if ($canManageTools): ?>
                                <li>
                                    <?= $this->Html->link('Tools', [
                                        'controller' => 'tools',
                                        'action' => 'index'
                                    ]) ?>
                                </li>
                            <?php endif; ?>
                            <?php if ($canManageConfigs): ?>
                                <li>
                                    <?= $this->Html->link('Configuration', [
                                        'controller' => 'configurations',
                                        'action' => 'index'
                                    ]) ?>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <?php if ($authUser): ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">My Account <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li>
                                <?= $this->Html->link('Hosting Events', [
                                    'controller' => 'Events',
                                    'action' => 'submitted'
                                ]) ?>
                            </li>
                            <li>
                                <?= $this->Html->link('Attending Events', [
                                    'controller' => 'Events',
                                    'action' => 'attending'
                                ]) ?>
                            </li>
                            <li role="separator" class="divider"></li>
                            <li>
                                <?= $this->Html->link('Logout', [
                                    'controller' => 'Users',
                                    'action' => 'logout'
                                ]) ?>
                            </li>
                        </ul>
                    </li>

                <?php else: ?>
                <li>
                   <?= $this->Html->link( "DMS Login", [
                        'controller' => 'Users',
                        'action' => 'login',
                        '?' => ['redirect' => $this->request->getAttribute("here")]
                    ]) ?>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
