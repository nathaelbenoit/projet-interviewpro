<?php
/******************************
 * corriger.php
 * Affiche la grille d'évaluation avec possibilité de modifier
 *****************************/
$host = "127.0.0.1";
$user = "root";
$password = "";
$dbname = "nnn_sae";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée : ".$conn->connect_error);
}

// -------- Récupération de l'étudiant --------
$utilisateur_email = isset($_GET['utilisateur_email']) ? $_GET['utilisateur_email'] : '';
if ($utilisateur_email === '') {
    die("Email d'étudiant non spécifié.");
}

$stmt = $conn->prepare("SELECT nom, prenom FROM users WHERE utilisateur_email = ? AND role = 'etudiant'");
$stmt->bind_param("s", $utilisateur_email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    die("Étudiant introuvable.");
}
$stmt->bind_result($nomEtud, $prenomEtud);
$stmt->fetch();
$stmt->close();

// -------- Récupération des critères --------
$criteres = [];
$res = $conn->query("SELECT critere_id, nom_critere, note_max FROM criteres ORDER BY critere_id");
while ($row = $res->fetch_assoc()) {
    $criteres[] = $row;
}
$res->free();

// -------- Récupération des corrections existantes --------
$corrections_existantes = [];
$stmt2 = $conn->prepare("SELECT critere_id, note_obtenue, commentaire FROM criteres_evaluation WHERE utilisateur_email = ?");
$stmt2->bind_param("s", $utilisateur_email);
$stmt2->execute();
$result = $stmt2->get_result();
while ($row = $result->fetch_assoc()) {
    $corrections_existantes[$row['critere_id']] = [
        'note_obtenue' => $row['note_obtenue'],
        'commentaire' => $row['commentaire']
    ];
}
$stmt2->close();

// -------- Calcul de la note finale --------
$total_note_obtenue = 0;
$total_note_max = 0;
foreach ($criteres as $c) {
    $total_note_max += $c['note_max'];
    if (isset($corrections_existantes[$c['critere_id']]) && is_numeric($corrections_existantes[$c['critere_id']]['note_obtenue'])) {
        $total_note_obtenue += $corrections_existantes[$c['critere_id']]['note_obtenue'];
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Correction – <?php echo htmlspecialchars("$prenomEtud $nomEtud"); ?></title>
<style>
    /* (ton CSS ici, inchangé, pour la lisibilité je ne le recopie pas) */
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
        max-width: 1000px;
        padding: 30px;
        margin: 40px auto;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: fadeIn 0.5s ease-in-out;
    }

    h2 {
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
        text-align: center;
    }

    input[type=number] {
        width: 30%;
        padding: 10px;
        font-size: 15px;
        border: 1px solid #ccc;
        border-radius: 6px;
        box-sizing: border-box;
    }

    textarea {
        width: 100%;
        resize: vertical;
        min-height: 80px;
    }

    .btn {
        margin-top: 25px;
        padding: 12px 25px;
        background-color: #a00f28;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 17px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        transition: background-color 0.3s ease;
    }

    .btn:hover {
        background-color: #8b0d22;
    }

    .btn-red {
        background-color: #dc3545;
    }

    .btn-red:hover {
        background-color: #c82333;
    }

    #message {
        margin-top: 25px;
        display: none;
        padding: 15px;
        border-radius: 6px;
        font-size: 16px;
        animation: fadeIn 0.5s ease;
    }

    .center {
        text-align: center;
        margin-top: 40px;
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
<body>
<header>
    <div class="header-left-group">
        <img src="./images/logoSrien.png" alt="Logo sans rien" class="header-logo-left">
        <img src="./images/logoNom.png" alt="Logo Nom" class="header-logo-left header-logo-nom">
    </div>
    <img src="./images/LogoSlogan.png" alt="Logo Slogan" class="header-logo-right">
</header>
<div class="container">
    <h2>Correction de : <?php echo htmlspecialchars("$prenomEtud $nomEtud"); ?></h2>

    <form id="correctionForm">
        <input type="hidden" name="utilisateur_email" value="<?php echo htmlspecialchars($utilisateur_email); ?>">

        <table>
            <tr>
                <th>Critère</th>
                <th>Note</th>
                <th>Commentaire</th>
            </tr>
            <?php foreach ($criteres as $c): ?>
            <tr>
                <td><?php echo htmlspecialchars($c['nom_critere']); ?></td>
                <td>
                    <input
                        type="number"
                        name="notes[<?php echo $c['critere_id']; ?>]"
                        min="0"
                        max="<?php echo $c['note_max']; ?>"
                        required
                        value="<?php
                            echo isset($corrections_existantes[$c['critere_id']]) ?
                                htmlspecialchars($corrections_existantes[$c['critere_id']]['note_obtenue']) : '';
                        ?>"
                    > / <?php echo $c['note_max']; ?>
                </td>
                <td>
                    <textarea
                        name="commentaires[<?php echo $c['critere_id']; ?>]"
                        required><?php
                            echo isset($corrections_existantes[$c['critere_id']]) ?
                                htmlspecialchars($corrections_existantes[$c['critere_id']]['commentaire']) : '';
                        ?></textarea>
                </td>
            </tr>
            <?php endforeach; ?>

            <!-- Ligne note finale -->
            <tr style="font-weight: bold; color: #a00f28; font-size: 18px; background-color: #fff0f0;">
                <td>Note finale</td>
                <td colspan="2" style="color: #a00f28; font-weight: bold; font-size: 20px; text-align: center;">
                    <?php echo $total_note_obtenue . " / " . $total_note_max; ?>
                </td>
            </tr>
        </table>

        <button type="submit" class="btn">Enregistrer la correction</button>
    </form>

    <div id="message"></div>
</div>

<div class="center">
    <a href="professeur.php" class="btn btn-red">Retour à l’espace professeur</a>
</div>

<script>
document.getElementById("correctionForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const msgBox = document.getElementById("message");

    fetch("enregistrer_correction.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        msgBox.innerHTML = "✅ " + data.message;
        msgBox.style.display = "block";
        msgBox.style.backgroundColor = "#d4edda";
        msgBox.style.color = "#155724";
        msgBox.style.border = "1px solid #c3e6cb";
    })
    .catch(error => {
        msgBox.innerHTML = "❌ Une erreur est survenue.";
        msgBox.style.display = "block";
        msgBox.style.backgroundColor = "#f8d7da";
        msgBox.style.color = "#721c24";
        msgBox.style.border = "1px solid #f5c6cb";
    });

    setTimeout(() => {
        msgBox.style.display = "none";
    }, 6000);
});
</script>
</body>
</html>
