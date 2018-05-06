                            <table>
                                <tr>
                                    <td><strong>When</strong></td>
                                    <td>
                                        <?= str_replace(
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
                                                'h:mma',
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
                                    <td><?= $event->short_description ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Cost</strong></td>
                                    <td>$<?= number_format($event->cost, 2) ?></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                        <?= str_replace('"', "'", $this->Html->link('More Info and RSVP »', [
                                            'action' => 'view',
                                            $event->id
                                        ])) ?>
                                    </td>
                                </tr>
                            </table>
                            <p>
								<?= $event->long_description ?>
							</p>
							