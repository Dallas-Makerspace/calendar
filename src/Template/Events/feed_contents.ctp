                            <table>
                                <tr>
                                    <td><strong>When</strong></td>
                                    <td>
                                    <?php
                                    /** @var \App\Model\Entity\Event $event */
                                    $startdate = $this->Time->fromString($event->event_start, 'America/Chicago')->format('Ymd');
                                            $enddate = $this->Time->fromString($event->event_end, 'America/Chicago')->format('Ymd');
                                            if ($startdate == $enddate) {
                                                $secondFormat = "h:mma";
                                            } else {
                                                $secondFormat = "E MMM d h:mma";
                                            }
                                        ?><?= str_replace(
                                            [':00', 'AM', 'PM'],
                                            ['', 'am', 'pm'],
                                            $this->Time->format(
                                                $event->event_start,
                                                'E MMM d h:mma',
                                                null,
                                                'America/Chicago'
                                            )
                                        ) ?> —
                                        <?= str_replace(
                                            [':00', 'AM', 'PM'],
                                            ['', 'am', 'pm'],
                                            $this->Time->format(
                                                $event->event_end,
                                                $secondFormat,
                                                null, 'America/Chicago'
                                            )
                                        )?> Central
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Where</strong></td>
                                    <td>
                                        <?php if ($event->room): ?>
                                            <?= $event->room->name ?><br/>
                                        <?php endif; ?>
                                        <?php if ($event->address): ?>
                                            <?= $event->address ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Details</strong></td>
                                    <td><?= h($event->short_description) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Host</strong></td>
                                    <td><?= h($event->contact->name) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Cost</strong></td>
                                    <td>$<?= number_format($event->cost, 2) ?></td>
                                </tr>
                            </table>
                            <p>
								<?= nl2br(h($event->long_description)) ?>
							</p>
