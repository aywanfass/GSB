<?php

/**
 * Fonctions pour l'application GSB
 *
 * PHP Version 8
 *
 * @category  PPE
 * @package   GSB
 * @author    Cheri Bibi - Réseau CERTA <contact@reseaucerta.org>
 * @author    José GIL <jgil@ac-nice.fr>
 * @copyright 2017 Réseau CERTA
 * @license   Réseau CERTA
 * @version   GIT: <0>
 * @link      http://www.php.net/manual/fr/book.pdo.php PHP Data Objects sur php.net
 */

namespace Outils;

/**
 * Classe Utilitaires : Boîte à outils de l'application
 * 
 * Cette classe contient uniquement des méthodes statiques qui aident au 
 * fonctionnement global : gestion des sessions, conversion de dates,
 * validations de formulaires et gestion des erreurs.
 */
abstract class Utilitaires
{
    /**
     * Vérifie si un utilisateur est actuellement connecté en session.
     * On regarde simplement si la clé 'idVisiteur' existe dans le tableau $_SESSION.
     *
     * @return bool Vrai si connecté, faux sinon
     */
    public static function estConnecte(): bool
    {
        return isset($_SESSION['idVisiteur']);
    }

    /**
     * Enregistre les informations de l'utilisateur dans la session PHP.
     * Ces informations sont ensuite accessibles sur toutes les pages.
     *
     * @param string $idVisiteur Identifiant unique de l'utilisateur
     * @param string $nom        Nom de famille
     * @param string $prenom     Prénom
     */
    public static function connecter($idVisiteur, $nom, $prenom): void
    {
        $_SESSION['idVisiteur'] = $idVisiteur;
        $_SESSION['nom'] = $nom;
        $_SESSION['prenom'] = $prenom;
    }

    /**
     * Détruit la session en cours lors de la déconnexion.
     */
    public static function deconnecter(): void
    {
        session_destroy();
    }

    /**
     * Transforme une date française (jj/mm/aaaa) vers le format SQL anglais (aaaa-mm-jj).
     * Cette conversion est essentielle car les bases de données SQL stockent généralement
     * les dates au format 'aaaa-mm-jj' pour faciliter les tris et les comparaisons.
     * 
     * @param string $maDate La date au format jj/mm/aaaa
     * @return string        La date au format aaaa-mm-jj
     */
    public static function dateFrancaisVersAnglais($maDate): string
    {
        // On sépare le jour, le mois et l'année en utilisant le slash comme séparateur
        @list($jour, $mois, $annee) = explode('/', $maDate);
        // Utilisation de sprintf pour formater la date et s'assurer que les mois/jours
        // sont sur deux chiffres et l'année sur quatre, comme attendu par SQL.
        return sprintf('%04d-%02d-%02d', $annee, $mois, $jour);
    }

    /**
     * Transforme une date SQL anglaise (aaaa-mm-jj) vers le format français (jj/mm/aaaa).
     * Utile pour l'affichage à l'utilisateur, qui est habitué au format français.
     * 
     * @param string $maDate La date au format aaaa-mm-jj
     * @return string        La date au format jj/mm/aaaa
     */
    public static function dateAnglaisVersFrancais($maDate): string
    {
        // On sépare l'année, le mois et le jour en utilisant le tiret
        @list($annee, $mois, $jour) = explode('-', $maDate);
        // Utilisation de sprintf pour formater la date dans l'ordre français.
        return sprintf('%02d/%02d/%04d', $jour, $mois, $annee);
    }

    /**
     * Extrait le mois et l'année d'une date française pour former la clé 'aaaamm'.
     * Ce format est souvent utilisé dans l'application GSB pour identifier
     * les fiches de frais par mois et année (ex: '27/03/2026' devient '202603').
     *
     * @param string $date La date au format jj/mm/aaaa
     * @return string      Le mois au format aaaamm
     */
    public static function getMois($date): string
    {
        @list($jour, $mois, $annee) = explode('/', $date);
        // Si le mois est sur un seul chiffre (ex: '3' pour mars), on ajoute un '0' devant.
        if (strlen($mois) == 1) {
            $mois = '0' . $mois;
        }
        return $annee . $mois;
    }

    /**
     * Retourne le libellé d'un mois en français
     *
     * @param Integer $numMois Numéro du mois (1 à 12)
     *
     * @return String Libellé du mois (ex: Janvier)
     */
    public static function getLibelleMois($numMois): string
    {
        $lesMois = array(
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        );
        return $lesMois[(int)$numMois];
    }

    /**
     * Indique si une valeur est un entier positif ou nul.
     * Utilisé pour valider les quantités de frais (ex: un nombre de repas).
     *
     * @param string $valeur La valeur à tester
     * @return bool Vrai si c'est un entier positif, faux sinon
     */
    public static function estEntierPositif($valeur): bool
    {
        return preg_match('/^[0-9]+$/', (string)$valeur);
    }

