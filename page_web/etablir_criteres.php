<?php
// Connexion à la base de données
$pdo = new PDO('mysql:host=localhost;dbname=nnn_sae', 'root', '');
$modifier_critere = null;

// Suppression d'un critère
if (isset($_GET['supprimer'])) {
    $critere_id = (int)$_GET['supprimer'];
    $stmt = $pdo->prepare("DELETE FROM criteres WHERE critere_id = :id");
    $stmt->execute([':id' => $critere_id]);
    header("Location: etablir_criteres.php");
    exit;
}

// Récupérer les infos du critère à modifier
if (isset($_GET['modifier'])) {
    $critere_id = (int)$_GET['modifier'];
    $stmt = $pdo->prepare("SELECT * FROM criteres WHERE critere_id = :id");
    $stmt->execute([':id' => $critere_id]);
    $modifier_critere = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Enregistrer les modifications du critère
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_id'])) {
    $critere_id = (int)$_POST['modifier_id'];
    $nouveau_nom = trim($_POST['modifier_nom']);
    $nouvelle_note = (int)$_POST['modifier_note'];

    if ($nouveau_nom !== '' && $nouvelle_note > 0) {
        $stmt = $pdo->prepare("UPDATE criteres SET nom_critere = :nom_critere, note_max = :note_max WHERE critere_id = :id");
        $stmt->execute([
            ':nom_critere' => $nouveau_nom,
            ':note_max' => $nouvelle_note,
            ':id' => $critere_id
        ]);
    }
    header("Location: etablir_criteres.php");
    exit;
}

// Insertion de nouveaux critères
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['modifier_id']) && isset($_POST['nom_critere']) && isset($_POST['note_max'])) {
    $nom_criteres = $_POST['nom_critere'];
    $notes_max = $_POST['note_max'];

    $stmt = $pdo->prepare("INSERT INTO criteres (nom_critere, note_max) VALUES (:nom_critere, :note_max)");

    for ($i = 0; $i < count($nom_criteres); $i++) {
        $nom = trim($nom_criteres[$i]);
        $note = (int)$notes_max[$i];

        if ($nom !== '' && $note > 0) {
            $stmt->execute([
                ':nom_critere' => $nom,
                ':note_max' => $note
            ]);
        }
    }
}

// Récupération des critères existants
$criteres = $pdo->query("SELECT * FROM criteres")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Grille d'évaluation</title>
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
        h1, h2 {
            text-align: center;
            color: #a00f28;
        }
        .espace-criteres {
            margin-top: 60px;
        }
        table {
            width: 80%;
            margin: 0 auto 20px;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #999;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #a00f28;
            color: white;
        }
        input[type="text"], input[type="number"] {
            width: 90%;
            padding: 5px;
        }
        .btn {
            display: block;
            margin: 10px auto;
            padding: 10px 20px;
            background-color: #a00f28;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #8b0d22;
        }
        .btn-danger {
            background-color: #c0392b;
            padding: 5px 10px;
            font-size: 0.9em;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            margin: 2px;
        }
        .btn-danger:hover {
            background-color: #a93226;
        }

        /* Bouton "Revenir à l’espace Professeur" */
        a.btn-retour {
            display: block;
            width: 180px;       /* largeur réduite */
            margin: 80px auto 20px auto; /* marge haute plus grande, centrage horizontal, marge basse */
            padding: 8px 10px;  /* padding ajusté */
            background-color: #a00f28;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }
        a.btn-retour:hover {
            background-color: #8b0d22;
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
<br>
<h1>Établir la grille d'évaluation</h1>

<?php if ($modifier_critere): ?>
    <h2>Modifier un critère</h2>
    <form method="POST">
        <input type="hidden" name="modifier_id" value="<?= $modifier_critere['critere_id'] ?>">
        <table>
            <tr>
                <th>Nom du critère</th>
                <th>Note maximale</th>
            </tr>
            <tr>
                <td><input type="text" name="modifier_nom" required value="<?= htmlspecialchars($modifier_critere['nom_critere']) ?>"></td>
                <td><input type="number" name="modifier_note" required min="1" value="<?= htmlspecialchars($modifier_critere['note_max']) ?>"></td>
            </tr>
        </table>
        <button type="submit" class="btn">Enregistrer les modifications</button>
    </form>
<?php else: ?>
    <form method="POST">
        <table id="grille">
            <thead>
                <tr>
                    <th>Nom du critère</th>
                    <th>Note maximale</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" name="nom_critere[]" required></td>
                    <td><input type="number" name="note_max[]" required min="1"></td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn" onclick="ajouterLigne()">Ajouter un critère</button>
        <button type="submit" class="btn">Enregistrer</button>
    </form>
<?php endif; ?>


<h2 class="espace-criteres">Critères enregistrés</h2>
<table>
    <thead>
        <tr>
            <th>Nom du critère</th>
            <th>Note maximale</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($criteres as $critere): ?>
            <tr>
                <td><?= htmlspecialchars($critere['nom_critere']) ?></td>
                <td><?= htmlspecialchars($critere['note_max']) ?></td>
                <td>
                    <a href="?supprimer=<?= $critere['critere_id'] ?>" class="btn-danger">Supprimer</a>
                    <a href="?modifier=<?= $critere['critere_id'] ?>" class="btn-danger">Modifier</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href="professeur.php" class="btn btn-retour">Revenir à l’espace Professeur</a>

<script>
    function ajouterLigne() {
        const tbody = document.querySelector('#grille tbody');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td><input type="text" name="nom_critere[]" required></td>
            <td><input type="number" name="note_max[]" required min="1"></td>
        `;
        tbody.appendChild(newRow);
    }
</script>

</body>
</html>
