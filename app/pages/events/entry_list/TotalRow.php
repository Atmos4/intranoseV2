<?php
function TotalRow(...$columns)
{
    ?>
    <tr>
        <?php foreach ($columns as $total):
            if (!is_array($total))
                $total = [$total] ?>
                <td <?= ($total[1] ?? false) ? 'class=center' : '' ?>>
                <b>
                    <?= $total[0] ?? null ?>
                </b>
            </td>
        <?php endforeach ?>
    </tr>
    <?php
}
?>