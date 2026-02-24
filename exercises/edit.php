<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 1. Pobieramy dane ćwiczenia
$stmt = $pdo->prepare("SELECT * FROM fit_exercises WHERE id = ?");
$stmt->execute([$id]);
$ex = $stmt->fetch();

if (!$ex) die("Nie znaleziono ćwiczenia.");

// 2. Obsługa zapisu
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nazwa = $_POST['nazwa'];
    $opis = $_POST['opis'];
    $yt = $_POST['youtube_link'];
    $garmin = $_POST['garmin_exercise_link'];
    $image_name = $ex['image_path']; // Domyślnie zostaje stary obrazek

    // Jeśli wgrano nowy plik
    if (isset($_FILES['exercise_img']) && $_FILES['exercise_img']['error'] == 0) {
        $ext = pathinfo($_FILES['exercise_img']['name'], PATHINFO_EXTENSION);
        $image_name = time() . '_' . uniqid() . '.' . $ext;
        $target = $root . '/uploads/exercises/' . $image_name;
        
        if (move_uploaded_file($_FILES['exercise_img']['tmp_name'], $target)) {
            // Opcjonalnie: usuwamy stary plik z serwera, żeby nie śmiecić
            if ($ex['image_path'] && file_exists($root . '/uploads/exercises/' . $ex['image_path'])) {
                unlink($root . '/uploads/exercises/' . $ex['image_path']);
            }
        }
    }

    $stmt = $pdo->prepare("UPDATE fit_exercises SET nazwa = ?, opis = ?, youtube_link = ?, garmin_exercise_link = ?, image_path = ? WHERE id = ?");
    if ($stmt->execute([$nazwa, $opis, $yt, $garmin, $image_name, $id])) {
        header("Location: list.php?msg=updated");
        exit;
    }
}

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow mb-4" style="max-width: 800px;">
        <div class="card-header py-3 bg-info text-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">Edytuj Ćwiczenie: <?= htmlspecialchars($ex['nazwa']) ?></h6>
            <a href="list.php" class="btn btn-sm btn-light">Wróć do listy</a>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nazwa ćwiczenia</label>
                            <input type="text" name="nazwa" class="form-control" 
                                   value="<?= htmlspecialchars($ex['nazwa']) ?>" 
                                   placeholder="np. Martwy ciąg na prostych nogach" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nowy Obrazek (opcjonalnie)</label>
                            <input type="file" name="exercise_img" class="form-control" accept="image/*">
                            <div class="form-text">Wybierz plik tylko, jeśli chcesz zmienić obecne zdjęcie.</div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 text-center">
                        <label class="form-label fw-bold d-block">Aktualny podgląd</label>
                        <?php if($ex['image_path']): ?>
                            <img src="/uploads/exercises/<?= $ex['image_path'] ?>" class="img-thumbnail shadow-sm" style="max-height: 120px;">
                        <?php else: ?>
                            <div class="border rounded d-flex align-items-center justify-content-center bg-light" style="height: 120px;">
                                <span class="text-muted small">Brak zdjęcia</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Opis techniczny</label>
                    <textarea name="opis" class="form-control" rows="3" 
                              placeholder="Dodaj wskazówki dotyczące techniki..."><?= htmlspecialchars($ex['opis'] ?? '') ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Link YouTube</label>
                        <input type="url" name="youtube_link" class="form-control" 
                               value="<?= htmlspecialchars($ex['youtube_link'] ?? '') ?>"
                               placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Link Garmin Connect</label>
                        <input type="url" name="garmin_exercise_link" class="form-control" 
                               value="<?= htmlspecialchars($ex['garmin_exercise_link'] ?? '') ?>"
                               placeholder="https://connect.garmin.com/...">
                    </div>
                </div>

                <hr>
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary px-5 shadow">Zaktualizuj dane</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include $root . '/includes/footer.php'; ?>