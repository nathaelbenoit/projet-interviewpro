<?php
session_start();
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Connexion à la base de données
    $host = "localhost";
    $dbname = "nnn_sae";
    $username = "root";
    $password = "";

    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connexion échouée : " . $conn->connect_error);
    }

    $email = $conn->real_escape_string($_POST["email"]);
    $mdp = $conn->real_escape_string($_POST["motdepasse"]);
    $role = $conn->real_escape_string($_POST["role"]);

    // Mise à jour de la requête pour correspondre aux noms des colonnes dans la base de données
    if ($role === "etudiant" || $role === "enseignant") {
        $sql = "SELECT * FROM users WHERE utilisateur_email = ? AND motdepasse = ? AND role = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $email, $mdp, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // ✅ Enregistrer le rôle et l'email dans la session
            $_SESSION['role'] = $role;
            $_SESSION['email'] = $email;

            if ($role === "etudiant") {
                header("Location: etudiant.php");
                exit();
            } elseif ($role === "enseignant") {
                header("Location: professeur.php");
                exit();
            }
        } else {
            $error = "Email, mot de passe ou rôle incorrect.";
        }

        $stmt->close();
    }
    // Si le rôle est "professionnel", vérifier dans la table professionnels
    elseif ($role === "professionnel") {
        $sql = "SELECT * FROM professionnels WHERE pro_email = ? AND motdepasse = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $mdp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // ✅ Enregistrer le rôle et l'email dans la session
            $_SESSION['role'] = "professionnel";
            $_SESSION['email'] = $email;

            header("Location: professionnel.html");
            exit();
        } else {
            $error = "Email ou mot de passe incorrect.";
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5dc;
            color: #a00f28;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #ffffff;
            border: 2px solid #a00f28;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 300px;
            text-align: center;
        }
        .login-container h1 {
            margin-bottom: 20px;
            color: #a00f28;
        }
        .login-container input,
        .login-container select {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .login-container button {
            background-color: #a00f28;
            color: #ffffff;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        .login-container .direct-access {
            background-color: transparent;
            color: #a00f28;
            border: none;
            text-decoration: underline;
            cursor: pointer;
            margin-top: 20px;
            font-size: 16px;
        }
        .error {
            color: red;
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <img src="./images/Logo.png" alt="Logo sans fond">
<div class="login-container">
    <h1>Connexion</h1>
    <?php if (!empty($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="motdepasse" placeholder="Mot de passe" required>
        <select name="role" required>
            <option value="" disabled selected>Choisir un rôle</option>
            <option value="etudiant">Étudiant</option>
            <option value="enseignant">Enseignant</option>
        </select>
        <button type="submit">Se connecter</button>
    </form>
    <a href="pro.php"><button class="direct-access">Se connecter en tant qu'invité</button></a>
</div>
<img src="./images/Logo.png" alt="Logo avec slogan">
</body>
</html>
