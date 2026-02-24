<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nazwa = $_POST['nazwa'];
    $opis = $_POST['opis'];

    $stmt = $pdo->prepare("INSERT INTO fit_groups (nazwa, opis) VALUES (?, ?)");
    if ($stmt->execute([$nazwa, $opis])) {
        header("Location: list.php?msg=success");
        exit;
    }
}

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow mb-4" style="max-width: 600px;">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">Dodaj nową kategorię grupy</h6>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nazwa Grupy</label>
                    <input type="text" name="nazwa" class="form-control" placeholder="np. Zawodnicy BJJ" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Opis / Uwagi</label>
                    <textarea name="opis" class="form-control" rows="3" placeholder="Dodatkowe informacje o poziomie zaawansowania itp."></textarea>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <a href="list.php" class="btn btn-secondary">Wróć</a>
                    <button type="submit" class="btn btn-success px-4">Zapisz</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include $root . '/includes/footer.php'; ?>