<?php

/**
 * @brief Classe permettant de gérer l'enregistrement d'un ou de plusieurs fichiers envoyés via formulaire html
 *  La fonction sauver à utiliser pour la réception d'un fichier
 *  La fonction sauver_multiple à utiliser pour la réception de plusieurs fichiers (mot clé multiple dans champs html)
 *  Possibilité d'activer le fichier de log
 *  Possibilité d'afficher les info et erreurs
 *  Possibilité de récupérer le ou les noms des fichiers une fois copiés
 *  Système permettant d'avoir un nom unique de fichier à coup sûr évitant les conflits de nom sur le disque
 */
class Fichiers {

    private $_files_names;      //Nom des fichiers une fois copiés
    private $_err;              //Contient les erreurs
    private $_nb_err;           //Contient le nombre d'erreurs
    private $_info;             //Contient les infos
    private $_log;              //Contient les log (err+info)
    private $_utiliser_log;     //Indique si le fichier de log doit être créé (defaut : false)
    private $_afficher_erreur;  //Indique si les erreurs doivent être affichées (defaut : false)
    private $_afficher_info;    //Indique si les info doivent être affichées (defaut : false)
    private $_path_log;         //Chemin et nom du fichier de log (defaut : "log_fichier.txt")

    public function __construct() {
        $this->_files_names     = array();
        $this->_err             = "";
        $this->_nb_err          = 0;
        $this->_info            = "";
        $this->_utiliser_log    = false;
        $this->_afficher_erreur = false;
        $this->_afficher_info   = false;
        $this->_path_log        = "log_fichier.txt";
    }

    /**
     * @brief Permet de sauvegarder l'unique fichier envoyé en POST
     * @param $fichier Correspond à la clé du tableau associatif $_FILES
     * @param string $path Indique ou enregistrer le fichier (par défaut répertoire courant)
     * @param string $nom_fichier Indique le nouveau nom du fichier, ou bien concaténation de l'ancien avec celui-ci si on commence par % Ex : .., "%NEW") -> "ancienNEW.extension"
     * @param string $extension Indique la seule extension à accepter
     * @return bool Retourne false en cas d'erreur sinon false
     */
    public function sauver($fichier, $path = "", $nom_fichier = "", $extension = "-1") {
        if(isset($_FILES)) {
            if($_FILES[$fichier]['error'] == UPLOAD_ERR_OK) {
                if(isset($_FILES[$fichier]['name'])) {
                    $ext = '.'.pathinfo($_FILES[$fichier]['name'],PATHINFO_EXTENSION); //récupération de l'extension du fichier pour analyse
                    if($extension != "-1" && $ext != $extension) {
                        $this->add_err_log("[!] Mauvaise extention ! Attendue : ".$extension.", Trouvee : ".$ext);
                        $this->_nb_err++;
                    } else {
                        if(!empty($nom_fichier)) {
                            //Le % indique qu'un désire une concaténation
                            if($nom_fichier[0] == "%") {
                                $file_name = $this->trouver_nom_unique(pathinfo($_FILES[$fichier]['name'])['filename'].substr($nom_fichier, 1, strlen($nom_fichier)).$ext, $path);
                            } else {
                                $file_name = $this->trouver_nom_unique($nom_fichier, $path);
                            }
                        } else {
                            $file_name = $this->trouver_nom_unique($_FILES[$fichier]['name'], $path);
                        }
                        //On garde en mémoire le nouveau nom du fichier
                        $this->_files_names[] = $_FILES[$fichier]['name'];

                        //On copie pour de bon le fichier
                        if(!move_uploaded_file($_FILES[$fichier]['tmp_name'], $path.$file_name)) {
                            $this->add_err_log("[!] Erreur lors de la copie du fichier.");
                            $this->_nb_err++;
                        } else {
                            $this->add_info_log("[Copie reussie] : ".$_FILES[$fichier]['name']." -> ".$path.$file_name);
                        }
                    }
                } else {
                    $this->add_err_log("[!] Erreur, fichier inexistant.<br>");
                    $this->_nb_err++;
                }
            } else {
                $this->check_erreur($_FILES[$fichier]['error']);
                $this->_nb_err++;
            }
        } else {
            $this->add_err_log("[!] Aucun fichier envoyé.");
            $this->_nb_err++;
        }

        if($this->_utiliser_log == true) {
            $this->ecrire_log(); //ecrit la dernière info ou erreur dans le fichier de log
        }

        if($this->_afficher_info == true) {
            $this->afficher_info();
        }

        if($this->_nb_err > 0) {
            if($this->_afficher_erreur == true) {
                $this->afficher_err();
            }
            return false;
        } else {
            return true;
        }
    }

