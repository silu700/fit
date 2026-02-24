<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nazwa = $_POST['nazwa'];
    $godzina = $_POST['godzina'];
    $opis = $_POST['opis'];

    $sql = "INSERT INTO fit_groups (nazwa, godzina, opis) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$nazwa, $godzina, $opis])) {
        header("Location: list.php?msg=success");
        exit;
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white text-primary">
            <h6 class="m-0 font-weight-bold">Konfiguracja nowej grupy (Slotu)</h6>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nazwa (np. G1, Poranna)</label>
                        <input type="text" name="nazwa" class="form-control" required placeholder="np. G1">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Godzina zajęć</label>
                        <input type="time" name="godzina" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Opis dodatkowy</label>
                    <textarea name="opis" class="form-control" rows="2" placeholder="Opcjonalny opis..."></textarea>
                </div>
                <hr>
                <button type="submit" class="btn btn-primary px-4">Zapisz grupę</button>
                <a href="list.php" class="btn btn-outline-secondary">Anuluj</a>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>