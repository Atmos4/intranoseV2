<?php
$count = em()
    ->createQuery("SELECT COUNT(u) FROM User u WHERE u.nose_email=:email")
    ->setParameters(["email" => 'arnaud.perrin@nose42.fr'])
    ->getSingleScalarResult();
print_r($count);
page("Random") ?>
<p>Random page to test things on</p>