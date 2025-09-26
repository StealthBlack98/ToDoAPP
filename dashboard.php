<?php
include 'configPDO.php';
session_start();

// Controllo login
$isLogged = isset($_SESSION["user_id"]);
$username = $isLogged ? $_SESSION["username"] : null;
$user_id = $isLogged ? $_SESSION["user_id"] : null;
$message = "";

// Se l'utente è loggato, gestisco le task
if($isLogged){
    // Aggiunta nuova task
    if (isset($_POST["add_task"])) {
        $title = trim($_POST["title"]);
        $description = trim($_POST["description"]);
        $priority = !empty($_POST["priority"]) ? $_POST["priority"] : null;

        if (!empty($title)) {
            $stmt = $pdo->prepare("INSERT INTO task (user_id, title, description, priority) VALUES (:user_id, :title, :description, :priority)");
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":description", $description);
            $stmt->bindParam(":priority", $priority);
            $stmt->execute();
            $message = "✅ Task aggiunta!";
        } else {
            $message = "❌ Il titolo non può essere vuoto.";
        }
    }

    // Aggiorna stato task
    if (isset($_POST["update_status"])) {
        $task_id = $_POST["task_id"];
        $new_status = $_POST["status"];
        $stmt = $pdo->prepare("UPDATE task SET status = :status WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(":status", $new_status);
        $stmt->bindParam(":id", $task_id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $message = "🔄 Stato aggiornato!";
    }

    // Modifica task
    if (isset($_POST["edit_task"])) {
        $task_id = $_POST["task_id"];
        $new_title = trim($_POST["title"]);
        $new_description = trim($_POST["description"]);
        $new_priority = !empty($_POST["priority"]) ? $_POST["priority"] : null;

        if (!empty($new_title)) {
            $stmt = $pdo->prepare("UPDATE task SET title = :title, description = :description, priority = :priority WHERE id = :id AND user_id = :user_id");
            $stmt->bindParam(":title", $new_title);
            $stmt->bindParam(":description", $new_description);
            $stmt->bindParam(":priority", $new_priority);
            $stmt->bindParam(":id", $task_id);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            $message = "✏️ Task modificata!";
        } else {
            $message = "❌ Il titolo non può essere vuoto.";
        }
    }

    // Elimina task
    if (isset($_POST["delete_task"])) {
        $task_id = $_POST["task_id"];
        $stmt = $pdo->prepare("DELETE FROM task WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(":id", $task_id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $message = "🗑️ Task eliminata!";
    }

    // Gestione filtro priorità
    $filter_priority = isset($_GET["filter_priority"]) ? $_GET["filter_priority"] : "";
    $valid_priorities = ["alta","media","bassa"];
    if($filter_priority && in_array($filter_priority, $valid_priorities)) {
        $stmt = $pdo->prepare("
            SELECT * FROM task 
            WHERE user_id = :user_id AND priority = :priority
            ORDER BY CASE priority
                        WHEN 'alta' THEN 1
                        WHEN 'media' THEN 2
                        WHEN 'bassa' THEN 3
                        ELSE 4
                     END, id DESC
        ");
        $stmt->bindParam(":priority", $filter_priority);
    } else {
        $stmt = $pdo->prepare("
            SELECT * FROM task 
            WHERE user_id = :user_id
            ORDER BY CASE priority
                        WHEN 'alta' THEN 1
                        WHEN 'media' THEN 2
                        WHEN 'bassa' THEN 3
                        ELSE 4
                     END, id DESC
        ");
    }
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - To Do List</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="asset/style.css">
</head>
<body class="<?php echo (isset($_COOKIE['theme']) && $_COOKIE['theme']=='dark') ? 'dark-mode' : 'bg-light'; ?> d-flex flex-column min-vh-100">

<?php include "partial/header.php"; ?>

<div class="flex-grow-1">
    <!-- Flash message -->
    <?php if(!empty($message)): ?>
        <div id="flash-message" class="alert alert-info text-center position-fixed w-75 start-50 translate-middle-x mt-3 shadow-lg rounded-3" style="z-index:1050;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Se non loggato -->
    <?php if(!$isLogged): ?>
        <div class="welcome-bg">
            <div class="welcome-bg-overlay d-flex align-items-center justify-content-center text-white">
                <div class="text-center p-5">
                    <h1 class="fw-bold display-3 mb-4">🚀 Organizza le tue giornate con ToDoApp!</h1>
                    <p class="lead mb-5">Tieni traccia di tutto ciò che devi fare in modo semplice ed efficiente.</p>
                    <a href="register.php" class="btn btn-warning btn-lg px-5 py-3 rounded-pill text-uppercase fw-bold shadow-sm">Registrati subito</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Dashboard loggato -->
        <div class="container my-5">
            <h2 class="mb-5 text-center fw-bold text-primary">Gestione Task <i class="bi bi-clipboard-check"></i></h2>

            <!-- Aggiungi task -->
            <div class="card shadow-lg mb-5 rounded-3">
                <div class="card-body p-5">
                    <h4 class="card-title mb-4 text-center text-primary">Aggiungi nuova task <i class="bi bi-plus-circle"></i></h4>
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="title" class="form-label fw-bold text-secondary">Titolo</label>
                            <div class="input-group">
                                <span class="input-group-text rounded-start-pill bg-light"><i class="bi bi-tag"></i></span>
                                <input type="text" name="title" id="title" class="form-control rounded-end-pill" placeholder="Es. Preparare la presentazione" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold text-secondary">Descrizione</label>
                            <div class="input-group">
                                <span class="input-group-text rounded-start-pill bg-light"><i class="bi bi-journal-text"></i></span>
                                <textarea name="description" id="description" class="form-control rounded-end-pill" placeholder="Dettagli aggiuntivi"></textarea>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="priority" class="form-label fw-bold text-secondary">Priorità</label>
                            <div class="input-group">
                                <span class="input-group-text rounded-start-pill bg-light"><i class="bi bi-exclamation-triangle"></i></span>
                                <select name="priority" id="priority" class="form-select rounded-end-pill">
                                    <option value="">Nessuna</option>
                                    <option value="bassa">Bassa</option>
                                    <option value="media">Media</option>
                                    <option value="alta">Alta</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="add_task" class="btn btn-primary btn-lg rounded-pill"><i class="bi bi-plus-lg me-2"></i> Aggiungi Task</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista task -->
            <h3 class="mb-4 text-center fw-bold text-primary">Le tue task <i class="bi bi-list-task"></i></h3>

            <div class="d-flex justify-content-center mb-4">
                <form method="get" action="" class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 fw-bold text-secondary">Filtra per priorità:</label>
                    <select name="filter_priority" class="form-select form-select-sm rounded-pill" style="width:auto;">
                        <option value="" <?php if($filter_priority==="") echo "selected"; ?>>Tutte</option>
                        <option value="alta" <?php if($filter_priority==="alta") echo "selected"; ?>>Alta</option>
                        <option value="media" <?php if($filter_priority==="media") echo "selected"; ?>>Media</option>
                        <option value="bassa" <?php if($filter_priority==="bassa") echo "selected"; ?>>Bassa</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill">Applica</button>
                </form>
            </div>

            <!-- Task cards -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach($tasks as $task): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm rounded-3">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0 text-truncate fw-bold text-primary"><?php echo htmlspecialchars($task["title"]); ?></h5>
                                    <div class="flex-shrink-0 ms-2 d-flex flex-column align-items-end">
                                        <span class="badge rounded-pill mb-1 <?php 
                                            echo ($task["status"]=="completata") ? "bg-success" : 
                                                 (($task["status"]=="in corso") ? "bg-warning text-dark" : "bg-secondary");
                                        ?>">
                                            <?php echo ucfirst($task["status"]); ?>
                                        </span>
                                        <?php if(isset($task["priority"])): ?>
                                            <span class="badge rounded-pill <?php 
                                                echo ($task["priority"]=="alta") ? "bg-danger" : 
                                                     (($task["priority"]=="media") ? "bg-warning" : "bg-info");
                                            ?>">
                                                <?php echo ucfirst($task["priority"]); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <p class="card-text text-muted mb-3 small"><?php echo nl2br(htmlspecialchars($task["description"])); ?></p>
                                <div class="d-flex flex-wrap gap-2 mt-auto pt-3 border-top">
                                    <form method="post" action="" class="d-flex gap-2">
                                        <input type="hidden" name="task_id" value="<?php echo $task["id"]; ?>">
                                        <select name="status" class="form-select form-select-sm rounded-pill">
                                            <option value="da fare" <?php if($task["status"]=="da fare") echo "selected"; ?>>Da fare</option>
                                            <option value="in corso" <?php if($task["status"]=="in corso") echo "selected"; ?>>In corso</option>
                                            <option value="in revisione" <?php if($task["status"]=="in revisione") echo "selected"; ?>>In revisione</option>
                                            <option value="completata" <?php if($task["status"]=="completata") echo "selected"; ?>>Completata</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-sm btn-outline-primary rounded-pill"><i class="bi bi-arrow-repeat"></i></button>
                                    </form>
                                    <button class="btn btn-sm btn-outline-warning rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#editForm<?php echo $task["id"]; ?>"><i class="bi bi-pencil"></i> Modifica</button>
                                    <form method="post" action="">
                                        <input type="hidden" name="task_id" value="<?php echo $task["id"]; ?>">
                                        <button type="submit" name="delete_task" class="btn btn-sm btn-outline-danger rounded-pill"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>

                                <div class="collapse mt-3" id="editForm<?php echo $task["id"]; ?>">
                                    <div class="card card-body bg-light p-3">
                                        <form method="post" action="" class="d-grid gap-2">
                                            <input type="hidden" name="task_id" value="<?php echo $task["id"]; ?>">
                                            <input type="text" name="title" value="<?php echo htmlspecialchars($task["title"]); ?>" class="form-control form-control-sm mb-2" required>
                                            <textarea name="description" class="form-control form-control-sm mb-2"><?php echo htmlspecialchars($task["description"]); ?></textarea>
                                            <select name="priority" class="form-select form-select-sm mb-2">
                                                <option value="" <?php if($task["priority"]==="") echo "selected"; ?>>Nessuna</option>
                                                <option value="bassa" <?php if($task["priority"]==="bassa") echo "selected"; ?>>Bassa</option>
                                                <option value="media" <?php if($task["priority"]==="media") echo "selected"; ?>>Media</option>
                                                <option value="alta" <?php if($task["priority"]==="alta") echo "selected"; ?>>Alta</option>
                                            </select>
                                            <button type="submit" name="edit_task" class="btn btn-sm btn-success"><i class="bi bi-save"></i> Salva</button>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    <?php endif; ?>
</div>

<?php include "partial/footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="asset/script.js"></script>
</body>
</html>