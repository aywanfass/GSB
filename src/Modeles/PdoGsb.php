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
     * CONSTRUCTEUR PRIVE
     * On ne peut pas instancier cette classe directement avec 'new'.
     * C'est une règle du design pattern SINGLETON.
     * Le constructeur crée la connexion à la base de données via l'objet PDO.
     */
    private function __construct()
    {
        // On utilise les constantes définies dans config/bdd.php
        $this->connexion = new PDO(DB_DSN, DB_USER, DB_PWD);
        
        // On force l'encodage en UTF-8 pour éviter les problèmes d'accents
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
     * Methode statique qui retourne l'unique instance de la classe (Singleton).
     * Si l'instance n'existe pas encore, elle est créée. 
     * Sinon, on retourne l'instance déjà existante.
     * 
     * Appel : $monPdo = PdoGsb::getPdoGsb();
     *
     * @return PdoGsb L'unique objet de la classe
     * @return PdoGsb L'unique objet de la classe PdoGsb.
     */
    public static function getPdoGsb(): PdoGsb
    {
        if (self::$instance == null) {
            self::$instance = new PdoGsb();
        }
        return self::$instance;
    }

    /**
     * Authentifie un utilisateur à partir de son login.
     * Cette méthode récupère les infos de base ainsi que son rôle (Visiteur ou Comptable).
     *
     * @param string $login Le login saisi par l'utilisateur
     * @return array        Tableau des infos [id, nom, prenom, id_role, roleLibelle]
     */
    public function getInfosVisiteur($login): array
    {
        // Utilisation d'une requête préparée pour la sécurité (contre les injections SQL).
        // Les requêtes préparées séparent la structure de la requête des données,
        // empêchant ainsi l'exécution de code malveillant inséré dans les paramètres.
        $requetePrepare = $this->connexion->prepare(
            'SELECT v.id AS id, v.nom AS nom, v.prenom AS prenom, '
            . 'v.id_role AS id_role, r.libelle AS roleLibelle '
            . 'FROM visiteur v '
            . 'JOIN role r ON r.id = v.id_role '
            . 'WHERE v.login = :unLogin'
        );
        
        // On lie le paramètre nommé ':unLogin' de la requête préparée à la variable $login.
        // PDO::PARAM_STR indique que la valeur est une chaîne de caractères,
        // ce qui renforce la sécurité et l'intégrité des données.
        $requetePrepare->bindParam(':unLogin', $login, PDO::PARAM_STR);
        $requetePrepare->execute();

        // On récupère le résultat sous forme de tableau associatif.
        // Si aucun enregistrement n'est trouvé, $ligne sera false.
        $ligne = $requetePrepare->fetch(PDO::FETCH_ASSOC);
        
        if (!$ligne) {
            // Si aucun visiteur n'est trouvé avec ce login, on retourne un tableau vide.
            return [];
        }

        // Nettoyage et normalisation des données avant de les retourner.
        // Par exemple, l'ID du rôle est converti en majuscules et les espaces superflus sont supprimés.
        $idRole = isset($ligne['id_role']) ? strtoupper(trim((string)$ligne['id_role'])) : null;
        $libelleRole = isset($ligne['roleLibelle']) ? trim((string)$ligne['roleLibelle']) : null;

        // Retourne un tableau associatif contenant les informations de l'utilisateur.
        return [
            'id' => $ligne['id'],
            'nom' => $ligne['nom'],
            'prenom' => $ligne['prenom'],
            'id_role' => $idRole,
            'roleLibelle' => $libelleRole
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
     * hors forfait pour un visiteur et un mois donnés.
     * On convertit la date du format SQL (aaaa-mm-jj) vers le format français (jj/mm/aaaa).
     *
     * @param string $idVisiteur ID du visiteur
     * @param string $mois       Mois sous la forme aaaamm
     * @return array             Tableau des lignes hors forfait
     */
    public function getLesFraisHorsForfait($idVisiteur, $mois): array
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT * FROM lignefraishorsforfait '
            . 'WHERE lignefraishorsforfait.idvisiteur = :idVisiteur '
            . 'AND lignefraishorsforfait.mois = :mois '
            . 'ORDER BY lignefraishorsforfait.date DESC'
        );
        $requetePrepare->bindParam(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':mois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        
        $lesLignes = $requetePrepare->fetchAll();
        $nbLignes = count($lesLignes);
        
        // On parcourt les lignes pour transformer la date en format français
        for ($i = 0; $i < $nbLignes; $i++) {
            $date = $lesLignes[$i]['date'];
            $lesLignes[$i]['date'] = Utilitaires::dateAnglaisVersFrancais($date);
        }
        
        return $lesLignes;
    }

    /**
     * Retourne le nombre de justificatif d'un visiteur pour un mois donné
     *
     * @param string $idVisiteur ID du visiteur
     * @param string $mois       Mois sous la forme aaaamm
     *
     * @return int le nombre entier de justificatifs
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
     * Retourne sous forme d'un tableau associatif les frais forfaitisés
     * (Repas, Nuitée, Étape, KM) d'un visiteur pour un mois donné.
     *
     * @param string $idVisiteur ID du visiteur
     * @param string $mois       Mois sous la forme aaaamm
     * @return array             Tableau des frais forfaitisés
     */
    public function getLesFraisForfait($idVisiteur, $mois): array
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT fraisforfait.id AS idfrais, fraisforfait.libelle AS libelle, '
            . 'fraisforfait.montant as montant, '
            . 'lignefraisforfait.quantite AS quantite '
            . 'FROM lignefraisforfait '
            . 'INNER JOIN fraisforfait '
            . 'ON fraisforfait.id = lignefraisforfait.idfraisforfait '
            . 'WHERE lignefraisforfait.idvisiteur = :idVisiteur '
            . 'AND lignefraisforfait.mois = :mois '
            . 'ORDER BY lignefraisforfait.idfraisforfait'
        );
        $requetePrepare->bindParam(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
        $requetePrepare->bindParam(':mois', $mois, PDO::PARAM_STR);
        $requetePrepare->execute();
        
        return $requetePrepare->fetchAll();
    }

    /**
     * Retourne tous les id de la table FraisForfait
     *
     * @return array un tableau associatif
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
     * @param string $idVisiteur ID du visiteur
     * @param string $mois       Mois sous la forme aaaamm
     * @param array  $lesFrais   tableau associatif de clé idFrais et
     *                           de valeur la quantité pour ce frais
     *
     * @return void
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
     * @param string  $idVisiteur      ID du visiteur
     * @param string  $mois            Mois sous la forme aaaamm
     * @param int $nbJustificatifs Nombre de justificatifs
     *
     * @return void
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
     * @param string $idVisiteur ID du visiteur
     * @param string $mois       Mois sous la forme aaaamm
     *
     * @return bool vrai ou faux
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
     * @param string $idVisiteur ID du visiteur
     *
     * @return string le mois sous la forme aaaamm
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
     * @param string $idVisiteur ID du visiteur
     * @param string $mois       Mois sous la forme aaaamm
     *
     * @return void
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
     * @param string $idVisiteur ID du visiteur
     * @param string $mois       Mois sous la forme aaaamm
     * @param string $libelle    Libellé du frais
     * @param string $date       Date du frais au format français jj//mm/aaaa
     * @param float  $montant    Montant du frais
     *
     * @return void
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
     * @param string $idFrais ID du frais
     *
     * @return void
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
     * Récupère les informations d'un visiteur à partir de son ID.
     * Cette fonction est utilisée pour l'affichage du nom/prénom et
     * pour connaître la puissance fiscale de son véhicule pour les KM.
     *
     * @param string $id Identifiant unique du visiteur
     * @return array     Tableau contenant [id, nom, prenom, puissance]
     */
    public function getVisiteur($id): array
    {
        // On prépare la requête SQL pour éviter les injections
        $requetePrepare = $this->connexion->prepare(
            'SELECT id, nom, prenom, puissance FROM visiteur WHERE id = :id LIMIT 1'
        );
        $requetePrepare->bindParam(':id', $id, PDO::PARAM_STR);
        $requetePrepare->execute();

        // On retourne le résultat sous forme de tableau associatif
        return $requetePrepare->fetch();
    }

    /**
     * Calcule le montant total d'une fiche de frais pour un visiteur et un mois donnés.
     * Somme les frais forfaitisés (en appliquant le barème KM si activé) 
     * et les frais hors-forfait (en excluant ceux qui ont été refusés).
     *
     * @param string $idVisiteur Identifiant du visiteur
     * @param string $mois       Mois au format aaaamm
     * @return float             Montant total calculé en euros
     */
    public function calculerMontantFiche($idVisiteur, $mois): float
    {
        // On vérifie si le calcul spécial des indemnités kilométriques est actif
        $useKm = IndemniteKmService::isEnabled();

        // On récupère la puissance fiscale du véhicule du visiteur
        // Si elle n'est pas renseignée en BDD, on prend celle par défaut du service
        $requetePuissance = $this->connexion->prepare(
            'SELECT puissance FROM visiteur WHERE id = :id'
        );
        $requetePuissance->bindParam(':id', $idVisiteur, PDO::PARAM_STR);
        $requetePuissance->execute();
        $lignePuissance = $requetePuissance->fetch();

        if ($lignePuissance && !empty($lignePuissance['puissance'])) {
            $puissanceVisible = $lignePuissance['puissance'];
        } else {
            $puissanceVisible = IndemniteKmService::getDefaultPuissance();
        }

        // 3. Calcul de la partie FORFAIT (Repas, Nuitée, Étape, KM)
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
            $idFraisForfait = (string) $row['idf'];
            $quantite = (int) $row['qte'];
            $prixUnitaire = (float) $row['unit'];

            // Règle de gestion : si c'est un frais KM, on utilise le barème de puissance
            if ($idFraisForfait === 'KM' && $useKm) {
                // On délègue le calcul au service spécialisé
                $totalForfait += IndemniteKmService::computeMontant($quantite, (string) $puissanceVisible, $prixUnitaire);
            } else {
                // Sinon calcul standard (quantité * prix unitaire de la table fraisforfait)
                $totalForfait += $quantite * $prixUnitaire;
            }
        }

        // 4. Calcul de la partie HORS FORFAIT
        // On ignore les frais dont le libellé commence par "REFUSE " (Règle green-IT / Comptable)
        $reqHF = $this->connexion->prepare(
            "SELECT SUM(montant) AS totalHF FROM lignefraishorsforfait "
            . "WHERE idvisiteur = :v AND mois = :m AND libelle NOT LIKE 'REFUSE %'"
        );
        $reqHF->bindParam(':v', $idVisiteur, PDO::PARAM_STR);
        $reqHF->bindParam(':m', $mois, PDO::PARAM_STR);
        $reqHF->execute();

        $ligneHF = $reqHF->fetch();
        $totalHF = 0.0;
        if ($ligneHF && isset($ligneHF['totalHF'])) {
            $totalHF = (float) $ligneHF['totalHF'];
        }

        // 5. On additionne les deux parties
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
     * Retourne les mois pour lesquels un visiteur a une fiche de frais.
     * Les mois sont retournés du plus récent au plus ancien.
     *
     * @param string $idVisiteur ID du visiteur
     * @return array             Tableau de mois (aaaamm)
     */
    public function getLesMoisDisponibles($idVisiteur): array
    {
        $requetePrepare = $this->connexion->prepare(
            'SELECT fichefrais.mois AS mois FROM fichefrais '
            . 'WHERE fichefrais.idvisiteur = :idVisiteur '
            . 'ORDER BY fichefrais.mois DESC'
        );
        $requetePrepare->bindParam(':idVisiteur', $idVisiteur, PDO::PARAM_STR);
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
     * Indique si les colonnes de paiement étendues existent dans la table fichefrais.
     * Pourrait être automatisé via une requête DESCRIBE, mais fixé à false ici pour la stabilité.
     *
     * @return bool
     */
    public function hasPaiementColumns(): bool
    {
        return false; // Changé à true si schema étendu
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