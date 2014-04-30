<?php

require("Login.class.php");

$l = new Login("test.php", "admin");
$l->connect_db("localhost", "guidono", "235235", "peche_dir4");

$l->addChamp("Login", "login", "text");
$l->addChamp("Mdp", "mdp", "password", true);

$l->afficher();

if($l->connexion_ok()) {
    echo "ok";
}

?>