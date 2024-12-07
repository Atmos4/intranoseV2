<?php
page("Login")->css("about.css")->disableNav()->heading(false);
?>
<nav>
    <ul>
        <li><button role="link" class="outline contrast" onclick="history.back()"><i class="fa fa-caret-left"></i>
                Retour</button></li>
    </ul>
</nav>
<div class="row center g-4">
    <div class="col-12 col-md col-lg-7">
        <h1 class="logo-top">
            <?php import(__DIR__ . "/../components/linklub_logo.php")(false) ?>
        </h1>
        <div class="header">
            Linklub est une application web qui permet de gérer les événements de votre club. Si vous cherchez un
            système de
            gestion d'événements simple et efficace, vous êtes au bon endroit.
        </div>
    </div>
    <div class="col-12 col-md-auto col-lg-5">
        <div class="mockup-container">
            <div class="mockup-phone">
                <div class="camera"></div>
                <div class="display">
                    <div class="artboard">
                        <img class="image" src="assets/images/PhoneMockup.webp" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>