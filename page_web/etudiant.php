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

$stmt = $conn->prepare("SELECT * FROM interviews WHERE utilisateur_email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$interviewExists = ($result->num_rows > 0);
$stmt->close();

// Récupérer la note obtenue et la note maximale
$noteObtenue = null;
$noteMax = null;

$stmt = $conn->prepare("
    SELECT 
        SUM(ce.note_obtenue) AS total_obtenue,
        SUM(c.note_max) AS total_max
    FROM criteres_evaluation ce
    JOIN criteres c ON ce.critere_id = c.critere_id
    WHERE ce.utilisateur_email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $noteObtenue = $row['total_obtenue'];
    $noteMax = $row['total_max'];
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Étudiant</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f5f5dc;
            color: #a00f28;
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
        header nav a {
            color: #ffffff;
            text-decoration: none;
            margin: 0 10px;
            font-weight: bold;
        }
        header nav a:hover {
            text-decoration: underline;
        }
        .container {
            max-width: 1200px;
            padding: 20px;
            margin: 0 auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #a00f28;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #a00f28;
            color: #ffffff;
        }
        a.linkedin {
            color: #a00f28;
            text-decoration: none;
        }
        a.linkedin:hover {
            text-decoration: underline;
        }
        .form-section, .result-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #a00f28;
            border-radius: 10px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .form-section h2, .result-section h2 {
            color: #a00f28;
        }
        .form-section input, .form-section textarea, .form-section button {
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-section button {
            background-color: #a00f28;
            color: #ffffff;
            cursor: pointer;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        #evaluations-container {
            margin-top: 20px;
        }
        details {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
            padding: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        summary {
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            color: #a00f28;
        }
        summary:hover {
            text-decoration: underline;
        }
        #message {
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            display: none;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .btn-correction {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 18px;
            background-color: #a00f28;
            color: white;
            text-decoration: none;
            font-weight: bold;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }
        .btn-correction:hover {
            background-color: #7e0b20;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-left-group">
            <img src="./images/logoSrien.png" alt="Logo sans rien" class="header-logo-left">
            <img src="./images/logoNom.png" alt="Logo Nom" class="header-logo-left header-logo-nom">
        </div>
        <nav>
            <a href="etudiant.php">Espace Étudiant</a>
            <a href="pro.php">Professionnels</a>
            <a href="compte.php">Mon compte</a>
        </nav>
        <img src="./images/LogoSlogan.png" alt="Logo Slogan" class="header-logo-right">
    </header>
    <div class="container">
        <div class="form-section">
            <h2>Enregistrer une Interview</h2>

            <?php if ($interviewExists): ?>
                <p class="success">✅ Votre interview a déjà été remise.</p>
            <?php else: ?>
                <form action="enregistrer_interview.php" id="interviewForm" enctype="multipart/form-data">
                    <input type="text" name="interviewerLastName" placeholder="Nom du professionnel" required>
                    <input type="text" name="interviewerFirstName" placeholder="Prénom du professionnel" required>
                    <input type="email" name="interviewerEmail" placeholder="Email du professionnel" required>
                    <input type="text" name="interviewerMetier" placeholder="Métier du professionnel" required>
                    <input type="text" name="interviewerEntreprise" placeholder="Entreprise du professionnel" required>
                    <input type="url" name="interviewerLinkedIn" placeholder="Profil LinkedIn du professionnel" required>
                    <label for="workFile">Travail (PDF) :</label>
                    <input type="file" name="workFile" accept="application/pdf" required>
                    <label for="attestationFile">Attestation (PDF) :</label>
                    <input type="file" name="attestationFile" accept="application/pdf" required>
                    <button type="submit">Remettre l'interview</button>
                </form>
            <?php endif; ?>

            <div id="message"></div>
        </div>

        <div class="result-section">
            <h2>Résultat</h2>
            <?php if (!is_null($noteObtenue) && !is_null($noteMax)): ?>
                <p><strong>Note finale :</strong> <?= htmlspecialchars($noteObtenue) ?> / <?= htmlspecialchars($noteMax) ?></p>
                <a href="consulter_correction.php" class="btn-correction">Consulter la correction</a>
            <?php else: ?>
                <p>Note finale : non évaluée pour le moment.</p>
            <?php endif; ?>
        </div>

    </div>

    <script>
    document.getElementById("interviewForm").addEventListener("submit", function (e) {
        e.preventDefault();

        const form = document.getElementById("interviewForm");
        const formData = new FormData(form);

        fetch("enregistrer_interview.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const messageDiv = document.getElementById("message");
            if (data.status === "success") {
                messageDiv.textContent = data.message;
                messageDiv.className = "success";
            } else {
                messageDiv.textContent = data.message;
                messageDiv.className = "error";
            }
            messageDiv.style.display = "block";
        })
        .catch(error => {
            console.error("Erreur :", error);
        });
    });
    </script>
</body>
</html>
