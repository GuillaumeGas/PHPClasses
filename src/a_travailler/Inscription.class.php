<?
/* class GInscription
 * créé par guidono
 * le 18/06/2010
 *
 * site avec doc : http://www.gas28.net/guillaume/starwars/GClass/doc/index.html
 *
 * info : lorsque le script rencontre un champ "id", celui ci est mi à NULL dans la BDD, celà peut être changé avec la fonction setValueId($p_value);
 *
 */

class GInscription {
    private $host_bdd; //variable nécessaire à la connexion à la base de donnée
    private $login_bdd;
    private $mdp_bdd;
    private $base_bdd;
    private $table_bdd;

    private $fichier_action; //nécessaire pour l'attribu action du tag form

    private $liste_champs_value; //tableau contenant les champs (un pour contenant les values,
    private $liste_champs_name;  //un pour les noms
    private $liste_champs_type;  //un pour les types de champs (text, password...)
    private $liste_champs_bdd;   //un contenant les autres champs de la bdd non affichées dans le formulaire (ex : id, date...)
    private $liste_valeur_champs_bdd;
    private $nbr_champs;		 //nombre de champs pour la bdd
    private $value_id;           //valeur de l'id (si il y en a un) par défault NULL

    private $liste_select_champ;  //tableaux pour stocker les option de chaque champs select s'il y en a (ici pour le nom du champ)
    private $liste_select_option; // (ici pour la valeur de l'option)

    //constructeur, initiation de toutes les variables...
    public function __construct($p_host, $p_login, $p_mdp, $p_base, $p_table, $p_fichier_action) {
        $this->host_bdd = $p_host;
        $this->login_bdd = $p_login;
        $this->mdp_bdd = $p_mdp;
        $this->base_bdd = $p_base;
        $this->table_bdd = $p_table;

        $this->fichier_action = $p_fichier_action;

        $this->liste_champs_value = array();
        $this->liste_champs_name = array();
        $this->liste_champs_type = array();
        $this->liste_champs_bdd = array();

        $this->nbr_champs = 0;

        $this->liste_select_champ = array();
        $this->liste_select_option = array();

        $this->value_id = "NULL";

        $this->connec_ok = false;
    }

    public function __destruct() {
        mysql_close();
    }

    //fonction permettant de rajouter des champs (param : value, le nom du champs et son type (text, password...)
    public function addChamp($p_value, $p_name, $p_type) {
        array_push($this->liste_champs_value, $p_value);
        array_push($this->liste_champs_name, $p_name);
        array_push($this->liste_champs_type, $p_type);
        $this->nbr_champs++; //on ajoute 1 au nombre de champs
    }

    //fonction ajoutant un champs au tableau, celui ci servant à indiquer les champs de la bdd non affiché dans le formulaire
    public function addChamp_bdd($p_champ) {
        array_push($this->liste_champs_bdd, $p_champ);
        $this->nbr_champs++; //on ajoute 1 au nombre de champs
    }

    //fonction ajoutant des option à un champ select
    public function addSelectOption($p_option, $p_champ) {
        $select_existe = false;
        for($i = 0; $i < count($this->liste_champs_name); $i++) {
            if($this->liste_champs_name[$i] == $p_champ) {
                for($j = 0; $j < count($this->liste_champs_type); $j++) {
                    if($this->liste_champs_type[$j] == "select") {
                        $select_existe = true;
                    }
                }
            }
        }
        if($select_existe) {
            array_push($this->liste_select_option, $p_option);
            array_push($this->liste_select_champ, $p_champ);
        }
    }

