<?php
restrict_access();
restrict_feature(Feature::Messages);
page("Nouveau message") ?>

<?= actions()->back("/messages") ?>

<input type="search" name="search" placeholder="Destinataire" hx-get="/messages/search-users"
    hx-trigger="input changed delay:500ms, search" hx-target="#search-results">
<div id="search-results"></div>