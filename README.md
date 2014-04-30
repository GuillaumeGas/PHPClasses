PHPClasses
==========

Regroupement de classes php utiles

# Login #
Cette classe permet la création d'une page de login très simplement, avec ou sans génération de formulaire.
Exemple :
```php
session_start();
require("Login.class.php");

//On indique la table à utiliser
$l = new Login("admin");
//$l = new Login("admin", $bdd); Cas où nous sommes déjà connecté avec PDO
//On indique qu'on veut utiliser le fichier de log
$l->show_log_messages(true);
//Connexion à la base si besoin
$l->connect_db("localhost", "guidono", "235235", "peche_dir4");

//On ajoute les champs voulus (label, nom_champ, type_champ)
$l->addChamp("Login", "login", "text");
$l->addChamp("Mdp", "mdp", "password", true);

if($l->login()) {
    echo "ok";
    $l->logout();
} else {
    $l->generer_formulaire("test.php");
}
```

# Log #
Cette classe permet de gérer un fichier de log
Exemple :
```php
require("Log.class.php");

$log = new Log("../login.log");

try {
    $this->bdd = new PDO('mysql:host='.$p_host.';dbname='.$p_base, $p_login, $p_mdp);
    $this->log->add_info_log("Connexion à la base de donnees effectuee.");
} catch(PDOException $e) {
    $this->log->add_err_log("Erreur lors de la connexion à la base de données.");
}

$this->log->write_log();
```
