<?php
session_start();

// Connexion à la base de données
$host = "localhost";
$dbname = "nnn_sae";
$username = "root";  // à adapter si besoin
$password = "";      // à adapter si besoin

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=latin1", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Initialiser les variables de filtrage
    $nom = isset($_GET['nom']) ? $_GET['nom'] : '';
    $prenom = isset($_GET['prenom']) ? $_GET['prenom'] : '';
    $email = isset($_GET['email']) ? $_GET['email'] : '';
    $metier = isset($_GET['metier']) ? $_GET['metier'] : '';
    $entreprise = isset($_GET['entreprise']) ? $_GET['entreprise'] : '';

    // Construction de la requête SQL avec des conditions dynamiques selon les critères de recherche
    $sql = "SELECT nom, prenom, pro_email, metier, entreprise, linkedin FROM professionnels WHERE 1";

    // Filtrage par nom
    if (!empty($nom)) {
        $sql .= " AND nom LIKE :nom";
    }

    // Filtrage par prénom
    if (!empty($prenom)) {
        $sql .= " AND prenom LIKE :prenom";
    }

    // Filtrage par métier
    if (!empty($metier)) {
        $sql .= " AND metier LIKE :metier";
    }

    // Filtrage par entreprise
    if (!empty($entreprise)) {
        $sql .= " AND entreprise LIKE :entreprise";
    }

    $stmt = $pdo->prepare($sql);

    // Lier les paramètres de filtrage à la requête préparée
    if (!empty($nom)) {
        $stmt->bindValue(':nom', '%' . $nom . '%');
    }
    if (!empty($prenom)) {
        $stmt->bindValue(':prenom', '%' . $prenom . '%');
    }
    if (!empty($metier)) {
        $stmt->bindValue(':metier', '%' . $metier . '%');
    }
    if (!empty($entreprise)) {
        $stmt->bindValue(':entreprise', '%' . $entreprise . '%');
    }

    $stmt->execute();
    $professionnels = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professionnels</title>
    <style>
        body {
            font-family: Arial, sans-serif;
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
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #a00f28;
            color: #ffffff;
        }
        input[type="text"] {
            padding: 6px;
            margin: 10px 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 13px;
            width: calc(15% - 20px); /* Réduction de la taille des champs */
        }
        button {
            padding: 6px 12px;
            background-color: #a00f28;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
        }
        button:hover {
            background-color: #8c0e1f;
        }
        .error {
            color: #e74c3c;
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
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
            <!-- Navigation accessible à tous, même non connecté -->
             <?php if (isset($_SESSION['role'])): ?>
                <?php if ($_SESSION['role'] === 'etudiant'): ?>
                    <a href="etudiant.php">Espace Étudiant</a>
                <?php elseif ($_SESSION['role'] === 'enseignant'): ?>
                    <a href="professeur.php">Espace Professeur</a>
                <?php endif; ?>
            <?php endif; ?>
            <a href="pro.php">Professionnels</a>
            <a href="<?= isset($_SESSION['role']) ? 'compte.php' : 'login.php' ?>">
                <?= isset($_SESSION['role']) ? 'Mon compte' : 'Se connecter' ?>
            </a>
        </nav>
        <img src="./images/LogoSlogan.png" alt="Logo Slogan" class="header-logo-right">
    </header>

    <div class="container">
        <h1>Liste des Professionnels</h1>
        
        <!-- Formulaire de recherche -->
        <form method="GET">
            <input type="text" name="nom" placeholder="Rechercher par nom" value="<?= htmlspecialchars($nom) ?>">
            <input type="text" name="prenom" placeholder="Rechercher par prénom" value="<?= htmlspecialchars($prenom) ?>">
            <input type="text" name="email" placeholder="Rechercher par email" value="<?= htmlspecialchars($email) ?>">
            <input type="text" name="metier" placeholder="Rechercher par métier" value="<?= htmlspecialchars($metier) ?>">
            <input type="text" name="entreprise" placeholder="Rechercher par entreprise" value="<?= htmlspecialchars($entreprise) ?>">
            <button type="submit">Filtrer</button>
        </form>

        <?php if (empty($professionnels)): ?>
            <div class="error">Aucun professionnel trouvé pour les critères de recherche.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Métier</th>
                        <th>Entreprise</th>
                        <th>LinkedIn</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($professionnels as $pro): ?>
                        <tr>
                            <td><?= htmlspecialchars($pro['nom']) ?></td>
                            <td><?= htmlspecialchars($pro['prenom']) ?></td>
                            <td><?= htmlspecialchars($pro['pro_email']) ?></td>
                            <td><?= htmlspecialchars($pro['metier']) ?></td>
                            <td><?= htmlspecialchars($pro['entreprise']) ?></td>
                            <td>
                                <?php if (!empty($pro['linkedin'])): ?>
                                    <a href="<?= htmlspecialchars($pro['linkedin']) ?>" target="_blank">Voir le profil</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
