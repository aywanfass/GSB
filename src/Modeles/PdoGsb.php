<?php

/**
 * Classe d'accès aux données.
 *
 * PHP Version 8
 *
 * @category  PPE
 * @package   GSB
 * @author    Cheri Bibi - Réseau CERTA <contact@reseaucerta.org>
 * @author    José GIL - CNED <jgil@ac-nice.fr>
 * @copyright 2017 Réseau CERTA
 * @license   Réseau CERTA
 * @version   GIT: <0>
 * @link      http://www.php.net/manual/fr/book.pdo.php PHP Data Objects sur php.net
 */

/**
 * Classe d'accès aux données.
 *
 * Utilise les services de la classe PDO
 * pour l'application GSB
 * Les attributs sont tous statiques,
 * les 4 premiers pour la connexion
 * $connexion de type PDO
 * $instance qui contiendra l'unique instance de la classe
 *
 * PHP Version 8
 *
 * @category  PPE
 * @package   GSB
 * @author    Cheri Bibi - Réseau CERTA <contact@reseaucerta.org>
 * @author    José GIL <jgil@ac-nice.fr>
 * @copyright 2017 Réseau CERTA
 * @license   Réseau CERTA
 * @version   Release: 1.0
 * @link      http://www.php.net/manual/fr/book.pdo.php PHP Data Objects sur php.net
 */

namespace Modeles;

use PDO;
use Outils\Utilitaires;
use App\Services\IndemniteKmService;

require '../config/bdd.php';

class PdoGsb
{
    protected $connexion;
    private static $instance = null;

    /**
     * Constructeur privé, crée l'instance de PDO qui sera sollicitée
     * pour toutes les méthodes de la classe
     */
    private function __construct()
    {
        $this->connexion = new PDO(DB_DSN, DB_USER, DB_PWD);
        $this->connexion->query('SET CHARACTER SET utf8');
    }

    /**
     * Méthode destructeur appelée dès qu'il n'y a plus de référence sur un
     * objet donné, ou dans n'importe quel ordre pendant la séquence d'arrêt.
     */
    public function __destruct()
    {
        $this->connexion = null;
    }

    /**
     * Fonction statique qui crée l'unique instance de la classe
     * Appel : $instancePdoGsb = PdoGsb::getPdoGsb();
     *
     * @return l'unique objet de la classe PdoGsb
     */
    public static function getPdoGsb(): PdoGsb
    {
        if (self::$instance == null) {
            self::$instance = new PdoGsb();
        }
        return self::$instance;
    }

