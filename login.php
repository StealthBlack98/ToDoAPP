<?php 
include 'configPDO.php'; // Connessione al DB
session_start();

// variabili per l'header
$isLogged = isset($_SESSION["user_id"]);
$username = $isLogged ? $_SESSION["username"] : null;

$message = ""; // messaggi di errore o successo

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = trim($_POST["login"]); // può essere username O email
    $password = $_POST["password"];

    if (empty($login) || empty($password)) {
        $message = "❌ Inserisci username/email e password.";
    } else {
        try {
            // Cerco l'utente in base a username o email
            $stmt = $pdo->prepare("
                SELECT * FROM users 
                WHERE username = :username OR email = :email
                LIMIT 1
            ");
            $stmt->bindParam(":username", $login);
            $stmt->bindParam(":email", $login);                        
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Verifico la password inserita con quella salvata (hash)
                if (password_verify($password, $user["password_hash"])) {
                    // Avvio la sessione
                    $_SESSION["user_id"] = $user["id"];
                    $_SESSION["username"] = $user["username"];

                    // ✅ Login corretto → redirect alla dashboard
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $message = "❌ Password errata.";
                }
            } else {
                $message = "❌ Nessun utente trovato con questo username/email.";
            }
        } catch (PDOException $e) {
            $message = "❌ Errore DB: " . $e->getMessage();
        }
    }
}
?>

<!-- FORM HTML -->
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Link al CSS di Bootstrap tramite CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="asset/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
<?php include "partial/header.php";?>

<div class="flex-grow-1 d-flex align-items-center">
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4">Login</h2>

                        <?php if (!empty($message)): ?>
                            <div class="alert alert-info text-center" role="alert">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username o Email</label>
                                <input type="text" class="form-control" name="login" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">Accedi</button>
                            </div>
                            <p class="text-center mt-3">Non hai un account? <a href="register.php">Registrati qui</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "partial/footer.php" ;?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="asset/script.js"></script>
</body>
</html>