    /**
     * Indique si un tableau de valeurs contient uniquement des entiers positifs.
     * Utile pour vérifier tout un formulaire de frais forfaitaires d'un coup.
     *
     * @param array $lesValeurs Tableau de valeurs
     * @return bool Vrai si toutes les valeurs sont des entiers positifs
     */
    public static function estTableauEntiers($lesValeurs): bool
    {
        $resultat = true;
        foreach ($lesValeurs as $uneValeur) {
            if (!self::estEntierPositif($uneValeur)) {
                $resultat = false;
                break; // On s'arrête dès qu'une erreur est trouvée
            }
        }
        return $resultat;
    }

    /**
     * Vérifie si une date date de plus d'un an (Règle de gestion GSB).
     *
     * @param string $dateFrais Date au format jj/mm/aaaa
     * @return bool Vrai si périmée
     */
    public static function estDateDepassee($dateFrais): bool
    {
        $dateActuelle = date('d/m/Y');
        @list($jour, $mois, $annee) = explode('/', $dateActuelle);
        $anneeMoinsUn = $annee - 1;
        $unAnPlusTot = $anneeMoinsUn . $mois . $jour;
        
        @list($jourF, $moisF, $anneeF) = explode('/', $dateFrais);
        $dateAFester = $anneeF . $moisF . $jourF;
        
        return ($dateAFester < $unAnPlusTot);
    }

    /**
     * Vérifie si une date est valide (existe réellement dans le calendrier).
     *
     * @param string $date Date au format jj/mm/aaaa
     * @return bool Vrai si valide
     */
    public static function estDateValide($date): bool
    {
        $tabDate = explode('/', $date);
        $dateOK = true;
        if (count($tabDate) != 3) {
            $dateOK = false;
        } else {
            if (!self::estTableauEntiers($tabDate)) {
                $dateOK = false;
            } else {
                // checkdate vérifie la validité du jour, mois, année
                if (!checkdate((int)$tabDate[1], (int)$tabDate[0], (int)$tabDate[2])) {
                    $dateOK = false;
                }
            }
        }
        return $dateOK;
    }

    /**
     * Vérifie que le tableau de frais ne contient que des valeurs numériques
     *
     * @param array $lesFrais Tableau d'entier
     *
     * @return bool vrai ou faux
     */
    public static function lesQteFraisValides($lesFrais): bool
    {
        return self::estTableauEntiers($lesFrais);
    }

    /**
     * Vérifie la validité des trois arguments : la date, le libellé du frais
     * et le montant
     *
     * Des message d'erreurs sont ajoutés au tableau des erreurs
     *
     * @param String $dateFrais Date des frais
     * @param String $libelle   Libellé des frais
     * @param Float  $montant   Montant des frais
     *
     * @return null
     */
    public static function valideInfosFrais($dateFrais, $libelle, $montant): void
    {
        if ($dateFrais == '') {
            self::ajouterErreur('Le champ date ne doit pas être vide');
        } else {
            if (!self::estDatevalide($dateFrais)) {
                self::ajouterErreur('Date invalide');
            } else {
                if (self::estDateDepassee($dateFrais)) {
                    self::ajouterErreur("date d'enregistrement du frais dépassé, plus de 1 an");
                }
            }
        }
        if ($libelle == '') {
            self::ajouterErreur('Le champ description ne peut pas être vide');
        }
        if ($montant == '') {
            self::ajouterErreur('Le champ montant ne peut pas être vide');
        } elseif (!is_numeric($montant)) {
            self::ajouterErreur('Le champ montant doit être numérique');
        }
    }

    /**
     * Ajoute le libellé d'une erreur au tableau des erreurs
     *
     * @param String $msg Libellé de l'erreur
     *
     * @return null
     */
    public static function ajouterErreur($msg): void
    {
        if (!isset($_REQUEST['erreurs'])) {
            $_REQUEST['erreurs'] = array();
        }
        $_REQUEST['erreurs'][] = $msg;
    }

    /**
     * Retoune le nombre de lignes du tableau des erreurs
     *
     * @return Integer le nombre d'erreurs
     */
    public static function nbErreurs(): int
    {
        if (!isset($_REQUEST['erreurs'])) {
            return 0;
        } else {
            return count($_REQUEST['erreurs']);
        }
    }
    
     public static function setRole(?string $roleId): void
    {
        $_SESSION['role'] = $roleId;
    }

    public static function getRole(): ?string
    {
        return $_SESSION['role'] ?? null;
    }

    public static function hasRole(string $roleId): bool
    {
        return self::getRole() === $roleId;
    }

    /**
     * Exige un ou plusieurs rôles; 403 si non autorisé.
     */
    public static function exigerRole(string|array $roles): void
    {
        $roles = (array)$roles;
        if (!in_array(self::getRole(), $roles, true)) {
            header('HTTP/1.1 403 Forbidden');
            echo 'Accès refusé';
            exit;
        }
    }

}