    /*ici la fonction affich() voici son fonctionnement :
    * - connexion à la base de donnée
    * - on vérifi les les champs ont été remplis
    * - si oui on génère la requête en fonction du nombre de champs
    * - on execute la requête
    * - sinon on affiche le formulaire
    */
    public function affich() {
        //connexion à la base de donnée
        mysql_connect($this->host_bdd, $this->login_bdd, $this->mdp_bdd) or die("Erreur connection BDD");
        mysql_select_db($this->base_bdd) or die("Erreur selection table");

        $this->liste_valeur_champs_bdd = array_fill(0, count($this->liste_champs_bdd), 0);

        //on vérifi ici si des données ont été envoyées
        $this->champs_ok = true; //on suppose que les champs ont été remplis et que des données ont été envoyées
        for($i = 0; $i < count($this->liste_champs_name); $i++) { //on prend chaque nom de champs
            if(!isset($_POST[$this->liste_champs_name[$i]]) || $_POST[$this->liste_champs_name[$i]] == "") { //et on vérifi si des données ont été envoyés
                $this->champs_ok = false;
            }
        }

        if($this->champs_ok) {
            for($i = 0; $i < count($this->liste_champs_name); $i++) {
                for($j = 0; $j < count($this->liste_champs_name); $j++) {
                    if($i != $j) {
                        if($this->liste_champs_name[$i] == $this->liste_champs_name[$j]) {
                            $this->champs_ok = false;
                            erreur("Erreur : Vous avez deux champs identiques.");
                            return;
                        }
                    }
                }
            }
        }

        //si les données ont été envoyées
        if($this->champs_ok) {
            //on génère la requête
            $debut_requete = "INSERT INTO ".$this->table_bdd."(";

            //dans cette partie de la requête, on liste les champs à remplir
            $milieu_requete = "";
            $compteur_liste_bdd = 0;
            for($i = 0; $i < $this->nbr_champs; $i++) {
                if($i == 0) {
                    $milieu_requete = $this->liste_champs_name[$i];
                } else {
                    if($i < count($this->liste_champs_name)) {
                        $milieu_requete = $milieu_requete.", ".$this->liste_champs_name[$i];
                    } else {
                        $milieu_requete = $milieu_requete.", ".$this->liste_champs_bdd[$compteur_liste_bdd];
                        $compteur_liste_bdd++;
                    }
                }
            }
            $milieu_requete = $milieu_requete.") VALUES(";

            //dans cette dernière partie de la requête, on met les valeurs à insérer dans la base de données
            $fin_requete = "";
            $compteur_liste_bdd = 0;
            for($i = 0; $i < $this->nbr_champs; $i++) {
                if($i == 0) {
                    $fin_requete = "'".$_POST[$this->liste_champs_name[$i]]."'";
                } else {
                    if($i < count($this->liste_champs_name)) {
                        $fin_requete = $fin_requete.", '".$_POST[$this->liste_champs_name[$i]]."'";
                    } else {
                        if($this->liste_champs_bdd[$compteur_liste_bdd] == "id") {
                            $fin_requete = $fin_requete.", ".$this->value_id;
                            $compteur_liste_bdd++;
                        } else {
                            $fin_requete = $fin_requete.", ".$this->liste_champs_bdd[$compteur_liste_bdd];
                            $compteur_liste_bdd++;
                        }
                    }
                }
            }
            $fin_requete = $fin_requete.")";

            $requete = mysql_query($debut_requete.$milieu_requete.$fin_requete); //concaténation de chaque partie de la requête
            //execution de la requête
            if($requete) {
                info("Inscription reussie !");
            } else {
                erreur("Erreur requete, l'inscription a echouee.");
                return;
            }
        } else {
            //affichage du formulaire
            echo '<form action="'.$this->fichier_action.'", method="POST">';
            echo '<table>';
            for($i = 0; $i < count($this->liste_champs_name); $i++) {
                if($this->liste_champs_type[$i] == "select") {
                    echo ('<tr><td>'.$this->liste_champs_value[$i].' : <select name="'.$this->liste_champs_name[$i].'" id="'.$this->liste_champs_name[$i].'">');
                    for($j = 0; $j < count($this->liste_select_option); $j++) {
                        if($this->liste_select_champ[$j] == $this->liste_champs_name[$i]) {
                            echo '<option>'.$this->liste_select_option[$j].'</option>';
                        }
                    }
                    echo '</select></td></tr>';
                } else {
                    echo ('<tr><td>'.$this->liste_champs_value[$i].' : <input type="'.$this->liste_champs_type[$i].'" name="'.$this->liste_champs_name[$i].'" id="'.$this->liste_champs_name[$i].'"></td></tr>');
                }
            }
            echo '<tr><td><center><input type="submit" value="ok"></center></td></tr>';
            echo '</table>';
            echo '</form>';
        }
    }

    //fonction permettant de changer la valeur par défault de l'id a rentrer dans la table (par défaut NULL)
    public function setValueId($p_value) {
        $this->value_id = $p_value;
    }

    //fonction permettant de modifier la valeur par défaut d'un champs
    public function setDefaultValue($p_champ, $p_value) {
        $existe = false;
        for($i = 0; $i < count($this->liste_champs_bdd); $i++) {
            if($this->liste_champs_bdd[$i] == $p_champ) {
                $this->liste_champs_value[$i] = $p_value;
                $existe = true;
            }
        }
    }

}
function info($message) {
    echo '<center><table style="background-color:green"><tr><td><img src="img_info.png"></td><td><font color="white">'.$message.'</font></td></tr></table></center>';
}
function erreur($message) {
    echo '<center><table style="background-color:red"><tr><td><img src="img_erreur.png"></td><td><font color="black">'.$message.'</font></td></tr></table></center>';
}
?>