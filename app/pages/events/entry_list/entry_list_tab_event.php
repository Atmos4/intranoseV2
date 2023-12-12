<?php
$event_id = Component::prop("event_id");
$all_event_entries = Event::getAllEntries($event_id);
?>
<figure>
    <table>
        <thead>
            <tr>
                <th scope="col">Nom</th>
                <th scope="col">Prénom</th>
                <th scope="col">Transport</th>
                <th scope="col">Hébergement</th>
                <th scope="col">Remarques</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($all_event_entries as $entry): ?>
                <?php if ($entry->present): ?>
                    <tr class="clickable" tabindex=0 <?= UserModal::props($entry->user->id) ?>>
                        <td>
                            <?= $entry->user->last_name ?>
                        </td>
                        <td>
                            <?= $entry->user->first_name ?>
                        </td>
                        <td class="center">
                            <?= $entry->transport ? "1" : "0" ?>
                        </td>
                        <td class="center">
                            <?= $entry->accomodation ? "1" : "0" ?>
                        </td>
                        <td>
                            <?= $entry->comment ?>
                        </td>
                    </tr>
                <?php endif ?>
            <?php endforeach ?>
        </tbody>
    </table>
</figure>