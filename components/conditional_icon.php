<?php
/** Creates a icon with optional text after
 * - `$condition = true` => green checkmark
 * - `$condition = false` => red X */
function ConditionalIcon(bool $condition, string $text = "")
{
    if ($condition): ?>
        <ins><i class="fas fa-check"></i></ins>
    <?php else: ?>
        <del><i class="fas fa-xmark"></i></del>
    <?php endif; ?>
    <?php if (!!$text): ?>
        <span class="space-before">
            <?= $text ?>
        </span>
    <?php endif ?>
<?php } ?>