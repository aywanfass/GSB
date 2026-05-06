<?php
/**
 * Génération de la fiche de frais en PDF (FPDF) avec cache disque Green-IT.
 *
 * Tâche 7 : génère le PDF via FPDF.
 * Tâche 8 : met le PDF en cache dans storage/pdf/ pour ne le générer qu'une seule fois.
 */

require_once '../vendor/autoload.php';
session_start();
ob_start();

use Modeles\PdoGsb;
use Outils\Utilitaires;

require_once '../resources/Outils/fpdf/fpdf.php';

// Vérification de la connexion
$idVisiteur = $_SESSION['idVisiteur'] ?? null;
if (!$idVisiteur) {
    ob_end_clean();
    http_response_code(403);
    exit('Accès refusé. Veuillez vous connecter.');
}

$pdo = PdoGsb::getPdoGsb();
$leMois = filter_input(INPUT_GET, 'mois', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if (!$leMois) {
    ob_end_clean();
    exit('Erreur : Le mois de la fiche n\'est pas précisé.');
}

// Vérification de l'état de la fiche (Green-IT : PDF uniquement pour VA ou RB)
$lesInfosFicheFrais = $pdo->getLesInfosFicheFrais($idVisiteur, $leMois);
$etatFiche = $lesInfosFicheFrais['idEtat'];

if ($etatFiche != 'VA' && $etatFiche != 'RB') {
    ob_end_clean();
    http_response_code(403);
    exit('Action non autorisée : Le PDF n\'est disponible qu\'une fois la fiche validée ou mise en paiement.');
}

// --- Green-IT : gestion du cache disque ---
// Le dossier de cache est storage/pdf/ à la racine du projet (un niveau au-dessus de public/)
$cacheDir = __DIR__ . '/../storage/pdf';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

// Nom de fichier unique par visiteur et par mois
$nomFichier = $idVisiteur . '_' . $leMois . '.pdf';
$cheminCache = $cacheDir . '/' . $nomFichier;

// Si le fichier existe déjà en cache, on le sert directement sans régénérer
if (file_exists($cheminCache)) {
    ob_end_clean();
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="Fiche_Frais_' . $leMois . '.pdf"');
    header('Content-Length: ' . filesize($cheminCache));
    readfile($cheminCache);
    exit;
}

// --- Génération du PDF (première fois uniquement) ---
$lesFraisForfait    = $pdo->getLesFraisForfait($idVisiteur, $leMois);
$lesFraisHorsForfait = $pdo->getLesFraisHorsForfait($idVisiteur, $leMois);
$infosVisiteur      = $pdo->getVisiteur($idVisiteur);

$numAnnee    = substr($leMois, 0, 4);
$numMois     = substr($leMois, 4, 2);
$libelleMois = Utilitaires::getLibelleMois((int) $numMois);

class GsbPdf extends FPDF
{
    function Header()
    {
        if (file_exists('./images/logo.jpg')) {
            $this->Image('./images/logo.jpg', 10, 8, 33);
        }
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80);
        $this->Cell(80, 10, iconv('UTF-8', 'windows-1252', 'ÉTAT DE FRAIS ENGAGÉS'), 1, 0, 'C');
        $this->Ln(30);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new GsbPdf();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times', '', 12);

// Infos visiteur
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Visiteur : ' . strtoupper($infosVisiteur['nom']) . ' ' . $infosVisiteur['prenom']), 0, 1, 'L', true);
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Mois : ' . $libelleMois . ' ' . $numAnnee), 0, 1, 'L', true);
$pdf->Ln(5);

// Éléments forfaitisés
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(31, 78, 121);
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Éléments forfaitisés'), 0, 1);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', '', 10);

$pdf->SetFillColor(51, 122, 183);
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

// Éléments hors forfait
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(31, 78, 121);
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Descriptif des éléments hors forfait'), 0, 1);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', '', 10);

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

// Total
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(140, 10, 'TOTAL', 0, 0, 'R');
$pdf->SetFillColor(255, 255, 0);
$pdf->Cell(50, 10, number_format($totalGeneral, 2, ',', ' ') . iconv('UTF-8', 'windows-1252', ' €'), 1, 1, 'R', true);

$pdf->Ln(20);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Fait à Paris, le ' . date('d/m/Y')), 0, 1, 'R');
$pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', 'Signature'), 0, 1, 'R');

// Sauvegarde du PDF dans le cache disque (Green-IT : généré une seule fois)
$contenuPdf = $pdf->Output('S');
file_put_contents($cheminCache, $contenuPdf);

// Envoi au navigateur depuis le contenu déjà en mémoire
ob_end_clean();
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="Fiche_Frais_' . $leMois . '.pdf"');
header('Content-Length: ' . strlen($contenuPdf));
echo $contenuPdf;
