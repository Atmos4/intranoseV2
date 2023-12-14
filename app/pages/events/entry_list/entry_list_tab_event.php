<?php
$eventId = Component::prop("event_id");
$all_event_entries = Event::getAllEntries($eventId);
$options = check_auth(Access::$ADD_EVENTS) ? ["0", "1"] : ["❌", "✅"];
?>
<figure>
    <table>
        <thead>
            <tr>
                <th></th>
                <th class="center">Transport</th>
                <th class="center">Hébergement</th>
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
                        <td class="center">
                            <?= $options[$entry->transport] ?>
                        </td>
                        <td class="center">
                            <?= $options[$entry->accomodation] ?>
                        </td>
                        <td>
                            <?= $entry->comment ?>
                        </td>
                    </tr>
                <?php endif ?>
            <?php endforeach;
            $table = ob_get_clean() ?>
            <?= TotalRow("Total", [$totalTransport, true], [$totalAccomodation, true], "") ?>
            <?= $table ?>
        </tbody>
    </table>
</figure>