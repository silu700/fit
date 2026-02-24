<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nazwa = $_POST['nazwa'];
    $opis = $_POST['opis'];
    $yt = $_POST['youtube_link'];
    $garmin = $_POST['garmin_exercise_link'];
    
    $image_name = null;

    // Obsługa przesyłania obrazka
    if (isset($_FILES['exercise_img']) && $_FILES['exercise_img']['error'] == 0) {
        $ext = pathinfo($_FILES['exercise_img']['name'], PATHINFO_EXTENSION);
        $image_name = time() . '_' . uniqid() . '.' . $ext;
        $target = $root . '/uploads/exercises/' . $image_name;
        
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
    <div class="card shadow mb-4" style="max-width: 600px;">
        <div class="card-header py-3 bg-dark text-white"><h5>Nowe Ćwiczenie z Obrazkiem</h5></div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label>Nazwa ćwiczenia</label>
                    <input type="text" name="nazwa" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Obrazek pomocniczy (mały)</label>
                    <input type="file" name="exercise_img" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label>Opis</label>
                    <textarea name="opis" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label>YouTube Link</label>
                    <input type="url" name="youtube_link" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Garmin Link</label>
                    <input type="url" name="garmin_exercise_link" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary w-100">Zapisz ćwiczenie</button>
            </form>
        </div>
    </div>
</div>
<?php include $root . '/includes/footer.php'; ?>