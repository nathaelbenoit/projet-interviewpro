<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'etudiant') {
    echo json_encode(["status" => "error", "message" => "Accès non autorisé."]);
    exit;
}

$host = "localhost";
$dbname = "nnn_sae";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Erreur de connexion à la base de données."]);
    exit;
}

$nom = $conn->real_escape_string($_POST["interviewerLastName"]);
$prenom = $conn->real_escape_string($_POST["interviewerFirstName"]);
$emailPro = $conn->real_escape_string($_POST["interviewerEmail"]);
$metierPro = $conn->real_escape_string($_POST["interviewerMetier"]);
$entreprisePro = $conn->real_escape_string($_POST["interviewerEntreprise"]);
$linkedin = $conn->real_escape_string($_POST["interviewerLinkedIn"]);
$utilisateur_email = $_SESSION['email'];

if (!isset($_FILES["workFile"]) || !isset($_FILES["attestationFile"])) {
    echo json_encode(["status" => "error", "message" => "Les fichiers sont requis."]);
    exit;
}

$workFileContent = file_get_contents($_FILES["workFile"]["tmp_name"]);
$attestationFileContent = file_get_contents($_FILES["attestationFile"]["tmp_name"]);

// Vérification professionnel
$sqlPro = "SELECT * FROM professionnels WHERE pro_email = ?";
$stmtPro = $conn->prepare($sqlPro);
$stmtPro->bind_param("s", $emailPro);
$stmtPro->execute();
$resultPro = $stmtPro->get_result();

if ($resultPro->num_rows === 0) {
    $sqlInsertPro = "INSERT INTO professionnels (pro_email, nom, prenom, metier, entreprise, linkedin) VALUES (?, ?, ?, ?, ?, ?)";
    $stmtInsertPro = $conn->prepare($sqlInsertPro);
    $stmtInsertPro->bind_param("ssssss", $emailPro, $nom, $prenom, $metierPro, $entreprisePro, $linkedin);
    if (!$stmtInsertPro->execute()) {
        echo json_encode(["status" => "error", "message" => "Erreur lors de l'insertion du professionnel."]);
        exit;
    }
    $stmtInsertPro->close();
}
$stmtPro->close();

// Préparer l'insertion interview avec bind_param "ssbb"
$sqlInterview = "INSERT INTO interviews (utilisateur_email, pro_email, fichier_interview, fichier_attestation) VALUES (?, ?, ?, ?)";
$stmtInterview = $conn->prepare($sqlInterview);

// Pour bind_param, on met null pour les blobs, on envoie avec send_long_data ensuite
$null = null;
$stmtInterview->bind_param("ssbb", $utilisateur_email, $emailPro, $null, $null);

// Envoyer les blobs (attention indices : 2 et 3)
$stmtInterview->send_long_data(2, $workFileContent);
$stmtInterview->send_long_data(3, $attestationFileContent);

if ($stmtInterview->execute()) {
    echo json_encode(["status" => "success", "message" => "Interview enregistrée avec succès."]);
} else {
    echo json_encode(["status" => "error", "message" => "Erreur lors de l'enregistrement de l'interview."]);
}

$stmtInterview->close();
$conn->close();
?>
