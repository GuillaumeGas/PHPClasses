<?php

/**
 * Class Matrice permettant d'effectuer des calculs matriciels
 */
class Matrice {

    public static function prod($mat1, $mat2) {
        if(!(count($mat1[0]) == count($mat2))) {
            echo "Erreur, impossible de realiser le produit de ces deux matrices.<br>";
            exit(0);
        } else {
            /* INIT MATRICE RESULTAT */
            $res = array();
            $colRes = 0;
            $linRes = 0;
            for($i = 0; $i < count($mat1); $i++) {
                for($j = 0; $j < count($mat2[0]); $j++) {
                    $res[$i][$j] = 0;
                }
            }

            //pour chaque ligne de A (mat1)
            for($linA = 0; $linA < count($mat1); $linA++) {
                //pour chaque colonne de B (mat2)
                for($colB = 0; $colB < count($mat2[0]); $colB++) {
                    //pour chaque colonne de A
                    $linB = 0;
                    for($colA = 0; $colA < count($mat1[0]); $colA++) {
                        $res[$linRes][$colRes] += $mat1[$linA][$colA] * $mat2[$linB][$colB];
                        $linB++;
                    }
                    $colRes++;
                }
                $linRes++;
                $colRes = 0;
            }

            show_mat($res);
            return $res;
        }
    }

    public static function show($mat) {
        for($i = 0; $i < count($mat); $i++) {
            for($j = 0; $j < count($mat[0]); $j++) {
                echo '['.round($mat[$i][$j], 2).']';
                echo '  ';
            }
            echo '<br>';
        }
    }

}

?>