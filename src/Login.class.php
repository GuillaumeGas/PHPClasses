<?
/* class GLogin 
 * créé par guidono
 * le 18/06/2010
 * 
 * site avec doc : http://www.gas28.net/guillaume/starwars/GClass/doc/index.html
 *
 * info : creation de cet objet doit être précédé d'un session_start() ainsi que d'un unset($_SESSION["user"]); par sécurité
 * utilisation de la session : $_SESSION["user"][" ici le nom du champ : ex : pseudo "]
 * 
 *
 */

class GLogin {
	private $host_bdd; //variable nécessaire à la connexion à la base de donnée
	private $login_bdd;
	private $mdp_bdd;
	private $base_bdd;
	private $table_bdd;
	
	private $fichier_action; //nécessaire pour l'attribu action du tag form
	
	private $liste_champs_value; //tableau contenant les champs (un pour contenant les values, 
	private $liste_champs_name;  //un pour les noms
	private $liste_champs_type;  //et un pour les types de champs (text, password...)
	
	private $connec_ok; //boolean pour savoir si la connexion a été faite
	
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
		
		$this->connec_ok = false;
	}
	
	public function __destruct() {
		unset($_SESSION['user']); //on vide la variable de session
		mysql_close();			  //on ferme la connexion à la BDD
	}
	
	public function logout() {
		unset($_SESSION['user']);
	}
	
	//fonction permettant de rajouter des champs (param : value, le nom du champs et son type (text, password...)
	public function addChamp($p_value, $p_name, $p_type) {
		array_push($this->liste_champs_value, $p_value); //on rempli les tableaux...
		array_push($this->liste_champs_name, $p_name);
		array_push($this->liste_champs_type, $p_type);
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
		
		//on vérifi ici si des données ont été envoyées
		$this->champs_ok = true; //on suppose que les champs ont été remplis
		for($i = 0; $i < count($this->liste_champs_name); $i++) { //on prend chaque nom de champs
			if(!isset($_POST[$this->liste_champs_name[$i]])) {    //et on vérifi si des données ont été envoyés
				$this->champs_ok = false;						  
			}
		}
		
		//si les données ont été envoyées 
		if($this->champs_ok) {
			//on génère la requête
			$debut_requete = "SELECT * FROM ".$this->table_bdd." WHERE ";
			
			//dans cette partie de la requête, on doit ajouter chaque nom de champs, ceux ci étant stocké dans un tableau (liste_champs_name)
			$fin_requete = "";
			for($i = 0; $i < count($this->liste_champs_name); $i++) {
				if($i == 0) { //si c'est le premier paramètre, pas besoin de le faire précéder d'un AND 
					$fin_requete = $this->liste_champs_name[$i]." = '".$_POST[$this->liste_champs_name[$i]]."' ";
				} else { //sinon on le sépar des précédent par un AND
					$fin_requete = $fin_requete."AND ".$this->liste_champs_name[$i]." = '".$_POST[$this->liste_champs_name[$i]]."' ";
				}
			}
			
			$requete = mysql_query($debut_requete.$fin_requete); //concaténation de chaque partie de la requête
			//execution de la requête
			if($requete) {
				if(mysql_num_rows($requete)) {
					$_SESSION['user'] = mysql_fetch_array($requete); //on stock tout dans une variable de session
					info("Vous etes connecte.");
					$this->connec_ok = true; //on indique que la connexion est établie
				} else {
					erreur("Erreur lors de la connexion.");
				}
			} else {
				erreur("Erreur requete");
			}
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
