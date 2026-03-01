<?php
require_once '../config/db.php';

// 1. Pobranie danych ćwiczenia
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM fit_exercises WHERE id = ?");
$stmt->execute([$id]);
$ex = $stmt->fetch();

if (!$ex) {
    die("Błąd: Nie znaleziono ćwiczenia.");
}

// Tłumaczenia mięśni
$muscleTranslations = [
    'muscle_abs' => 'Brzuch', 'muscle_obliques' => 'Skośne brzucha', 'muscle_lower_back' => 'Prostowniki',
    'muscle_glutes' => 'Pośladki', 'muscle_quads' => 'Czworogłowe', 'muscle_hamstrings' => 'Dwugłowe',
    'muscle_adductors' => 'Przywodziciele', 'muscle_abductors' => 'Odwodziciele', 'muscle_calves' => 'Łydki',
    'muscle_chest' => 'Klatka piersiowa', 'muscle_shoulders' => 'Barki', 'muscle_biceps' => 'Biceps',
    'muscle_triceps' => 'Triceps', 'muscle_forearm' => 'Przedramię', 'muscle_lats' => 'Najszerszy grzbietu',
    'muscle_traps' => 'Kaptury', 'muscle_hips' => 'Biodra'
];

// Sortowanie aktywnych mięśni po wadze (0-9)
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
                <div class="card-body p-2 text-center bg-light border-bottom">
                    <?php 
                        // Priorytet dla miniatury własnej, potem Garmin
                        $imgUrl = !empty($ex['image_path']) ? "/uploads/exercises/" . $ex['image_path'] : ($ex['garmin_image_link'] ?? "");
                    ?>
                    <?php if ($imgUrl): ?>
                        <img src="<?= $imgUrl ?>" class="img-fluid border rounded shadow-sm" style="max-height: 400px; width: 100%; object-fit: contain;">
                    <?php else: ?>
                        <div class="py-5 text-muted bg-white border rounded"><i class="fas fa-image fa-4x mb-2"></i><br>Brak zdjęcia</div>
                    <?php endif; ?>
                </div>
                
                <div class="card-body">
                    <div class="row g-2">
                        <?php if(!empty($ex['youtube_link'])): ?>
                        <div class="col-12">
                            <a href="<?= $ex['youtube_link'] ?>" target="_blank" class="btn btn-danger w-100 shadow-sm text-start">
                                <i class="fab fa-youtube me-2"></i> Obejrzyj na YouTube
                            </a>
                        </div>
                        <?php endif; ?>

                        <?php if(!empty($ex['garmin_exercise_link'])): ?>
                        <div class="col-12">
                            <a href="<?= $ex['garmin_exercise_link'] ?>" target="_blank" class="btn btn-info w-100 shadow-sm text-start text-white">
                                <i class="fas fa-external-link-alt me-2"></i> Instrukcja na stronie Garmin
                            </a>
                        </div>
                        <?php endif; ?>

                        <?php if(!empty($videoLinks)): ?>
                        <div class="col-12 mt-2">
                            <label class="small fw-bold text-muted mb-1 text-uppercase">Pliki wideo MP4:</label>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach($videoLinks as $idx => $link): ?>
                                    <a href="<?= trim($link) ?>" target="_blank" class="btn btn-sm btn-outline-dark shadow-sm">
                                        <i class="fas fa-play-circle me-1"></i> Wideo <?= count($videoLinks) > 1 ? ($idx + 1) : '' ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
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
                    <h6 class="text-uppercase text-muted fw-bold small mb-3">Jak wykonać ćwiczenie:</h6>
                    <div class="text-dark mb-4 p-3 bg-light rounded shadow-sm border" style="white-space: pre-wrap; line-height: 1.7; font-size: 1.05rem;">
                        <?= nl2br(htmlspecialchars($ex['instrukcja'] ?: 'Brak instrukcji wykonania.')) ?>
                    </div>
                    
                    <?php if(!empty($ex['wskazowki'])): ?>
                    <div class="alert alert-danger border-left-danger shadow-sm">
                        <h6 class="fw-bold"><i class="fas fa-exclamation-circle"></i> WSKAZÓWKI TECHNICZNE:</h6>
                        <p class="mb-0 italic small text-dark"><?= nl2br(htmlspecialchars($ex['wskazowki'])) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($ex['opis'])): ?>
                    <div class="mt-4">
                        <h6 class="text-uppercase text-muted fw-bold small">Dodatkowy opis/notatki:</h6>
                        <p class="text-muted small"><?= nl2br(htmlspecialchars($ex['opis'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-body py-3 bg-gray-100 rounded">
                    <div class="row text-center text-md-start">
                        <div class="col-md-5 border-right">
                            <label class="small text-muted d-block mb-0">Nazwa Garmin:</label>
                            <span class="text-dark font-weight-bold small"><?= htmlspecialchars($ex['garmin_nazwa'] ?: '-') ?></span>
                        </div>
                        <div class="col-md-4 border-right">
                            <label class="small text-muted d-block mb-0">Trudność:</label>
                            <div class="mt-1">
                                <?php 
                                    $diff = (int)$ex['difficulty'];
                                    for($i=1; $i<=3; $i++) echo '<i class="fas fa-star '.($i <= $diff ? 'text-warning' : 'text-gray-300').' small"></i> ';
                                ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted d-block mb-0">Kategoria:</label>
                            <span class="badge bg-white border text-dark small fw-normal"><?= htmlspecialchars($ex['garmin_category'] ?: '-') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>