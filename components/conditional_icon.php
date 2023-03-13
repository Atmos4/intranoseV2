<?php
function ConditionalIcon(bool $condition, string $text = "")
{
    if ($condition): ?>
        <ins><i class="fas fa-check"></i></ins>
    <?php else: ?>
        <del><i class="fas fa-xmark"></i></del>
    <?php endif; ?>
    <span class="space-before">
        <?= $text ?>
    </span>
<?php } ?>