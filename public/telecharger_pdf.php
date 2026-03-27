<?php
/**
 * Génération de la fiche de frais en PDF (FPDF)
 */

require_once '../vendor/autoload.php';
session_start();
ob_start();

use Modeles\PdoGsb;
use Outils\Utilitaires;

// Inclusion manuelle de FPDF car non géré via composer.json PSR-4 par défaut dans ce projet
require_once '../resources/Outils/fpdf/fpdf.php';

// Vérification de la connexion
$idVisiteur = $_SESSION['idVisiteur'] ?? null;
if (!$idVisiteur) {
    exit('Accès refusé. Veuillez vous connecter.');
}

$pdo = PdoGsb::getPdoGsb();
$leMois = filter_input(INPUT_GET, 'mois', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// Vérification que le mois est bien renseigné dans l'URL
if (!$leMois) {
    exit('Erreur : Le mois de la fiche n\'est pas précisé.');
}

// Récupération des informations de la fiche en base de données
$lesInfosFicheFrais = $pdo->getLesInfosFicheFrais($idVisiteur, $leMois);

// Règle de gestion "Green-IT"
// On ne permet la génération du PDF QUE si la fiche est "Validée" (VA) ou "Remboursée" (RB)
$etatFiche = $lesInfosFicheFrais['idEtat'];
if ($etatFiche != 'VA' && $etatFiche != 'RB') {
    exit('Action non autorisée : Le PDF n\'est disponible qu\'une fois la fiche validée ou mise en paiement.');
}

// Étape 4 : Collecte de toutes les données nécessaires pour le document
$lesFraisForfait = $pdo->getLesFraisForfait($idVisiteur, $leMois);
$lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteur, $leMois);
$infosVisiteur = $pdo->getVisiteur($idVisiteur);

$numAnnee = substr($leMois, 0, 4);
$numMois = substr($leMois, 4, 2);
$libelleMois = Utilitaires::getLibelleMois((int) $numMois);

// --- Création du PDF ---
class GsbPdf extends FPDF
{
    function Header()
    {
        // Logo
        if (file_exists('./images/logo.jpg')) {
            $this->Image('./images/logo.jpg', 10, 8, 33);
        }
        // Police Arial gras 15
        $this->SetFont('Arial', 'B', 15);
        // Décalage à droite
        $this->Cell(80);
        // Titre
        $this->Cell(80, 10, iconv('UTF-8', 'windows-1252', 'ÉTAT DE FRAIS ENGAGÉS'), 1, 0, 'C');
        // Saut de ligne
        $this->Ln(30);
    }

    function Footer()
    {
        // Positionnement à 1,5 cm du bas
        $this->SetY(-15);
        // Police Arial italique 8
        $this->SetFont('Arial', 'I', 8);
        // Numéro de page
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new GsbPdf();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times', '', 12);

// --- Infos Visiteur ---
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Visiteur : ' . strtoupper($infosVisiteur['nom']) . ' ' . $infosVisiteur['prenom']), 0, 1, 'L', true);
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Mois : ' . $libelleMois . ' ' . $numAnnee), 0, 1, 'L', true);
$pdf->Ln(5);

// --- Éléments Forfaitisés ---
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(31, 78, 121); // Bleu GSB
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Éléments forfaitisés'), 0, 1);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', '', 10);

// En-tête tableau forfait
$pdf->SetFillColor(51, 122, 183); // Bleu primaire
$pdf->SetTextColor(255);
$w = array(60, 40, 40, 50);
$pdf->Cell($w[0], 7, iconv('UTF-8', 'windows-1252', 'Frais Forfaitaires'), 1, 0, 'C', true);
$pdf->Cell($w[1], 7, iconv('UTF-8', 'windows-1252', 'Quantité'), 1, 0, 'C', true);
$pdf->Cell($w[2], 7, iconv('UTF-8', 'windows-1252', 'Montant Unitaire'), 1, 0, 'C', true);
$pdf->Cell($w[3], 7, iconv('UTF-8', 'windows-1252', 'Total'), 1, 0, 'C', true);
$pdf->Ln();

$pdf->SetTextColor(0);
$totalGeneral = 0;

foreach ($lesFraisForfait as $unFrais) {
    $montantUnitaire = $unFrais['montant'];
    $total = $unFrais['quantite'] * $montantUnitaire;
    $totalGeneral += $total;

    $pdf->Cell($w[0], 7, iconv('UTF-8', 'windows-1252', $unFrais['libelle']), 1);
    $pdf->Cell($w[1], 7, $unFrais['quantite'], 1, 0, 'C');
    $pdf->Cell($w[2], 7, number_format($montantUnitaire, 2, ',', ' ') . iconv('UTF-8', 'windows-1252', ' €'), 1, 0, 'R');
    $pdf->Cell($w[3], 7, number_format($total, 2, ',', ' ') . iconv('UTF-8', 'windows-1252', ' €'), 1, 0, 'R');
    $pdf->Ln();
}
$pdf->Ln(10);

// --- Éléments Hors Forfait ---
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(31, 78, 121);
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Descriptif des éléments hors forfait'), 0, 1);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', '', 10);

// En-tête tableau hors forfait
$pdf->SetFillColor(51, 122, 183);
$pdf->SetTextColor(255);
$wHF = array(30, 110, 50);
$pdf->Cell($wHF[0], 7, iconv('UTF-8', 'windows-1252', 'Date'), 1, 0, 'C', true);
$pdf->Cell($wHF[1], 7, iconv('UTF-8', 'windows-1252', 'Libellé'), 1, 0, 'C', true);
$pdf->Cell($wHF[2], 7, iconv('UTF-8', 'windows-1252', 'Montant'), 1, 0, 'C', true);
$pdf->Ln();

$pdf->SetTextColor(0);
foreach ($lesFraisHorsForfait as $unFraisHF) {
    $totalGeneral += $unFraisHF['montant'];
    $pdf->Cell($wHF[0], 7, $unFraisHF['date'], 1, 0, 'C');
    $pdf->Cell($wHF[1], 7, iconv('UTF-8', 'windows-1252', $unFraisHF['libelle']), 1);
    $pdf->Cell($wHF[2], 7, number_format($unFraisHF['montant'], 2, ',', ' ') . iconv('UTF-8', 'windows-1252', ' €'), 1, 0, 'R');
    $pdf->Ln();
}
$pdf->Ln(10);

// --- TOTAL ---
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(140, 10, 'TOTAL', 0, 0, 'R');
$pdf->SetFillColor(255, 255, 0); // Jaune
$pdf->Cell(50, 10, number_format($totalGeneral, 2, ',', ' ') . iconv('UTF-8', 'windows-1252', ' €'), 1, 1, 'R', true);

$pdf->Ln(20);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Fait à Paris, le ' . date('d/m/Y')), 0, 1, 'R');
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Signature'), 0, 1, 'R');

// Sortie du PDF
ob_end_clean();
$pdf->Output('I', 'Fiche_Frais_' . $leMois . '.pdf');
