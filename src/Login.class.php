<?
/* class GLogin 
 * cr�� par guidono
 * le 18/06/2010
 * 
 * site avec doc : http://www.gas28.net/guillaume/starwars/GClass/doc/index.html
 *
 * info : creation de cet objet doit �tre pr�c�d� d'un session_start() ainsi que d'un unset($_SESSION["user"]); par s�curit�
 * utilisation de la session : $_SESSION["user"][" ici le nom du champ : ex : pseudo "]
 * 
 *
 */

class GLogin {
	private $host_bdd; //variable n�cessaire � la connexion � la base de donn�e
	private $login_bdd;
	private $mdp_bdd;
	private $base_bdd;
	private $table_bdd;
	
	private $fichier_action; //n�cessaire pour l'attribu action du tag form
	
	private $liste_champs_value; //tableau contenant les champs (un pour contenant les values, 
	private $liste_champs_name;  //un pour les noms
	private $liste_champs_type;  //et un pour les types de champs (text, password...)
	
	private $connec_ok; //boolean pour savoir si la connexion a �t� faite
	
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
		mysql_close();			  //on ferme la connexion � la BDD
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
	* - connexion � la base de donn�e
	* - on v�rifi les les champs ont �t� remplis
	* - si oui on g�n�re la requ�te en fonction du nombre de champs
	* - on execute la requ�te
	* - sinon on affiche le formulaire
	*/
	public function affich() {
		//connexion � la base de donn�e
		mysql_connect($this->host_bdd, $this->login_bdd, $this->mdp_bdd) or die("Erreur connection BDD");
		mysql_select_db($this->base_bdd) or die("Erreur selection table");
		
		//on v�rifi ici si des donn�es ont �t� envoy�es
		$this->champs_ok = true; //on suppose que les champs ont �t� remplis
		for($i = 0; $i < count($this->liste_champs_name); $i++) { //on prend chaque nom de champs
			if(!isset($_POST[$this->liste_champs_name[$i]])) {    //et on v�rifi si des donn�es ont �t� envoy�s
				$this->champs_ok = false;						  
			}
		}
		
		//si les donn�es ont �t� envoy�es 
		if($this->champs_ok) {
			//on g�n�re la requ�te
			$debut_requete = "SELECT * FROM ".$this->table_bdd." WHERE ";
			
			//dans cette partie de la requ�te, on doit ajouter chaque nom de champs, ceux ci �tant stock� dans un tableau (liste_champs_name)
			$fin_requete = "";
			for($i = 0; $i < count($this->liste_champs_name); $i++) {
				if($i == 0) { //si c'est le premier param�tre, pas besoin de le faire pr�c�der d'un AND 
					$fin_requete = $this->liste_champs_name[$i]." = '".$_POST[$this->liste_champs_name[$i]]."' ";
				} else { //sinon on le s�par des pr�c�dent par un AND
					$fin_requete = $fin_requete."AND ".$this->liste_champs_name[$i]." = '".$_POST[$this->liste_champs_name[$i]]."' ";
				}
			}
			
			$requete = mysql_query($debut_requete.$fin_requete); //concat�nation de chaque partie de la requ�te
			//execution de la requ�te
			if($requete) {
				if(mysql_num_rows($requete)) {
					$_SESSION['user'] = mysql_fetch_array($requete); //on stock tout dans une variable de session
					info("Vous etes connecte.");
					$this->connec_ok = true; //on indique que la connexion est �tablie
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
	
	//fonction permettant de v�rifier si la connexion a �t� �tablie
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
