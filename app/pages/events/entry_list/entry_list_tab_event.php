<?php
$eventId = Component::prop("event_id");
$event = em()->find(Event::class, $eventId);
$all_event_entries = EventService::getAllEntries($eventId);
$options = check_auth(Access::$ADD_EVENTS) ? ["0", "1"] : ["❌", "✅"];
?>
<figure class="overflow-auto">
    <table>
        <thead>
            <tr>
                <th></th>
                <?php if ($event->is_transport): ?>
                    <th class="center">Transport</th>
                <?php endif ?>
                <?php if ($event->is_accomodation): ?>
                    <th class="center">Hébergement</th>
                <?php endif ?>
                <th>Remarques</th>
            </tr>
        </thead>
        <tbody>

            <?php
            ob_start();
            $totalTransport = 0;
            $totalAccomodation = 0;
            foreach ($all_event_entries as $entry): ?>
                <?php if ($entry->present):
                    $totalTransport += $entry->transport;
                    $totalAccomodation += $entry->accomodation ?>
                    <tr class="clickable" tabindex=0 <?= UserModal::props($entry->user->id) ?>>
                        <td>
                            <?= $entry->user->last_name . " " . $entry->user->first_name ?>
                        </td>
                        <?php if ($event->is_transport): ?>
                            <td class="center">
                                <?= $options[$entry->transport] ?>
                            </td>
                        <?php endif ?>
                        <?php if ($event->is_accomodation): ?>
                            <td class="center">
                                <?= $options[$entry->accomodation] ?>
                            </td>
                        <?php endif ?>
                        <td>
                            <?= $entry->comment ?>
                        </td>
                    </tr>
                <?php endif ?>
            <?php endforeach;
            $table = ob_get_clean() ?>
            <?php
            $totalRowArgs = ["Total"];
            if ($event->is_transport) {
                $totalRowArgs[] = [$totalTransport, true];
            }
            if ($event->is_accomodation) {
                $totalRowArgs[] = [$totalAccomodation, true];
            }
            $totalRowArgs[] = "";
            ?>
            <?= TotalRow(...$totalRowArgs) ?>
            <?= $table ?>
        </tbody>
    </table>
</figure>