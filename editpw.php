<?php 
include 'configPDO.php';
session_start();

// Controllo login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// 👇 Aggiungi queste due variabili per il tuo header
$isLogged = isset($_SESSION["user_id"]);
$username = $isLogged ? $_SESSION["username"] : null;

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_password = $_POST["old_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $message = "❌ Compila tutti i campi.";
    } elseif ($new_password !== $confirm_password) {
        $message = "❌ Le nuove password non coincidono!";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/', $new_password)) {
        $message = "❌ La nuova password deve contenere almeno 6 caratteri, una lettera maiuscola, un numero e un carattere speciale.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id");
            $stmt->bindParam(":id", $_SESSION["user_id"]);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($old_password, $user["password_hash"])) {
                $newPasswordHash = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET password_hash = :newpw WHERE id = :id");
                $update->bindParam(":newpw", $newPasswordHash);
                $update->bindParam(":id", $_SESSION["user_id"]);
                $update->execute();

                session_destroy();
                header("Location: login.php");
                exit;
            } else {
                $message = "❌ La vecchia password non è corretta.";
            }

        } catch (PDOException $e) {
            $message = "❌ Errore DB: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cambia Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
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
                        <h2 class="card-title text-center mb-4">Cambia Password</h2>

                        <?php if (!empty($message)): ?>
                            <div class="alert alert-info text-center" role="alert">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="old_password" class="form-label">Vecchia Password</label>
                                <input type="password" class="form-control" id="old_password" name="old_password" required />
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">Nuova Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required />
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Conferma Nuova Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required />
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">Aggiorna Password</button>
                            </div>
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