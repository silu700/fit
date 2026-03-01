<?php
require_once '../config/db.php';

// 1. Pobranie danych ćwiczenia
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM fit_exercises WHERE id = ?");
$stmt->execute([$id]);
$ex = $stmt->fetch();

if (!$ex) {
    die("Błąd: Nie znaleziono ćwiczenia o ID: " . htmlspecialchars($id));
}

// 2. Definicja mięśni (mapowanie kolumn na polskie nazwy)
$muscles = [
    'muscle_abs' => 'Brzuch', 'muscle_obliques' => 'Skośne brzucha', 'muscle_lower_back' => 'Prostowniki',
    'muscle_glutes' => 'Pośladki', 'muscle_quads' => 'Czworogłowe', 'muscle_hamstrings' => 'Dwugłowe',
    'muscle_adductors' => 'Przywodziciele', 'muscle_abductors' => 'Odwodziciele', 'muscle_calves' => 'Łydki',
    'muscle_chest' => 'Klatka piersiowa', 'muscle_shoulders' => 'Barki', 'muscle_biceps' => 'Biceps',
    'muscle_triceps' => 'Triceps', 'muscle_forearm' => 'Przedramię', 'muscle_lats' => 'Najszerszy grzbietu',
    'muscle_traps' => 'Kaptury', 'muscle_hips' => 'Biodra'
];

// 3. Obsługa zapisu (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Przygotowanie podstawowych parametrów
    $params = [
        ':kat' => $_POST['kategoria'],
        ':nazwa' => $_POST['nazwa'],
        ':opis' => $_POST['opis'],
        ':inst' => $_POST['instrukcja'],
        ':wsk' => $_POST['wskazowki'],
        ':yt' => $_POST['youtube_link'],
        ':diff' => $_POST['difficulty'],
        ':id' => $id
    ];

    // Budowanie fragmentu SQL dla wag mięśni (skala 0-9)
    $muscleSqlPart = "";
    foreach ($muscles as $column => $label) {
        $val = isset($_POST[$column]) ? (int)$_POST[$column] : 0;
        if ($val > 9) $val = 9;
        if ($val < 0) $val = 0;
        $muscleSqlPart .= ", $column = $val";
    }

    // Obsługa uploadu zdjęcia
    $imagePath = $ex['image_path'];
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = "../uploads/exercises/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newName = "ex_" . time() . "_" . rand(100, 999) . "." . $ext;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newName)) {
            $imagePath = $newName;
        }
    }

    $sql = "UPDATE fit_exercises SET 
            kategoria = :kat, 
            nazwa = :nazwa, 
            opis = :opis, 
            instrukcja = :inst, 
            wskazowki = :wsk, 
            youtube_link = :yt, 
            difficulty = :diff, 
            image_path = '$imagePath' 
            $muscleSqlPart 
            WHERE id = :id";
    
    $stmtUpdate = $pdo->prepare($sql);
    if ($stmtUpdate->execute($params)) {
        header("Location: list.php?msg=updated");
        exit;
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h4 mb-0 text-gray-800">Edycja: <strong><?= htmlspecialchars($ex['nazwa']) ?></strong></h1>
        <a href="list.php" class="btn btn-sm btn-secondary shadow-sm"><i class="fas fa-arrow-left"></i> Powrót</a>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-light"><h6 class="m-0 font-weight-bold text-primary">Informacje ogólne</h6></div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label class="small fw-bold">Nazwa wyświetlana</label>
                                <input type="text" name="nazwa" class="form-control" value="<?= htmlspecialchars($ex['nazwa']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="small fw-bold">Kategoria własna</label>
                                <input type="text" name="kategoria" class="form-control" value="<?= htmlspecialchars($ex['kategoria']) ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">Opis / Notatka</label>
                            <textarea name="opis" class="form-control" rows="2"><?= htmlspecialchars($ex['opis']) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">Instrukcja (krok po kroku)</label>
                            <textarea name="instrukcja" class="form-control" rows="5"><?= htmlspecialchars($ex['instrukcja']) ?></textarea>
                        </div>
                        <div class="mb-0">
                            <label class="small fw-bold text-danger">Wskazówki techniczne (Krytyczne)</label>
                            <textarea name="wskazowki" class="form-control border-left-danger shadow-sm" rows="3"><?= htmlspecialchars($ex['wskazowki']) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-light"><h6 class="m-0 font-weight-bold text-primary">Zaangażowanie mięśni (Waga 0-9)</h6></div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($muscles as $column => $label): ?>
                            <div class="col-md-4 mb-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light" style="width: 130px; font-size: 11px;"><?= $label ?></span>
                                    <input type="number" name="<?= $column ?>" class="form-control" 
                                           value="<?= (int)$ex[$column] ?>" min="0" max="9">
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow mb-4 text-center">
                    <div class="card-header py-3 bg-light text-left"><h6 class="m-0 font-weight-bold text-primary">Miniatura własna</h6></div>
                    <div class="card-body">
                        <?php if ($ex['image_path']): ?>
                            <img src="/uploads/exercises/<?= $ex['image_path'] ?>" class="img-fluid border shadow-sm rounded mb-3" style="max-height: 180px; width: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light border rounded mb-3 py-5 text-muted small">Brak wgranego zdjęcia</div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control form-control-sm">
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="small fw-bold">YouTube URL</label>
                            <input type="url" name="youtube_link" class="form-control form-control-sm" value="<?= htmlspecialchars($ex['youtube_link'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">Trudność (1-3)</label>
                            <select name="difficulty" class="form-control form-control-sm">
                                <option value="1" <?= $ex['difficulty']=='1'?'selected':'' ?>>1 - Początkujący</option>
                                <option value="2" <?= $ex['difficulty']=='2'?'selected':'' ?>>2 - Średni</option>
                                <option value="3" <?= $ex['difficulty']=='3'?'selected':'' ?>>3 - Zaawansowany</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4 bg-gray-100 border-0">
                    <div class="card-body small">
                        <span class="text-uppercase text-muted fw-bold d-block mb-2 border-bottom">Dane Garmin Connect</span>
                        <div class="mb-1"><strong>ID Garmin:</strong> <?= htmlspecialchars($ex['garmin_name'] ?? '-') ?></div>
                        <div class="mb-1"><strong>Kat. Garmin:</strong> <?= htmlspecialchars($ex['garmin_category'] ?? '-') ?></div>
                        <div class="mb-1"><strong>Nazwa oryg:</strong> <span class="text-primary"><?= htmlspecialchars($ex['garmin_nazwa'] ?? '-') ?></span></div>
                        <?php if($ex['garmin_video_url']): ?>
                            <a href="<?= $ex['garmin_video_url'] ?>" target="_blank" class="btn btn-sm btn-link p-0 text-info mt-1"><i class="fas fa-video"></i> Zobacz wideo Garmin</a>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block shadow py-3">
                    <i class="fas fa-save shadow-sm"></i> <strong>ZAPISZ ZMIANY</strong>
                </button>
            </div>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>