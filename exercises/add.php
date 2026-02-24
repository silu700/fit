<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nazwa = $_POST['nazwa'];
    $opis = $_POST['opis'];
    $yt = $_POST['youtube_link'];
    $garmin = $_POST['garmin_exercise_link'];
    
    $image_name = null;

    if (isset($_FILES['exercise_img']) && $_FILES['exercise_img']['error'] == 0) {
        $ext = pathinfo($_FILES['exercise_img']['name'], PATHINFO_EXTENSION);
        $image_name = time() . '_' . uniqid() . '.' . $ext;
        $target = $root . '/uploads/exercises/' . $image_name;
        
        if (!is_dir($root . '/uploads/exercises/')) {
            mkdir($root . '/uploads/exercises/', 0777, true);
        }
        
        move_uploaded_file($_FILES['exercise_img']['tmp_name'], $target);
    }

    $stmt = $pdo->prepare("INSERT INTO fit_exercises (nazwa, opis, youtube_link, garmin_exercise_link, image_path) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$nazwa, $opis, $yt, $garmin, $image_name])) {
        header("Location: list.php?msg=added");
        exit;
    }
}

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow mb-4" style="max-width: 700px;">
        <div class="card-header py-3 bg-dark text-white">
            <h6 class="m-0 font-weight-bold">Dodaj Nowe Ćwiczenie do Bazy</h6>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Nazwa ćwiczenia</label>
                    <input type="text" name="nazwa" class="form-control" 
                           placeholder="np. Wyciskanie sztangi na ławce poziomej" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Miniatura / Obrazek</label>
                    <input type="file" name="exercise_img" class="form-control" accept="image/*">
                    <div class="form-text">Wybierz małe zdjęcie pokazujące technikę (jpg/png).</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Opis techniczny</label>
                    <textarea name="opis" class="form-control" rows="3" 
                              placeholder="Opisz krótko technikę, np. 'Łopatki ściągnięte, stopy mocno oparte o podłoże...'"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Link YouTube</label>
                        <input type="url" name="youtube_link" class="form-control" 
                               placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Link Garmin Connect</label>
                        <input type="url" name="garmin_exercise_link" class="form-control" 
                               placeholder="https://connect.garmin.com/...">
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-between">
                    <a href="list.php" class="btn btn-secondary">Anuluj</a>
                    <button type="submit" class="btn btn-primary px-5 shadow">Zapisz ćwiczenie</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include $root . '/includes/footer.php'; ?>