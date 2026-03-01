<?php
require_once '../config/db.php';

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
    if (isset($ex[$key]) && $ex[$key] > 0) {
        $activeMuscles[$label] = (int)$ex[$key];
    }
}
arsort($activeMuscles);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($ex['nazwa']) ?></h1>
            <span class="badge bg-primary text-white"><?= htmlspecialchars($ex['kategoria'] ?: 'Bez kategorii') ?></span>
            <span class="ms-2 text-muted small">ID: #<?= $ex['id'] ?></span>
        </div>
        <div>
            <a href="list.php" class="btn btn-sm btn-secondary shadow-sm"><i class="fas fa-list fa-sm text-white-50"></i> Lista</a>
            <a href="edit.php?id=<?= $ex['id'] ?>" class="btn btn-sm btn-info shadow-sm text-white"><i class="fas fa-edit fa-sm text-white-50"></i> Edytuj</a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Obraz / Wideo</h6>
                </div>
                <div class="card-body p-0 text-center bg-dark rounded-bottom">
                    <?php 
                        $imgUrl = !empty($ex['image_path']) ? "/uploads/exercises/" . $ex['image_path'] : ($ex['garmin_image_link'] ?? "");
                    ?>
                    <?php if ($imgUrl): ?>
                        <img src="<?= $imgUrl ?>" class="img-fluid" style="max-height: 400px; width: 100%; object-fit: contain;" alt="Podgląd">
                    <?php else: ?>
                        <div class="py-5 text-white-50"><i class="fas fa-image fa-4x"></i><br>Brak obrazu</div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white">
                    <div class="row text-center">
                        <div class="col">
                            <?php if($ex['youtube_link']): ?>
                                <a href="<?= $ex['youtube_link'] ?>" target="_blank" class="btn btn-outline-danger btn-sm w-100">
                                    <i class="fab fa-youtube"></i> YouTube
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="col">
                            <?php if($ex['garmin_video_url']): ?>
                                <a href="<?= $ex['garmin_video_url'] ?>" target="_blank" class="btn btn-outline-info btn-sm w-100">
                                    <i class="fas fa-video"></i> Garmin Video
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Zaangażowanie mięśni</h6>
                </div>
                <div class="card-body">
                    <?php if(empty($activeMuscles)): ?>
                        <p class="text-muted small">Brak danych o mięśniach.</p>
                    <?php else: ?>
                        <?php foreach($activeMuscles as $name => $weight): ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-bold text-gray-800"><?= $name ?></span>
                                    <span class="badge bg-light text-primary border"><?= $weight ?>/9</span>
                                </div>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= ($weight/9)*100 ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Szczegóły ćwiczenia</h6>
                </div>
                <div class="card-body">
                    <h5 class="small fw-bold text-muted text-uppercase">Instrukcja wykonania:</h5>
                    <p class="text-gray-800 mb-4" style="white-space: pre-wrap; line-height: 1.6;"><?= nl2br(htmlspecialchars($ex['instrukcja'] ?: 'Brak instrukcji.')) ?></p>
                    
                    <hr>

                    <h5 class="small fw-bold text-danger text-uppercase"><i class="fas fa-exclamation-circle"></i> Wskazówki techniczne:</h5>
                    <div class="p-3 bg-light border-left-danger rounded">
                        <p class="mb-0 text-dark italic"><?= nl2br(htmlspecialchars($ex['wskazowki'] ?: 'Brak dodatkowych wskazówek.')) ?></p>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-gray-100">
                    <h6 class="m-0 font-weight-bold text-secondary small">Dane systemowe Garmin</h6>
                </div>
                <div class="card-body py-2">
                    <div class="row">
                        <div class="col-sm-4 border-right">
                            <label class="d-block small text-muted mb-0">Nazwa oryginalna</label>
                            <span class="text-dark font-weight-bold small"><?= $ex['garmin_nazwa'] ?: '-' ?></span>
                        </div>
                        <div class="col-sm-4 border-right">
                            <label class="d-block small text-muted mb-0">Kod ćwiczenia</label>
                            <span class="text-dark small"><?= $ex['garmin_name'] ?: '-' ?></span>
                        </div>
                        <div class="col-sm-4">
                            <label class="d-block small text-muted mb-0">Kategoria Garmin</label>
                            <span class="text-dark small text-capitalize"><?= $ex['garmin_category'] ?: '-' ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>