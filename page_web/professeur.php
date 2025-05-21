<?php
// Connexion à la base de données
$host = "127.0.0.1";
$user = "root";         // adapte selon ta config
$password = "";         // adapte selon ta config
$dbname = "nnn_sae";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Requête pour récupérer les étudiants avec interview, attestation et nombre de corrections
$sql = "
    SELECT 
        u.utilisateur_email, u.nom, u.prenom, u.role,
        i.interview_id, i.fichier_interview, i.fichier_attestation,
        p.nom AS pro_nom, p.prenom AS pro_prenom, p.metier AS pro_metier,
        (SELECT COUNT(*) FROM criteres_evaluation ce WHERE ce.utilisateur_email = u.utilisateur_email) AS nb_corrections,
        (SELECT SUM(ce.note_obtenue) FROM criteres_evaluation ce WHERE ce.utilisateur_email = u.utilisateur_email) AS total_obtenu,
        (SELECT SUM(c.note_max) FROM criteres c) AS total_max
    FROM users u
    LEFT JOIN interviews i ON u.utilisateur_email = i.utilisateur_email
    LEFT JOIN professionnels p ON i.pro_email = p.pro_email
    WHERE u.role = 'etudiant'
    ORDER BY u.nom, u.prenom;
";
$result = $conn->query($sql);

$etudiants = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $etudiants[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Espace Professeur</title>
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
        details p {
            margin: 5px 0;
            font-size: 16px;
            color: #555;
        }
        details p strong {
            color: #333;
        }
        .btn-criteres {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #a00f28;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            text-align: center;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-criteres:hover {
            background-color: #8b0d22;
        }
        .btn-corriger {
            margin-top: 15px;
            padding: 8px 15px;
            background-color: #a00f28;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-corriger:hover {
            background-color: #8b0d22;
        }
        .section-label {
            font-weight: bold;
            color: #a00f28;
            margin-top: 10px;
        }
        .status-corrige {
            color: green;
            font-weight: bold;
            margin-left: 10px;
        }
        .status-non-corrige {
            color: red;
            font-weight: bold;
            margin-left: 10px;
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
        <div>
            <a href="professeur.php">Espace Professeur</a>
            <a href="pro.php">Professionnels</a>
            <a href="compte.php">Mon compte</a>
        </div>
    </nav>
    <img src="./images/LogoSlogan.png" alt="Logo Slogan" class="header-logo-right">
</header>
<div class="container">
    <h1>Bienvenue, Professeur</h1>
    <a class="btn-criteres" href="etablir_criteres.php">Établir la grille d'évaluation</a>
    <p>Voici les évaluations des étudiants :</p>
    <div id="evaluations-container">
        <?php if (count($etudiants) > 0): ?>
            <?php foreach ($etudiants as $etudiant): ?>
                <details>
                    <summary>
                        <?= htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']) ?>
                        <?php if ($etudiant['nb_corrections'] > 0): ?>
                            <span class="status-corrige">(Corrigé)</span>
                        <?php else: ?>
                            <span class="status-non-corrige">(Non corrigé)</span>
                        <?php endif; ?>
                    </summary>
                    <p><strong>Email :</strong> <?= htmlspecialchars($etudiant['utilisateur_email']) ?></p>

                    <p class="section-label">Interview :</p>
                    <p>
                        <?php if (!empty($etudiant['fichier_interview'])): ?>
                            <a href="telecharger.php?type=interview&id=<?= $etudiant['interview_id'] ?>">Télécharger l'interview</a>
                        <?php else: ?>
                            Aucune interview disponible.
                        <?php endif; ?>
                    </p>

                    <p class="section-label">Attestation :</p>
                    <p>
                        <?php if (!empty($etudiant['fichier_attestation'])): ?>
                            <a href="telecharger.php?type=attestation&id=<?= $etudiant['interview_id'] ?>">Télécharger l'attestation</a>
                        <?php else: ?>
                            Aucune attestation disponible.
                        <?php endif; ?>
                    </p>

                    <p class="section-label">Professionnel :</p>
                    <p>Nom : <?= htmlspecialchars(isset($etudiant['pro_nom']) ? $etudiant['pro_nom'] : 'Aucun nom disponible.') ?></p>
                    <p>Prénom : <?= htmlspecialchars(isset($etudiant['pro_prenom']) ? $etudiant['pro_prenom'] : 'Aucun prénom disponible.') ?></p>
                    <p>Métier : <?= htmlspecialchars(isset($etudiant['pro_metier']) ? $etudiant['pro_metier'] : 'Aucun métier disponible.') ?></p>


                    <p class="section-label">Note finale :</p>
                        <?php if (!is_null($etudiant['total_obtenu']) && !is_null($etudiant['total_max'])): ?>
                            <p><?= htmlspecialchars($etudiant['total_obtenu']) ?> / <?= htmlspecialchars($etudiant['total_max']) ?></p>
                        <?php else: ?>
                            <p>Non évalué</p>
                        <?php endif; ?>

                    <a href="corriger.php?utilisateur_email=<?= urlencode($etudiant['utilisateur_email']) ?>" class="btn-corriger">
                        <?= ($etudiant['nb_corrections'] > 0) ? 'Modifier la correction' : 'Corriger' ?>
                    </a>
                </details>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun étudiant trouvé dans la base de données.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
