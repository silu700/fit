<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nazwa = $_POST['nazwa'];
    $godzina = $_POST['godzina'];
    $opis = $_POST['opis'];

    $sql = "INSERT INTO fit_groups (nazwa, godzina, opis) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$nazwa, $godzina, $opis])) {
        header("Location: list.php?msg=added");
        exit;
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="list.php">Grupy</a></li>
            <li class="breadcrumb-item active">Nowa grupa</li>
        </ol>
    </nav>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Dodaj nowy slot godzinowy (Grupę)</h6>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nazwa grupy</label>
                        <input type="text" name="nazwa" class="form-control" placeholder="np. G1 lub Poranna" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Godzina zajęć</label>
                        <input type="time" name="godzina" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Opis (opcjonalnie)</label>
                        <input type="text" name="opis" class="form-control" placeholder="np. Grupa zaawansowana">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-2">Zapisz grupę</button>
                <a href="list.php" class="btn btn-secondary mt-2">Anuluj</a>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>