<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nazwa = $_POST['nazwa'];
    $opis = $_POST['opis'];
    $youtube = $_POST['youtube_link'];
    $garmin = $_POST['garmin_exercise_link'];

    $sql = "INSERT INTO fit_exercises (nazwa, opis, youtube_link, garmin_exercise_link) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$nazwa, $opis, $youtube, $garmin])) {
        header("Location: list.php?msg=success");
        exit;
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Nowe Ćwiczenie</h6>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nazwa ćwiczenia</label>
                    <input type="text" name="nazwa" class="form-control" placeholder="np. Przysiad ze sztangą" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Opis / Technika</label>
                    <textarea name="opis" class="form-control" rows="3"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Link YouTube (Wideo)</label>
                        <input type="url" name="youtube_link" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Link Garmin (Baza ćwiczeń)</label>
                        <input type="url" name="garmin_exercise_link" class="form-control" placeholder="https://connect.garmin.com/...">
                    </div>
                </div>
                <hr>
                <button type="submit" class="btn btn-primary px-4">Zapisz ćwiczenie</button>
                <a href="list.php" class="btn btn-outline-secondary">Anuluj</a>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>