<?php
session_start();

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'etudiant') {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

$host = "localhost";
$dbname = "nnn_sae";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

$stmt = $conn->prepare("
    SELECT c.critere_id, c.nom_critere, ce.note_obtenue, c.note_max, ce.commentaire
    FROM criteres_evaluation ce
    JOIN criteres c ON ce.critere_id = c.critere_id
    WHERE ce.utilisateur_email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$evaluations = [];
while ($row = $result->fetch_assoc()) {
    $evaluations[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Correction - Espace Étudiant</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        background-color: #f5f5dc;
        color: #333;
    }
    header {
            background-color: #a00f28;
            color: #ffffff;
            padding: 10px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .header-left-group {
            display: flex;
            align-items: center;
            gap: 0; 
        }
        .header-logo-left {
            height: 40px;
            width: auto;
            display: block;
        }
        .header-logo-nom {
            margin-left: 0;
            height: 20px; 
            width: auto;
        }
        .header-logo-right {
            height: 40px;
            width: auto;
        }
    .container {
        max-width: 900px;
        padding: 30px;
        margin: 40px auto;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: fadeIn 0.5s ease-in-out;
        display: flex;
        flex-direction: column;
        min-height: 80vh;
    }

    h1 {
        color: #a00f28;
        text-align: center;
        margin-bottom: 30px;
        font-size: 28px;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        margin-top: 10px;
        background-color: #fff;
        flex-grow: 1;
    }

    th,
    td {
        border: 1px solid #ccc;
        padding: 15px;
        vertical-align: top;
        transition: background-color 0.3s;
    }

    th {
        background-color: #a00f28;
        color: #fff;
        font-weight: bold;
        text-align: left;
    }

    .commentaire {
        font-style: italic;
        color: #555;
        white-space: pre-wrap;
    }

    .return-container {
        margin-top: 30px;
        text-align: center;
    }

    a.back-link {
        color : #ffff;
        background-color: #a00f28;
        text-decoration: none;
        font-weight: bold;
        font-size: 18px;
        padding: 10px 20px;
        border-radius: 6px;
        transition: background-color 0.3s, color 0.3s;
        display: inline-block;
    }

    a.back-link:hover {
        background-color: #c82333;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
</head>
<header>
    <div class="header-left-group">
        <img src="./images/logoSrien.png" alt="Logo sans rien" class="header-logo-left">
        <img src="./images/logoNom.png" alt="Logo Nom" class="header-logo-left header-logo-nom">
    </div>
    <img src="./images/LogoSlogan.png" alt="Logo Slogan" class="header-logo-right">
</header>
<body>
<div class="container">
    <h1>Correction de votre évaluation</h1>

    <?php if (count($evaluations) === 0): ?>
        <p>Aucune correction disponible pour le moment.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Critère</th>
                    <th>Note</th>
                    <th>Commentaire</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($evaluations as $eval): ?>
                    <tr>
                        <td><?= htmlspecialchars($eval['nom_critere']) ?></td>
                        <td><?= htmlspecialchars($eval['note_obtenue']) . ' / ' . htmlspecialchars($eval['note_max']) ?></td>
                        <td class="commentaire"><?= nl2br(htmlspecialchars($eval['commentaire'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<div class="return-container">
    <a href="etudiant.php" class="back-link">Retour à l'espace étudiant</a>
</div>
<br><br>
</body>
</html>
