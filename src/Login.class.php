<?php
/* class Login
 * créé par Guillaume Gas
 * le 18/06/2010
 *
 *
 * info :
 * utilisation de la session : $_SESSION["user"][" ici le nom du champ : ex : pseudo "]
 * 
 *
 */

class Login {
    private $bdd; //stocke un objet PDO
    private $table;
	
	private $fichier_action; //nécessaire pour l'attribu action du tag form
	
	private $liste_champs_value; //tableau contenant les champs (un pour contenant les values, 
	private $liste_champs_name;  //un pour les noms
	private $liste_champs_type;  //et un pour les types de champs (text, password...)
    private $liste_champs_cryptes;
	
	private $connec_ok; //boolean pour savoir si la connexion a été faite

	public function __construct($p_fichier_action, $p_table, $p_bdd = null) {
		$this->bdd = $p_bdd;
        $this->table = $p_table;
		
		$this->fichier_action = $p_fichier_action;
	
		$this->liste_champs_value = array();
		$this->liste_champs_name = array();
		$this->liste_champs_type = array();
		
		$this->connec_ok = false;

        if(session_status() == PHP_SESSION_DISABLED) {
            session_start();
        }
        if(isset($_SESSION['user'])) {
            unset($_SESSION['user']);
        }
	}
	
	public function logout() {
		unset($_SESSION['user']);
	}

    public function connect_db($p_host, $p_login, $p_mdp, $p_base) {
        $this->bdd = new PDO('mysql:host='.$p_host.';dbname='.$p_base, $p_login, $p_mdp);
    }

	//fonction permettant de rajouter des champs (param : value, le nom du champs et son type (text, password...)
	public function addChamp($p_value, $p_name, $p_type, $p_md5 = false) {
		$this->liste_champs_value[] = $p_value;
		$this->liste_champs_name[]  = $p_name;
		$this->liste_champs_type[]  = $p_type;
        if($p_md5) {
            $this->liste_champs_cryptes[] = $p_name;
        }
	}
	
	public function afficher() {
		
		//on vérifi ici si des données ont été envoyées
		$this->champs_ok = true; //on suppose que les champs ont été remplis
        $liste_valeurs = array();
		for($i = 0; $i < count($this->liste_champs_name); $i++) { //on prend chaque nom de champs
			if(!isset($_POST[$this->liste_champs_name[$i]])) {    //et on vérifi si des données ont été envoyés
				$this->champs_ok = false;
			} else {
                if(in_array($this->liste_champs_name[$i], $this->liste_champs_cryptes)) {
                    $liste_valeurs[':'.$i] = md5($_POST[$this->liste_champs_name[$i]]);
                } else {
                    $liste_valeurs[':'.$i] = $_POST[$this->liste_champs_name[$i]];
                }
            }
		}
		
		//si les données ont été envoyées 
		if($this->champs_ok) {
			//on génére la requéte
			$debut_requete = "SELECT * FROM ".$this->table." WHERE ";
			
			//dans cette partie de la requéte, on doit ajouter chaque nom de champs, ceux ci étant stocké dans un tableau (liste_champs_name)
			$fin_requete = "";
			for($i = 0; $i < count($this->liste_champs_name); $i++) {
				if($i == 0) { //si c'est le premier paramétre, pas besoin de le faire précéder d'un AND 
					$fin_requete = $this->liste_champs_name[$i]." = :".$i;
				} else { //sinon on le sépar des précédent par un AND
					$fin_requete .= " AND ".$this->liste_champs_name[$i]." = :".$i;
				}
			}

            //concaténation de chaque partie de la requéte, utilisation de prepare pour plus de sécurité
			$requete = $this->bdd->prepare($debut_requete.$fin_requete);

			if($requete->execute($liste_valeurs)) {
				if($requete->rowCount() > 0) {
					$_SESSION['user'] = $requete->fetch(); //on stock tout dans une variable de session
					info("Vous etes connecte.");
					$this->connec_ok = true; //on indique que la connexion est établie
				} else {
					erreur("Erreur lors de la connexion.");
				}
			} else {
				erreur("Erreur requete");
			}

            echo var_dump($debut_requete.$fin_requete);
		} else {
			//affichage du formulaire
			echo '<form action="'.$this->fichier_action.'", method="POST">';
			echo '<table>';
	  			for($i = 0; $i < count($this->liste_champs_name); $i++) {
	  				echo ('<tr><td>'.$this->liste_champs_value[$i].' : <input type="'.$this->liste_champs_type[$i].'" name="'.$this->liste_champs_name[$i].'" id="'.$this->liste_champs_name[$i].'"></td></tr>');
	  			}
	  			echo '<tr><td><center><input type="submit" value="ok"></center></td></tr>';
	  		echo '</form>';
			echo '</form>';
		}
	}
	
	//fonction permettant de vérifier si la connexion a été établie
	public function connexion_ok() {
		if($this->connec_ok) {
			return true;
		} else {
			return false;
		}
	}
	
}

function info($message) {
	echo '<center><font color="green">'.$message.'</font></center>';
}
function erreur($message) {
	echo '<center><font color="red">'.$message.'</font></center>';
}
?>
