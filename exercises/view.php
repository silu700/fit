<?php
require_once '../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM fit_exercises WHERE id = ?");
$stmt->execute([$id]);
$ex = $stmt->fetch();

if (!$ex) {
    die("Błąd: Nie znaleziono ćwiczenia.");
}

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
    if (isset($ex[$key]) && $ex[$key] > 0) {
        $activeMuscles[$label] = (int)$ex[$key];
    }
}
arsort($activeMuscles);

// Parsowanie wielu linków wideo
$videoLinks = !empty($ex['garmin_video_url']) ? explode(', ', $ex['garmin_video_url']) : [];

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold"><?= htmlspecialchars($ex['nazwa']) ?></h1>
            <span class="badge bg-dark rounded-pill mt-2"><?= htmlspecialchars($ex['kategoria'] ?: 'Inne') ?></span>
        </div>
        <div class="btn-group">
            <a href="list.php" class="btn btn-sm btn-outline-secondary shadow-sm">Lista</a>
            <a href="edit.php?id=<?= $ex['id'] ?>" class="btn btn-sm btn-info text-white shadow-sm px-3">Edytuj</a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-5 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Wideo i Obraz</h6>
                </div>
                <div class="card-body p-0 bg-black text-center overflow-hidden" style="min-height: 300px; display: flex; align-items: center; justify-content: center;">
                    <?php if (!empty($videoLinks)): ?>
                        <video controls class="w-100 shadow-lg" style="max-height: 450px;" poster="<?= !empty($ex['image_path']) ? '/uploads/exercises/'.$ex['image_path'] : $ex['garmin_image_link'] ?>">
                            <source src="<?= trim($videoLinks[0]) ?>" type="video/video">
                            Twoja przeglądarka nie obsługuje odtwarzacza wideo.
                        </video>
                    <?php else: ?>
                        <?php $imgUrl = !empty($ex['image_path']) ? "/uploads/exercises/" . $ex['image_path'] : ($ex['garmin_image_link'] ?? ""); ?>
                        <?php if ($imgUrl): ?>
                            <img src="<?= $imgUrl ?>" class="img-fluid" style="max-height: 450px; width: 100%; object-fit: contain;">
                        <?php else: ?>
                            <div class="py-5 text-muted"><i class="fas fa-video-slash fa-4x mb-3"></i><br>Brak multimediów</div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <?php if (count($videoLinks) > 0 || !empty($ex['youtube_link'])): ?>
                <div class="card-footer bg-white">
                    <label class="small fw-bold text-muted mb-2 d-block">Dostępne materiały:</label>
                    <div class="d-flex flex-wrap gap-2">
                        <?php if($ex['youtube_link']): ?>
                            <a href="<?= $ex['youtube_link'] ?>" target="_blank" class="btn btn-danger btn-sm rounded-pill"><i class="fab fa-youtube"></i> Otwórz w YT</a>
                        <?php endif; ?>
                        
                        <?php foreach($videoLinks as $idx => $link): ?>
                            <a href="<?= trim($link) ?>" target="_blank" class="btn btn-outline-info btn-sm rounded-pill text-dark border">
                                <i class="fas fa-play-circle"></i> Wideo MP4 <?= count($videoLinks) > 1 ? ($idx + 1) : '' ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="card shadow mb-4 border-left-primary">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Zaangażowanie mięśni</h6>
                </div>
                <div class="card-body">
                    
                    <?php if(empty($activeMuscles)): ?>
                        <p class="text-muted small italic">Nie zdefiniowano obciążeń mięśniowych.</p>
                    <?php else: ?>
                        <?php foreach($activeMuscles as $name => $weight): 
                            $pct = ($weight / 9) * 100;
                            $color = ($weight >= 7) ? 'bg-danger' : (($weight >= 4) ? 'bg-primary' : 'bg-info');
                        ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-bold text-gray-800"><?= $name ?></span>
                                    <span class="badge bg-light text-dark border"><?= $weight ?>/9</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar <?= $color ?> shadow-sm" role="progressbar" style="width: <?= $pct ?>%"></div>
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
                    <h6 class="text-uppercase text-muted fw-bold small">Prawidłowe wykonanie:</h6>
                    <p class="text-dark mb-4" style="white-space: pre-wrap; line-height: 1.6; font-size: 1.05rem;"><?= nl2br(htmlspecialchars($ex['instrukcja'] ?: 'Brak instrukcji.')) ?></p>
                    
                    <?php if(!empty($ex['wskazowki'])): ?>
                    <div class="alert alert-warning border-0 shadow-sm rounded-lg p-3">
                        <h6 class="fw-bold text-danger"><i class="fas fa-exclamation-triangle"></i> WSKAZÓWKI I UWAGI:</h6>
                        <p class="mb-0 text-dark italic small"><?= nl2br(htmlspecialchars($ex['wskazowki'])) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($ex['opis'])): ?>
                    <div class="mt-4">
                        <h6 class="text-uppercase text-muted fw-bold small">Dodatkowy opis:</h6>
                        <p class="text-muted small"><?= nl2br(htmlspecialchars($ex['opis'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-body py-3 bg-gray-100 border-radius-sm">
                    <div class="row align-items-center">
                        <div class="col-md-6 border-right">
                            <label class="small text-muted mb-0 d-block">Nazwa oryginalna Garmin:</label>
                            <span class="text-dark fw-bold"><?= htmlspecialchars($ex['garmin_nazwa'] ?: '-') ?></span>
                        </div>
                        <div class="col-md-3 border-right">
                            <label class="small text-muted mb-0 d-block">Trudność:</label>
                            <?php 
                                $diff = (int)$ex['difficulty'];
                                for($i=1; $i<=3; $i++) {
                                    echo '<i class="fas fa-circle '.($i <= $diff ? 'text-warning' : 'text-gray-300').' small"></i> ';
                                }
                            ?>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted mb-0 d-block">Kategoria Garmin:</label>
                            <span class="badge bg-light border text-muted"><?= htmlspecialchars($ex['garmin_category'] ?: '-') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>