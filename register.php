<?php 
include 'configPDO.php';
session_start();

// variabili per l'header
$isLogged = isset($_SESSION["user_id"]);
$username = $isLogged ? $_SESSION["username"] : null;

$message = "";

// Gestione registrazione
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Controllo campi vuoti
    if (empty($username) || empty($email) || empty($password)) {
        $message = "❌ Compila tutti i campi.";
    } 
    // Controllo password sicura
    elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/', $password)) {
        $message = "❌ La password deve contenere almeno 6 caratteri, una lettera maiuscola, un numero e un carattere speciale.";
    } 
    else {
        // Hash della password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Controllo username/email già esistenti
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                $message = "❌ Username o email già esistenti.";
            } else {
                // Inserimento nuovo utente
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) 
                                       VALUES (:username, :email, :password_hash)");
                $stmt->bindParam(":username", $username);
                $stmt->bindParam(":email", $email);
                $stmt->bindParam(":password_hash", $passwordHash);
                $stmt->execute();

                // Registrazione corretta → redirect al login
                header("Location: login.php");
                exit;
            }

        } catch (PDOException $e) {
            $message = "❌ Errore: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="asset/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

<?php include "partial/header.php"; ?>

<div class="flex-grow-1 d-flex align-items-center">
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4">Registrazione</h2>

                        <?php if (!empty($message)): ?>
                            <div class="alert alert-info text-center" role="alert">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">Registrati</button>
                            </div>
                            <p class="text-center mt-3">Se sei già registrato <a href="login.php">clicca qui</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "partial/footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="asset/script.js"></script>
</body>
</html>