<?php
if (AuthService::create()->isUserLoggedIn()) {
    redirect("evenements");
}
redirect("login");