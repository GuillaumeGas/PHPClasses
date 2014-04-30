<?php

class Log {

    private $_log;
    private $_err;
    private $_info;
    private $_show_err;
    private $_show_info;
    private $_path_log;

    public function __construct($p_path) {

        if(session_status() == PHP_SESSION_DISABLED) {
            session_start();
        }

        $this->_path_log = $p_path;
        $this->_log  = "";
        $this->_err  = "";
        $this->_info = "";
        $this->_show_err  = false;
        $this->_show_info = false;

        if(isset($_SESSION['log_started'])) {
            if(!$_SESSION['log_started']) {
                $this->add_info_log("Initialisation fichier de log");
                $this->write_log();
                $_SESSION['log_started'] = true;
            }
        } else {
            $this->add_info_log("Initialisation fichier de log");
            $this->write_log();
            $_SESSION['log_started'] = true;
        }

    }

    public function close() {
        unset($_SESSION['log_started']);
    }

    public function delete_file() {
        @unlink($this->_path_log);
    }

    /**
     * @brief Ajout le message d'erreur aux variables concernées (err et log)
     * @param $err Représente le message d'erreur
     */
    public function add_err_log($err) {
        $this->_err .= $err."<br>";
        $this->_log .= date("[j/m/y H:i:s]")." - ".$err."\r\n";

        $this->write_log();
    }

    /**
     * @brief Ajout le message d'info aux variables concernées (info et log)
     * @param $err Représente le message d'info
     */
    public function add_info_log($info) {
        $this->_info .= $info."<br>";
        $this->_log .= date("[j/m/y H:i:s]")." - ".$info."\r\n";

        $this->write_log();
    }

    /**
     * @brief Permet d'activer l'affichage des erreurs
     * @param $val (activer = true, désactiver = false)
     */
    public function set_show_err($val) {
        $this->_show_err = $val;
    }

    /**
     * @brief Permet d'activer l'affichage des infos
     * @param $val (activer = true, désactiver = false)
     */
    public function set_show_info($val) {
        $this->_show_info = $val;
    }

    /**
     * @brief Renvoie les erreurs survenues
     * @return string
     */
    public function get_err() {
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
    public function show_err() {
        echo $this->_err;
    }

    /**
     * @brief Affiche les infos survenues
     */
    public function show_info() {
        echo $this->_info;
    }

    /**
     * @brief Ecrit dans le fichier de log
     */
    public function write_log() {
        file_put_contents($this->_path_log, $this->_log, FILE_APPEND);
        $this->_log = "";
    }
}

?>