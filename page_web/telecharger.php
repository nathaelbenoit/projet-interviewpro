<?php
$host = "127.0.0.1";
$user = "root";
$password = "";
$dbname = "nnn_sae";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!in_array($type, ['interview', 'attestation']) || $id <= 0) {
    die("Paramètres invalides.");
}

$champ = ($type === 'interview') ? 'fichier_interview' : 'fichier_attestation';

$stmt = $conn->prepare("SELECT $champ FROM interviews WHERE interview_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($fichier);
    $stmt->fetch();

    // Important : nettoyer le buffer avant d'envoyer le fichier
    if (ob_get_length()) ob_end_clean();

    header("Content-Type: application/pdf");
    header("Content-Disposition: attachment; filename=\"{$type}_etudiant_{$id}.pdf\"");
    header("Content-Length: " . strlen($fichier));
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo $fichier;
} else {
    echo "Fichier non trouvé.";
}

$stmt->close();
$conn->close();