    /**
     * @brief Permet de sauvegarder les fichiers envoyés en POST
     * @param $fichiers Correspond à la clé du tableau associatif $_FILES
     * @param string $path Indique ou enregistrer le fichier (par défaut répertoire courant)
     * @param string $concat Indique qu'on veut concaténer l'ancien nom avec la chaine suivante (l'extension est évitée lors de la concaténation)
     * @param string $extension Indique la seule extension à accepter
     * @return bool Retourne false en cas d'erreur sinon false
     */
    public function sauver_multiple($fichiers, $path = "", $concat = "", $extension = "-1") {
        if(isset($_FILES)) {
            $i = 0;
            while(isset($_FILES[$fichiers]['name'][$i])) {
                if($_FILES[$fichiers]['error'][$i] == UPLOAD_ERR_OK) {
                    $ext = '.'.pathinfo($_FILES[$fichiers]['name'][$i],PATHINFO_EXTENSION);
                    if($extension != "-1" && $ext != $extension) {
                        $this->add_err_log("[!] Mauvaise extention ! Attendue : ".$extension.", Trouvee : ".$ext.", Fichier concerné : ".$_FILES[$fichiers]['name'][$i].".");
                        $this->_nb_err++;
                    } else {
                        if(!empty($concat)) {
                            $file_name = $this->trouver_nom_unique(pathinfo($_FILES[$fichiers]['name'][$i])['filename'].$concat.$ext, $path);
                        } else {
                            $file_name = $this->trouver_nom_unique($_FILES[$fichiers]['name'][$i], $path);
                        }
                        $this->_files_names[] = $file_name;

                        if(!move_uploaded_file($_FILES[$fichiers]['tmp_name'][$i], $path.$file_name)) {
                            $this->add_err_log("[!] Erreur lors de la copie du fichier ".$_FILES[$fichiers]['name'][$i].".");
                            $this->_nb_err++;
                        } else {
                            $this->add_info_log("[Copie reussie] : ".$_FILES[$fichiers]['name'][$i]." -> ".$path.$file_name.".");
                        }

                    }

                    $i++;
                } else {
                    $this->check_erreur($_FILES[$fichiers]['error'][$i]);
                    $this->add_err_log("\tFichier concerné ".$_FILES[$fichiers]['name'][$i].".");
                    $this->_nb_err++;
                }
            }
        } else {
            $this->add_err_log("[!] Aucun fichier envoyé.");
            $this->_nb_err++;
        }

        if($this->_utiliser_log == true) {
            $this->ecrire_log(); //ecrit la dernière info ou erreur dans le fichier de log
        }

        if($this->_nb_err > 0) {
            if($this->_afficher_erreur == true) {
                $this->afficher_err();
            }
            return false;
        } else {
            return true;
        }
    }

    /**
     * @brief Renvoie un nom de fichier qui ne va pas entrer en collision avec celui d'un fichier déjà présent sur le disque
     * @param $fichier Représente le nom du fichier
     * @param $path Représente le répertoire dans lequel on veut copier le fichier
     * @return string Représente le nom définitif
     */
    public function trouver_nom_unique($fichier, $path) {
        $nom_fichier = pathinfo($fichier)['filename']; //récupère le nom du fichier sans l'extension
        $ext = '.'.pathinfo($fichier,PATHINFO_EXTENSION); //récupère l'extension
        $i = -1;
        $res = "";
        do {
            $i++;
            $res = $nom_fichier."_".$i.$ext;
        } while(file_exists($path.$res));

        return $res;
    }

    private function check_erreur($erreur) {
        switch($erreur) {
            case UPLOAD_ERR_INI_SIZE:
                $this->add_err_log("[!] Fichier trop gros ! Taille autorisée par le <u>serveur</u> : ".ini_get('upload_max_filesize'));
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $this->add_err_log("[!] Fichier trop gros ! Taille autorisée par le <u>formulaire</u> : ".$_POST['MAX_FILE_SIZE']);
                break;
            case UPLOAD_ERR_PARTIAL:
                $this->add_err_log("[!] Une erreur est survenue, le fichier n'a été que partiellement téléchargé");
                break;
            case UPLOAD_ERR_NO_FILE:
                $this->add_err_log("[!] Aucun fichier n'a été téléchargé");
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $this->add_err_log("[!] Dossier temporaire manquant");
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $this->add_err_log("[!] Echec de l'écriture sur le disque.");
                break;
            case UPLOAD_ERR_EXTENSION:
                $this->add_err_log("[!] Une extension PHP a arrêté l'envoi du fichier.");
                break;
            default:
                $this->add_err_log("[!] Erreur inconnue.");
        }
    }

    /**
     * @brief Ajout le message d'erreur aux variables concernées (err et log)
     * @param $err Représente le message d'erreur
     */
    private function add_err_log($err) {
        $this->_err .= $err."<br>";
        $this->_log .= date("[j/m/y H:i:s]")." - ".$err."\r\n";
    }

    /**
     * @brief Ajout le message d'info aux variables concernées (info et log)
     * @param $err Représente le message d'info
     */
    private function add_info_log($info) {
        $this->_info .= $info."<br>";
        $this->_log .= date("[j/m/y H:i:s]")." - ".$info."\r\n";
    }

    /**
     * @brief Renvoie les noms définitifs des fichiers enregistrés
     * @return array
     */
    public function get_files_names() {
        return $this->_files_names;
    }

    /**
     * @brief Permet d'activer l'utilisation du fichier de log
     * @param $val (activer = true, désactiver = false)
     * @param string $path représente l'endroit où stocker le fichier de log (répertoire courant par défaut)
     */
    public function set_log($val, $path = "") {
        $this->_utiliser_log = $val;
        if(!empty($path)) {
            $this->_path_log = $path;
        }
    }

    /**
     * @brief Permet d'activer l'affichage des erreurs
     * @param $val (activer = true, désactiver = false)
     */
    public function set_erreur($val) {
        $this->_afficher_erreur = $val;
    }

    /**
     * @brief Permet d'activer l'affichage des infos
     * @param $val (activer = true, désactiver = false)
     */
    public function set_info($val) {
        $this->_afficher_info = $val;
    }

    /**
     * @brief Renvoie les erreurs survenues
     * @return string
     */
    public function get_erreurs() {
        return $this->_err;
    }

    /**
     * @brief Renvoie les infos survenues
     * @return string
     */
    public function get_infos() {
        return $this->_info;
    }

    /**
     * @brief Affiche les erreurs survenues
     */
    public function afficher_err() {
        echo $this->_err;
    }

    /**
     * @brief Affiche les infos survenues
     */
    public function afficher_info() {
        echo $this->_info;
    }

    /**
     * @brief Ecrit dans le fichier de log
     */
    public function ecrire_log() {
        file_put_contents($this->_path_log, $this->_log, FILE_APPEND);
    }
}

?>