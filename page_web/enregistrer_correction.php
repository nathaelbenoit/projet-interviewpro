<?php
/********************************
 * enregistrer_correction.php
 * Insère (ou remplace) les notes
 ********************************/
header("Content-Type: application/json");

$host = "127.0.0.1";
$user = "root";
$password = "";
$dbname = "nnn_sae";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connexion échouée."]);
    exit;
}

$utilisateur_email = isset($_POST['utilisateur_email']) ? $_POST['utilisateur_email'] : '';
$notes        = isset($_POST['notes'])        ? $_POST['notes']        : [];
$commentaires = isset($_POST['commentaires']) ? $_POST['commentaires'] : [];

if ($utilisateur_email === '' || empty($notes) || empty($commentaires)) {
    echo json_encode(["status" => "error", "message" => "Paramètres manquants."]);
    exit;
}

$stmtDel = $conn->prepare("DELETE FROM criteres_evaluation WHERE utilisateur_email = ?");
$stmtDel->bind_param("s", $utilisateur_email);
$stmtDel->execute();
$stmtDel->close();

$stmtIns = $conn->prepare(
    "INSERT INTO criteres_evaluation (utilisateur_email, critere_id, note_obtenue, commentaire)
     VALUES (?, ?, ?, ?)"
);

foreach ($notes as $critere_id => $note) {
    $commentaire = $commentaires[$critere_id];
    $stmtIns->bind_param("siis", $utilisateur_email, $critere_id, $note, $commentaire);
    $stmtIns->execute();
}
$stmtIns->close();
$conn->close();

echo json_encode(["status" => "success", "message" => "Correction enregistrée avec succès."]);
?>
