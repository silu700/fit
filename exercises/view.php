<?php
require_once '../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM fit_exercises WHERE id = ?");
$stmt->execute([$id]);
$ex = $stmt->fetch();

if (!$ex) die("Błąd: Nie znaleziono ćwiczenia.");

$muscleTranslations = [
    'muscle_abs' => 'Brzuch', 'muscle_obliques' => 'Skośne brzucha', 'muscle_lower_back' => 'Prostowniki',
    'muscle_glutes' => 'Pośladki', 'muscle_quads' => 'Czworogłowe', 'muscle_hamstrings' => 'Dwugłowe',
    'muscle_adductors' => 'Przywodziciele', 'muscle_abductors' => 'Odwodziciele', 'muscle_calves' => 'Łydki',
    'muscle_chest' => 'Klatka piersiowa', 'muscle_shoulders' => 'Barki', 'muscle_biceps' => 'Biceps',
    'muscle_triceps' => 'Triceps', 'muscle_forearm' => 'Przedramię', 'muscle_lats' => 'Najszerszy grzbietu',
    'muscle_traps' => 'Kaptury', 'muscle_hips' => 'Biodra'
];

$activeMuscles = [];
foreach ($muscleTranslations as $key => $label) {
    if (isset($ex[$key]) && (int)$ex[$key] > 0) {
        $activeMuscles[$label] = (int)$ex[$key];
    }
}
arsort($activeMuscles);

// Parsowanie linków wideo MP4
$videoLinks = !empty($ex['garmin_video_url']) ? explode(', ', $ex['garmin_video_url']) : [];

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold"><?= htmlspecialchars($ex['nazwa']) ?></h1>
            <span class="badge bg-dark rounded-pill mt-2 px-3"><?= htmlspecialchars($ex['kategoria'] ?: 'Inne') ?></span>
        </div>
        <div class="btn-group shadow-sm">
            <a href="list.php" class="btn btn-sm btn-light border">Lista</a>
            <a href="edit.php?id=<?= $ex['id'] ?>" class="btn btn-sm btn-primary px-3">Edytuj dane</a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-5 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Podgląd ćwiczenia</h6>
                </div>
                <div class="card-body p-2 text-center bg-light">
                    <?php 
                        $imgUrl = !empty($ex['image_path']) ? "/uploads/exercises/" . $ex['image_path'] : ($ex['garmin_image_link'] ?? "");
                    ?>
                    <?php if ($imgUrl): ?>
                        <img src="<?= $imgUrl ?>" class="img-fluid border rounded shadow-sm" style="max-height: 400px; width: 100%; object-fit: contain;">
                    <?php else: ?>
                        <div class="py-5 text-muted bg-white border rounded"><i class="fas fa-image fa-4x mb-2"></i><br>Brak zdjęcia</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white border-bottom">
                    <h6 class="m-0 font-weight-bold text-primary">Zaangażowanie mięśni (0-9)</h6>
                </div>
                <div class="card-body">
                    
                    <?php if(empty($activeMuscles)): ?>
                        <p class="text-muted small text-center py-3">Brak danych o mięśniach.</p>
                    <?php else: ?>
                        <?php foreach($activeMuscles as $name => $weight): 
                            $pct = ($weight / 9) * 100;
                            $color = ($weight >= 7) ? 'bg-danger' : (($weight >= 4) ? 'bg-primary' : 'bg-info');
                        ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small fw-bold text-dark"><?= $name ?></span>
                                    <span class="badge bg-light text-dark border small"><?= $weight ?>/9</span>
                                </div>
                                <div class="progress" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar <?= $color ?> progress-bar-striped" role="progressbar" style="width: <?= $pct ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-7 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Instrukcja i Technika</h6>
                </div>
                <div class="card-body">
                    <div class="text-dark mb-4 p-3 bg-light rounded shadow-sm border" style="white-space: pre-wrap; line-height: 1.7; font-size: 1.05rem;">
                        <?= nl2br(htmlspecialchars($ex['instrukcja'] ?: 'Brak instrukcji.')) ?>
                    </div>
                    
                    <?php if(!empty($ex['wskazowki'])): ?>
                    <div class="alert alert-danger border-left-danger shadow-sm">
                        <h6 class="fw-bold"><i class="fas fa-exclamation-circle"></i> WSKAZÓWKI TECHNICZNE:</h6>
                        <p class="mb-0 italic small text-dark"><?= nl2br(htmlspecialchars($ex['wskazowki'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="row g-2 mt-2">
                        <?php if(!empty($ex['youtube_link'])): ?>
                        <div class="col-md-6">
                            <a href="<?= $ex['youtube_link'] ?>" target="_blank" class="btn btn-danger btn-sm w-100 shadow-sm">
                                <i class="fab fa-youtube me-2"></i> YouTube
                            </a>
                        </div>
                        <?php endif; ?>
                        <?php if(!empty($ex['garmin_exercise_link'])): ?>
                        <div class="col-md-6">
                            <a href="<?= $ex['garmin_exercise_link'] ?>" target="_blank" class="btn btn-info btn-sm w-100 text-white shadow-sm">
                                <i class="fas fa-running me-2"></i> Strona Garmin
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4 bg-gray-100">
                <div class="card-body py-2">
                    <div class="row small">
                        <div class="col-md-6 border-right"><strong>Oryginalna nazwa:</strong> <?= htmlspecialchars($ex['garmin_nazwa'] ?: '-') ?></div>
                        <div class="col-md-3 border-right"><strong>Trudność:</strong> <?= $ex['difficulty'] ?>/3</div>
                        <div class="col-md-3"><strong>Kat. Garmin:</strong> <?= $ex['garmin_category'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(!empty($videoLinks)): ?>
    <div class="row mt-2">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-play-circle me-2 text-info"></i>Wideo instruktażowe MP4</h6>
                </div>
                <div class="card-body bg-light">
                    <div class="row row-cols-1 row-cols-md-2 g-3">
                        <?php foreach($videoLinks as $idx => $link): ?>
                        <div class="col">
                            <div class="bg-dark rounded p-1 shadow-sm">
                                <video controls class="w-100 rounded" style="max-height: 280px;" preload="metadata">
                                    <source src="<?= trim($link) ?>" type="video/mp4">
                                </video>
                                <div class="text-center py-1"><small class="text-white-50 small">Odtwarzacz #<?= $idx + 1 ?></small></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>