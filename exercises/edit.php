<?php
require_once '../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM fit_exercises WHERE id = ?");
$stmt->execute([$id]);
$ex = $stmt->fetch();

if (!$ex) {
    die("Nie znaleziono ćwiczenia.");
}

// Mapa mięśni do czytelnych nazw
$muscles = [
    'muscle_abs' => 'Brzuch', 'muscle_obliques' => 'Skośne brzucha', 'muscle_lower_back' => 'Dół pleców',
    'muscle_glutes' => 'Pośladki', 'muscle_quads' => 'Czworogłowe ud', 'muscle_hamstrings' => 'Dwugłowe ud',
    'muscle_adductors' => 'Przywodziciele', 'muscle_abductors' => 'Odwodziciele', 'muscle_calves' => 'Łydki',
    'muscle_chest' => 'Klatka piersiowa', 'muscle_shoulders' => 'Barki', 'muscle_biceps' => 'Biceps',
    'muscle_triceps' => 'Triceps', 'muscle_forearm' => 'Przedramię', 'muscle_lats' => 'Najszerszy grzbietu',
    'muscle_traps' => 'Kaptury', 'muscle_hips' => 'Biodra'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Podstawowe pola
    $updateData = [
        $_POST['kategoria'], $_POST['nazwa'], $_POST['opis'], $_POST['instrukcja'], 
        $_POST['wskazowki'], $_POST['youtube_link'], $_POST['difficulty'], $id
    ];

    // Obsługa mięśni - zerujemy wszystko i ustawiamy tylko zaznaczone
    $muscleSql = "";
    foreach ($muscles as $key => $label) {
        $val = isset($_POST[$key]) ? 1 : 0;
        $muscleSql .= ", $key = $val";
    }

    // Obsługa obrazka (jeśli przesłano nowy)
    $imagePath = $ex['image_path'];
    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/exercises/" . $newName)) {
            $imagePath = $newName;
        }
    }

    $sql = "UPDATE fit_exercises SET 
            kategoria = ?, nazwa = ?, opis = ?, instrukcja = ?, 
            wskazowki = ?, youtube_link = ?, difficulty = ?, 
            image_path = '$imagePath' $muscleSql 
            WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($updateData)) {
        header("Location: list.php?msg=updated");
        exit;
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edytuj: <?= htmlspecialchars($ex['nazwa']) ?></h1>
        <a href="list.php" class="btn btn-sm btn-secondary shadow-sm">Powrót do listy</a>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">Informacje o ćwiczeniu</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Nazwa własna</label>
                                <input type="text" name="nazwa" class="form-control" value="<?= htmlspecialchars($ex['nazwa']) ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Kategoria</label>
                                <input type="text" name="kategoria" class="form-control" value="<?= htmlspecialchars($ex['kategoria']) ?>" list="catList">
                                <datalist id="catList">
                                    <option value="Siłowe">
                                    <option value="Kardio">
                                    <option value="Stretching">
                                </datalist>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Krótki opis</label>
                            <textarea name="opis" class="form-control" rows="2"><?= htmlspecialchars($ex['opis']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Instrukcja wykonania</label>
                            <textarea name="instrukcja" class="form-control" rows="5"><?= htmlspecialchars($ex['instrukcja']) ?></textarea>
                        </div>

                        <div class="mb-3 text-danger">
                            <label class="form-label fw-bold"><i class="fas fa-exclamation-triangle"></i> Wskazówki techniczne</label>
                            <textarea name="wskazowki" class="form-control border-left-danger" rows="3"><?= htmlspecialchars($ex['wskazowki']) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">Multimedia i Linki</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">YouTube Link</label>
                                <input type="url" name="youtube_link" class="form-control" value="<?= htmlspecialchars($ex['youtube_link']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Poziom trudności</label>
                                <select name="difficulty" class="form-control">
                                    <option value="poczatkujący" <?= $ex['difficulty']=='poczatkujący'?'selected':'' ?>>Początkujący</option>
                                    <option value="średni" <?= $ex['difficulty']=='średni'?'selected':'' ?>>Średni</option>
                                    <option value="zaawansowany" <?= $ex['difficulty']=='zaawansowany'?'selected':'' ?>>Zaawansowany</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="p-3 bg-gray-100 rounded small">
                            <div class="text-uppercase fw-bold mb-2 text-muted">Dane z Garmina (tylko odczyt):</div>
                            <div class="row">
                                <div class="col-6"><strong>Garmin Name:</strong> <?= $ex['garmin_name'] ?></div>
                                <div class="col-6"><strong>Garmin Category:</strong> <?= $ex['garmin_category'] ?></div>
                                <div class="col-12 mt-1"><strong>Wideo Garmin:</strong> <a href="<?= $ex['garmin_video_url'] ?>" target="_blank"><?= $ex['garmin_video_url'] ?></a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">Zaangażowane mięśnie</h6>
                    </div>
                    <div class="card-body">
                        

[Image of human muscle groups diagram]

                        <div class="row">
                            <?php foreach ($muscles as $key => $label): ?>
                            <div class="col-6 mb-2">
                                <div class="form-check custom-control custom-checkbox">
                                    <input type="checkbox" name="<?= $key ?>" class="custom-control-input" id="<?= $key ?>" <?= $ex[$key] ? 'checked' : '' ?>>
                                    <label class="custom-control-label small" for="<?= $key ?>"><?= $label ?></label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-light">
                        <h6 class="m-0 font-weight-bold text-primary">Miniatura Własna</h6>
                    </div>
                    <div class="card-body text-center">
                        <?php if ($ex['image_path']): ?>
                            <img src="/uploads/exercises/<?= $ex['image_path'] ?>" class="img-fluid border rounded shadow-sm mb-3" style="max-height: 200px;">
                        <?php endif; ?>
                        <div class="custom-file text-left">
                            <input type="file" name="image" class="custom-file-input" id="customFile">
                            <label class="custom-file-label" for="customFile">Wybierz plik...</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block shadow">
                    <i class="fas fa-save"></i> Zapisz zmiany
                </button>
            </div>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>