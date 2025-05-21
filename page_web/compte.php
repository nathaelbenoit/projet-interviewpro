<?php
session_start();

// Redirection si l'utilisateur n'est pas connecté
if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
$host = "localhost";
$dbname = "nnn_sae";
$username = "root";
$password = "";
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Récupération des informations de l'utilisateur
$email = $_SESSION['email'];
$sql = "SELECT nom, prenom, utilisateur_email, role, motdepasse FROM users WHERE utilisateur_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    die("Utilisateur introuvable.");
}

$message = "";

// Modification du mot de passe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["nouveau_mdp"])) {
    $nouveau_mdp = $_POST["nouveau_mdp"];

    $update_sql = "UPDATE users SET motdepasse = ? WHERE utilisateur_email = ?";
    $update_stmt = $conn->prepare($update_sql);
    if ($update_stmt) {
        $update_stmt->bind_param("ss", $nouveau_mdp, $email);
        if ($update_stmt->execute()) {
            $message = "Mot de passe mis à jour avec succès.";
            // Recharger les infos utilisateur
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
            }
        } else {
            $message = "Erreur lors de la mise à jour.";
        }
        $update_stmt->close();
    } else {
        $message = "Erreur dans la préparation de la requête : " . $conn->error;
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Compte</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5dc;
            color: #a00f28;
            margin: 0;
            padding: 0;
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
        nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 10px;
            font-weight: bold;
        }
        nav a:hover {
            text-decoration: underline;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fffaf0;
            border: 1px solid #a00f28;
            border-radius: 10px;
        }
        h1 {
            text-align: center;
        }
        p {
            font-size: 16px;
        }
        form {
            margin-top: 20px;
        }
        label, input {
            display: block;
            width: 100%;
            margin: 10px 0;
        }
        input[type="password"], input[type="text"], input[type="submit"] {
            padding: 8px;
            border: 1px solid #a00f28;
            border-radius: 5px;
        }
        input[type="submit"] {
            background-color: #a00f28;
            color: white;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #800c20;
        }

        .message {
            margin-top: 15px;
            font-weight: bold;
            color: green;
        }
        .deco-btn {
            margin-top: 30px;
            text-align: center;
        }
        .deco-btn a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #a00f28;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .deco-btn a:hover {
            background-color: #800c20;
        }

        /* Style pour le bouton de bascule */
        .toggle-password-btn {
            background-color: #b30000;
            color: white;
            border: none;
            padding: 5px 15px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 5px;
            margin-left: 10px;
        }

        .toggle-password-btn:hover {
            background-color: #800c20;
        }

        .password-container {
            display: flex;
            align-items: center;
        }
    </style>
    <script>
        function togglePassword() {
            var passwordField = document.getElementById("nouveau_mdp");
            var passwordToggle = document.getElementById("password_toggle");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                passwordToggle.innerText = "Cacher";
            } else {
                passwordField.type = "password";
                passwordToggle.innerText = "Voir";
            }
        }
    </script>
</head>
<body>
    <header>
        <div class="header-left-group">
            <img src="./images/logoSrien.png" alt="Logo sans rien" class="header-logo-left">
            <img src="./images/logoNom.png" alt="Logo Nom" class="header-logo-left header-logo-nom">
        </div>
        <nav>
            <?php if ($_SESSION['role'] === 'etudiant'): ?>
                <a href="etudiant.php">Espace Étudiant</a>
            <?php elseif ($_SESSION['role'] === 'enseignant'): ?>
                <a href="professeur.php">Espace Professeur</a>
            <?php endif; ?>
            <a href="pro.php">Professionnels</a>
            <a href="compte.php">Mon compte</a>
        </nav>
        <img src="./images/LogoSlogan.png" alt="Logo Slogan" class="header-logo-right">
    </header>

    <div class="container">
        <h1>Mon Compte</h1>
        <p><strong>Nom :</strong> <?= htmlspecialchars($user['nom']) ?></p>
        <p><strong>Prénom :</strong> <?= htmlspecialchars($user['prenom']) ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($user['utilisateur_email']) ?></p>
        <p><strong>Rôle :</strong> <?= htmlspecialchars($user['role']) ?></p>
        
        <form method="POST">
            <label for="nouveau_mdp">Nouveau mot de passe :</label>
            <div class="password-container">
                <input type="password" id="nouveau_mdp" name="nouveau_mdp" value="<?= htmlspecialchars($user['motdepasse']) ?>" required>
                <button type="button" id="password_toggle" class="toggle-password-btn" onclick="togglePassword()">Voir</button>
            </div>
            <input type="submit" value="Changer le mot de passe">
        </form>

        <?php if (!empty($message)): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <div class="deco-btn">
            <a href="logout.php">Se déconnecter</a>
        </div>
    </div>
</body>
</html>