    /**
     * Authentifie et retourne les informations d'un visiteur.
     *
     * Si la colonne password_hash est présente: compare SHA-256; sinon fallback legacy (mdp en clair).
     *
     * @param string $login Login du visiteur
     * @return array|false  Tableau ['id','nom','prenom'] si authentifié, False sinon
     */
    public function getInfosVisiteur($login): array
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT v.id AS id, v.nom AS nom, v.prenom AS prenom, '
            . 'v.id_role AS id_role, r.libelle AS roleLibelle '
            . 'FROM visiteur v '
            . 'JOIN role r ON r.id = v.id_role '
            . 'WHERE v.login = :unLogin'
        );
        $requetePrepare->bindParam(':unLogin', $login, PDO::PARAM_STR);
        $requetePrepare->execute();

        $row = $requetePrepare->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return [];
        }

        // Normalisation des valeurs renvoyées
        $row['id_role'] = isset($row['id_role']) ? strtoupper(trim((string)$row['id_role'])) : null;
        $row['roleLibelle'] = isset($row['roleLibelle']) ? trim((string)$row['roleLibelle']) : null;

        return [
            'id' => $row['id'],
            'nom' => $row['nom'],
            'prenom' => $row['prenom'],
            'id_role' => $row['id_role'],
            'roleLibelle' => $row['roleLibelle']
        ];
    }
    
    public function getMdpVisiteur($login) 
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT mdp '
            . 'FROM visiteur '
            . 'WHERE visiteur.login = :unLogin'
        );
        $requetePrepare->bindParam(':unLogin', $login, PDO::PARAM_STR);
        $requetePrepare->execute();
        return $requetePrepare->fetch(PDO::FETCH_OBJ)->mdp;
    }

    /**
     * Retourne sous forme d'un tableau associatif toutes les lignes de frais
     * hors forfait concernées par les deux arguments.
     * La boucle foreach ne peut être utilisée ici car on procède
     * à une modification de la structure itérée - transformation du champ date-
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return tous les champs des lignes de frais hors forfait sous la forme
     * d'un tableau associatif
     */
    public function getLesFraisHorsForfait($idVisiteur, $mois): array
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT * FROM lignefraishorsforfait '
            . 'WHERE lignefraishorsforfait.idvisiteur = :unIdVisiteur '
            . 'AND lignefraishorsforfait.mois = :unMois'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        $lesLignes = $requetePrepare->fetchAll();
        $nbLignes = count($lesLignes);
        for ($i = 0; $i < $nbLignes; $i++) {
            $date = $lesLignes[$i]['date'];
            $lesLignes[$i]['date'] = Utilitaires::dateAnglaisVersFrancais($date);
        }
        return $lesLignes;
    }

    /**
     * Retourne le nombre de justificatif d'un visiteur pour un mois donné
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return le nombre entier de justificatifs
     */
    public function getNbjustificatifs($idVisiteur, $mois): int
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT fichefrais.nbjustificatifs as nb FROM fichefrais '
            . 'WHERE fichefrais.idvisiteur = :unIdVisiteur '
            . 'AND fichefrais.mois = :unMois'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        $laLigne = $requetePrepare->fetch();
        return $laLigne['nb'];
    }

    /**
     * Retourne sous forme d'un tableau associatif toutes les lignes de frais
     * au forfait concernées par les deux arguments
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return l'id, le libelle et la quantité sous la forme d'un tableau
     * associatif
     */
    public function getLesFraisForfait($idVisiteur, $mois): array
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT fraisforfait.id as idfrais, '
            . 'fraisforfait.libelle as libelle, '
            . 'fraisforfait.montant as montant, '
            . 'lignefraisforfait.quantite as quantite '
            . 'FROM lignefraisforfait '
            . 'INNER JOIN fraisforfait '
            . 'ON fraisforfait.id = lignefraisforfait.idfraisforfait '
            . 'WHERE lignefraisforfait.idvisiteur = :unIdVisiteur '
            . 'AND lignefraisforfait.mois = :unMois '
            . 'ORDER BY lignefraisforfait.idfraisforfait'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        return $requetePrepare->fetchAll();
    }

    /**
     * Retourne tous les id de la table FraisForfait
     *
     * @return un tableau associatif
     */
    public function getLesIdFrais(): array
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT fraisforfait.id as idfrais '
            . 'FROM fraisforfait ORDER BY fraisforfait.id'
        );
        $requetePrepare->execute();
        return $requetePrepare->fetchAll();
    }

    /**
     * Met à jour la table ligneFraisForfait
     * Met à jour la table ligneFraisForfait pour un visiteur et
     * un mois donné en enregistrant les nouveaux montants
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     * @param Array  $lesFrais   tableau associatif de clé idFrais et
     *                           de valeur la quantité pour ce frais
     *
     * @return null
     */
    public function majFraisForfait($idVisiteur, $mois, $lesFrais): void
    {
        $lesCles = array_keys($lesFrais);
        foreach ($lesCles as $unIdFrais) {
            $qte = $lesFrais[$unIdFrais];
            $requetePrepare = $this->connexion->prepare(
                'UPDATE lignefraisforfait '
                . 'SET lignefraisforfait.quantite = :uneQte '
                . 'WHERE lignefraisforfait.idvisiteur = :unIdVisiteur '
                . 'AND lignefraisforfait.mois = :unMois '
                . 'AND lignefraisforfait.idfraisforfait = :idFrais'
            );
            $requetePrepare->bindParam(':uneQte', $qte, PDO::PARAM_INT);
            $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
            $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
            $requetePrepare->bindParam(':idFrais', $unIdFrais, PDO::PARAM_STR);
            $requetePrepare->execute();
        }
    }

    /**
     * Met à jour le nombre de justificatifs de la table ficheFrais
     * pour le mois et le visiteur concerné
     *
     * @param String  $idVisiteur      ID du visiteur
     * @param String  $mois            Mois sous la forme aaaamm
     * @param Integer $nbJustificatifs Nombre de justificatifs
     *
     * @return null
     */
    public function majNbJustificatifs($idVisiteur, $mois, $nbJustificatifs): void
    {
        $requetePrepare = $this->connexion->prepare(
            'UPDATE fichefrais '
            . 'SET nbjustificatifs = :unNbJustificatifs '
            . 'WHERE fichefrais.idvisiteur = :unIdVisiteur '
            . 'AND fichefrais.mois = :unMois'
        );
        $requetePrepare->bindParam(
            ':unNbJustificatifs',
            $nbJustificatifs,
            PDO::PARAM_INT
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
    }

    /**
     * Teste si un visiteur possède une fiche de frais pour le mois passé en argument
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return vrai ou faux
     */
    public function estPremierFraisMois($idVisiteur, $mois): bool
    {
        $boolReturn = false;
        $requetePrepare = $this->connexion->prepare(
            'SELECT fichefrais.mois FROM fichefrais '
            . 'WHERE fichefrais.mois = :unMois '
            . 'AND fichefrais.idvisiteur = :unIdVisiteur'
        );
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->execute();
        if (!$requetePrepare->fetch()) {
            $boolReturn = true;
        }
        return $boolReturn;
    }

    /**
     * Retourne le dernier mois en cours d'un visiteur
     *
     * @param String $idVisiteur ID du visiteur
     *
     * @return le mois sous la forme aaaamm
     */
    public function dernierMoisSaisi($idVisiteur): string
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT MAX(mois) as dernierMois '
            . 'FROM fichefrais '
            . 'WHERE fichefrais.idvisiteur = :unIdVisiteur'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->execute();
        $laLigne = $requetePrepare->fetch();
        $dernierMois = $laLigne['dernierMois'];
        return $dernierMois;
    }

    /**
     * Crée une nouvelle fiche de frais et les lignes de frais au forfait
     * pour un visiteur et un mois donnés
     *
     * Récupère le dernier mois en cours de traitement, met à 'CL' son champs
     * idEtat, crée une nouvelle fiche de frais avec un idEtat à 'CR' et crée
     * les lignes de frais forfait de quantités nulles
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return null
     */
    public function creeNouvellesLignesFrais($idVisiteur, $mois): void
    {
        $dernierMois = $this->dernierMoisSaisi($idVisiteur);
        $laDerniereFiche = $this->getLesInfosFicheFrais($idVisiteur, $dernierMois);
        if ($laDerniereFiche['idEtat'] == 'CR') {
            $this->majEtatFicheFrais($idVisiteur, $dernierMois, 'CL');
        }
        $requetePrepare = $this->connexion->prepare(
            'INSERT INTO fichefrais (idvisiteur,mois,nbjustificatifs,'
            . 'montantvalide,datemodif,idetat) '
            . "VALUES (:unIdVisiteur,:unMois,0,0,now(),'CR')"
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        $lesIdFrais = $this->getLesIdFrais();
        foreach ($lesIdFrais as $unIdFrais) {
            $requetePrepare = $this->connexion->prepare(
                'INSERT INTO lignefraisforfait (idvisiteur,mois,'
                . 'idfraisforfait,quantite) '
                . 'VALUES(:unIdVisiteur, :unMois, :idFrais, 0)'
            );
            $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
            $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
            $requetePrepare->bindParam(':idFrais', $unIdFrais['idfrais'], PDO::PARAM_STR);
            $requetePrepare->execute();
        }
    }

    /**
     * Crée un nouveau frais hors forfait pour un visiteur un mois donné
     * à partir des informations fournies en paramètre
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     * @param String $libelle    Libellé du frais
     * @param String $date       Date du frais au format français jj//mm/aaaa
     * @param Float  $montant    Montant du frais
     *
     * @return null
     */
    public function creeNouveauFraisHorsForfait($idVisiteur, $mois, $libelle, $date, $montant): void
    {
        $dateFr = Utilitaires::dateFrancaisVersAnglais($date);
        $requetePrepare = $this->connexion->prepare(
            'INSERT INTO lignefraishorsforfait '
            . 'VALUES (null, :unIdVisiteur,:unMois, :unLibelle, :uneDateFr,'
            . ':unMontant) '
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unLibelle', $libelle, PDO::PARAM_STR);
        $requetePrepare->bindParam(':uneDateFr', $dateFr, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMontant', $montant, PDO::PARAM_INT);
        $requetePrepare->execute();
    }

    /**
     * Marque une ligne hors-forfait comme refusée en préfixant son libellé par 'REFUSE '.
     * Opération idempotente: ne double pas le préfixe si déjà présent.
     *
     * @param string $idFrais Identifiant de la ligne hors-forfait
     * @return void
     */
    public function refuserFraisHorsForfait($idFrais): void
    {
        $requetePrepare = $this->connexion->prepare(
            "UPDATE lignefraishorsforfait SET libelle = CONCAT('REFUSE ', libelle) "
            . "WHERE id = :unIdFrais AND libelle NOT LIKE 'REFUSE %'"
        );
        $requetePrepare->bindParam(':unIdFrais', $idFrais, PDO::PARAM_STR);
        $requetePrepare->execute();
    }

    /**
     * Supprime le frais hors forfait dont l'id est passé en argument
     *
     * @param String $idFrais ID du frais
     *
     * @return null
     */
    public function supprimerFraisHorsForfait($idFrais): void
    {
        $requetePrepare = $this->connexion->prepare(
            'DELETE FROM lignefraishorsforfait '
            . 'WHERE lignefraishorsforfait.id = :unIdFrais'
        );
        $requetePrepare->bindParam(':unIdFrais', $idFrais, PDO::PARAM_STR);
        $requetePrepare->execute();
    }

    /**
     * Retourne la liste de tous les visiteurs triés par nom puis prénom.
     *
     * @return array Liste des visiteurs [id, nom, prenom]
     */
    public function getTousVisiteurs(): array
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT id, nom, prenom FROM visiteur ORDER BY nom, prenom'
        );
        $requetePrepare->execute();
        return $requetePrepare->fetchAll();
    }

    /**
     * Retourne l'identité d'un visiteur par son identifiant.
     *
     * @param string $id Identifiant du visiteur
     * @return array     [id, nom, prenom]
     */
    public function getVisiteur($id): array
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT id, nom, prenom FROM visiteur WHERE id = :id LIMIT 1'
        );
        $requetePrepare->bindParam(':id', $id, PDO::PARAM_STR);
        $requetePrepare->execute();
        return $requetePrepare->fetch();
    }

    /**
     * Calcule le montant total d'une fiche (forfait + hors-forfait, hors REFUSE).
     * Si le barème kilométrique est activé, remplace le calcul de la ligne 'KM'
     * par le service d'indemnités (sinon fallback à l'unitaire de la BDD).
     *
     * @param string $idVisiteur Identifiant du visiteur
     * @param string $mois       Mois au format aaaamm
     * @return float             Montant total calculé
     */
    public function calculerMontantFiche($idVisiteur, $mois): float
    {
        $useKm = IndemniteKmService::isEnabled();
        $puissance = IndemniteKmService::getDefaultPuissance();
        $reqLignes = $this->connexion->prepare(
            'SELECT lff.idfraisforfait AS idf, lff.quantite AS qte, ff.montant AS unit '
            . 'FROM lignefraisforfait lff '
            . 'INNER JOIN fraisforfait ff ON ff.id = lff.idfraisforfait '
            . 'WHERE lff.idvisiteur = :v AND lff.mois = :m'
        );
        $reqLignes->bindParam(':v', $idVisiteur, PDO::PARAM_STR);
        $reqLignes->bindParam(':m', $mois, PDO::PARAM_STR);
        $reqLignes->execute();
        $totalForfait = 0.0;
        while ($row = $reqLignes->fetch()) {
            $idf = isset($row['idf']) ? (string)$row['idf'] : '';
            $qte = isset($row['qte']) ? (int)$row['qte'] : 0;
            $unit = isset($row['unit']) ? (float)$row['unit'] : 0.0;
            if ($idf === 'KM' && $useKm && $puissance !== null) {
                $totalForfait += IndemniteKmService::computeMontant($qte, (int)$puissance, $unit);
            } else {
                $totalForfait += $qte * $unit;
            }
        }

        $reqHF = $this->connexion->prepare(
            "SELECT SUM(montant) AS totalHF FROM lignefraishorsforfait "
            . "WHERE idvisiteur = :v AND mois = :m AND libelle NOT LIKE 'REFUSE %'"
        );
        $reqHF->bindParam(':v', $idVisiteur, PDO::PARAM_STR);
        $reqHF->bindParam(':m', $mois, PDO::PARAM_STR);
        $reqHF->execute();
        $rowH = $reqHF->fetch();
        $totalHF = $rowH && isset($rowH['totalHF']) ? (float)$rowH['totalHF'] : 0.0;

        return $totalForfait + $totalHF;
    }

    /**
     * Met à jour le montant validé d'une fiche donnée.
     *
     * @param string     $idVisiteur Identifiant du visiteur
     * @param string     $mois       Mois au format aaaamm
     * @param float|int  $montant    Montant validé à enregistrer
     * @return void
     */
    public function setMontantValide($idVisiteur, $mois, $montant): void
    {
        $requetePrepare = $this->connexion->prepare(
            'UPDATE fichefrais SET montantvalide = :montant '
            . 'WHERE idvisiteur = :v AND mois = :m'
        );
        $requetePrepare->bindParam(':montant', $montant);
        $requetePrepare->bindParam(':v', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':m', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
    }

    /**
     * Retourne les mois pour lesquel un visiteur a une fiche de frais
     *
     * @param String $idVisiteur ID du visiteur
     *
     * @return un tableau associatif de clé un mois -aaaamm- et de valeurs
     *         l'année et le mois correspondant
     */
    public function getLesMoisDisponibles($idVisiteur): array
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT fichefrais.mois AS mois FROM fichefrais '
            . 'WHERE fichefrais.idvisiteur = :unIdVisiteur '
            . 'ORDER BY fichefrais.mois desc'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->execute();
        $lesMois = array();
        while ($laLigne = $requetePrepare->fetch()) {
            $mois = $laLigne['mois'];
            $numAnnee = substr($mois, 0, 4);
            $numMois = substr($mois, 4, 2);
            $lesMois[] = array(
                'mois' => $mois,
                'numAnnee' => $numAnnee,
                'numMois' => $numMois
            );
        }
        return $lesMois;
    }

    /**
     * Retourne les informations d'une fiche de frais d'un visiteur pour un
     * mois donné
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     *
     * @return un tableau avec des champs de jointure entre une fiche de frais
     *         et la ligne d'état
     */
    public function getLesInfosFicheFrais($idVisiteur, $mois): array
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT fichefrais.idetat as idEtat, '
            . 'fichefrais.datemodif as dateModif,'
            . 'fichefrais.nbjustificatifs as nbJustificatifs, '
            . 'fichefrais.montantvalide as montantValide, '
            . 'etat.libelle as libEtat '
            . 'FROM fichefrais '
            . 'INNER JOIN etat ON fichefrais.idetat = etat.id '
            . 'WHERE fichefrais.idvisiteur = :unIdVisiteur '
            . 'AND fichefrais.mois = :unMois'
        );
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        $laLigne = $requetePrepare->fetch();
        return $laLigne;
    }

    /**
     * Modifie l'état et la date de modification d'une fiche de frais.
     * Modifie le champ idEtat et met la date de modif à aujourd'hui.
     *
     * @param String $idVisiteur ID du visiteur
     * @param String $mois       Mois sous la forme aaaamm
     * @param String $etat       Nouvel état de la fiche de frais
     *
     * @return null
     */
    public function majEtatFicheFrais($idVisiteur, $mois, $etat): void
    {
        $requetePrepare = $this->connexion->prepare(
            'UPDATE fichefrais '
            . 'SET idetat = :unEtat, datemodif = now() '
            . 'WHERE fichefrais.idvisiteur = :unIdVisiteur '
            . 'AND fichefrais.mois = :unMois'
        );
        $requetePrepare->bindParam(':unEtat', $etat, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unIdVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':unMois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
    }

    /**
     * Retourne les fiches VALIDÉES (VA), optionnellement filtrées par visiteur
     */
    public function getFichesValidees($idVisiteur = null): array
    {
        if ($idVisiteur) {
            $sql = 'SELECT f.idvisiteur, f.mois, f.nbjustificatifs, f.montantvalide, f.datemodif, '
                . 'v.nom, v.prenom '
                . 'FROM fichefrais f INNER JOIN visiteur v ON v.id = f.idvisiteur '
                . "WHERE f.idetat = 'VA' AND f.idvisiteur = :v ORDER BY f.mois DESC, v.nom, v.prenom";
            $stmt = $this->connexion->prepare($sql);
            $stmt->bindParam(':v', $idVisiteur, PDO::PARAM_STR);
        } else {
            $sql = 'SELECT f.idvisiteur, f.mois, f.nbjustificatifs, f.montantvalide, f.datemodif, '
                . 'v.nom, v.prenom '
                . 'FROM fichefrais f INNER JOIN visiteur v ON v.id = f.idvisiteur '
                . "WHERE f.idetat = 'VA' ORDER BY f.mois DESC, v.nom, v.prenom";
            $stmt = $this->connexion->prepare($sql);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Rembourse une fiche (passe à RB) et enregistre les infos de paiement si disponibles
     */
    public function rembourserFiche($idVisiteur, $mois, $datePaiement = null, $refPaiement = null): void
    {
        if ($this->hasPaiementColumns()) {
            $stmt = $this->connexion->prepare(
                'UPDATE fichefrais SET idetat = :e, datemodif = now(), '
                . 'date_paiement = :dp, ref_paiement = :rp '
                . 'WHERE idvisiteur = :v AND mois = :m'
            );
            $etat = 'RB';
            $stmt->bindParam(':e', $etat, PDO::PARAM_STR);
            $stmt->bindParam(':dp', $datePaiement, PDO::PARAM_STR);
            $stmt->bindParam(':rp', $refPaiement, PDO::PARAM_STR);
            $stmt->bindParam(':v', $idVisiteur, PDO::PARAM_STR);
            $stmt->bindParam(':m', $mois, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $this->majEtatFicheFrais($idVisiteur, $mois, 'RB');
        }
    }

    /**
     * Statistiques annuelles des remboursements (somme des montants des fiches RB par mois)
     */
    public function getStatsRemboursements($annee): array
    {
        $stmt = $this->connexion->prepare(
            "SELECT mois, SUM(montantvalide) AS total FROM fichefrais "
            . "WHERE idetat = 'RB' AND SUBSTRING(mois,1,4) = :a GROUP BY mois ORDER BY mois"
        );
        $stmt->bindParam(':a', $annee, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}