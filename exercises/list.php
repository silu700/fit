<?php
require_once '../config/db.php';

// 1. Parametry pagynacji
$limit = 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 2. Pobieranie łącznej liczby rekordów
$totalExercises = $pdo->query("SELECT COUNT(*) FROM fit_exercises")->fetchColumn();
$totalPages = ceil($totalExercises / $limit);

// 3. Pobieranie danych
$stmt = $pdo->prepare("SELECT * FROM fit_exercises ORDER BY nazwa ASC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$exercises = $stmt->fetchAll();

// Tłumaczenia mięśni
$muscleTranslations = [
    'muscle_abductors' => 'Odwodziciele', 'muscle_abs' => 'Brzuch', 'muscle_adductors' => 'Przywodziciele',
    'muscle_biceps' => 'Biceps', 'muscle_calves' => 'Łydki', 'muscle_chest' => 'Klatka piersiowa',
    'muscle_forearm' => 'Przedramię', 'muscle_glutes' => 'Pośladki', 'muscle_hamstrings' => 'Dwugłowe ud',
    'muscle_hips' => 'Biodra', 'muscle_lats' => 'Najszerszy grzbietu', 'muscle_lower_back' => 'Prostowniki grzbietu',
    'muscle_obliques' => 'Skośne brzucha', 'muscle_quads' => 'Czworogłowe ud', 'muscle_shoulders' => 'Barki',
    'muscle_traps' => 'Kaptury', 'muscle_triceps' => 'Triceps'
];

include '../includes/header.php';
include '../includes/sidebar.php';

function renderPagination($page, $totalPages) {
    if ($totalPages <= 1) return '';
    $html = '<nav><ul class="pagination pagination-sm m-0 justify-content-center">';
    $html .= '<li class="page-item '.($page <= 1 ? 'disabled' : '').'"><a class="page-link" href="?page='.($page-1).'">Poprzednia</a></li>';
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)) {
            $html .= '<li class="page-item '.($page == $i ? 'active' : '').'"><a class="page-link" href="?page='.$i.'">'.$i.'</a></li>';
        }
    }
    $html .= '<li class="page-item '.($page >= $totalPages ? 'disabled' : '').'"><a class="page-link" href="?page='.($page+1).'">Następna</a></li>';
    $html .= '</ul></nav>';
    return $html;
}
?>

<div class="container-fluid">
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h1 class="h4 m-0 text-gray-800">Biblioteka Ćwiczeń</h1>
                    <small class="text-muted">Strona <?= $page ?> z <?= $totalPages ?> (Łącznie: <?= $totalExercises ?>)</small>
                </div>
                <div class="col-md-5">
                    <label class="small fw-bold text-muted text-uppercase">Szybkie szukanie:</label>
                    <div class="input-group input-group-sm shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="liveSearch" class="form-control border-start-0" placeholder="Wpisz nazwę...">
                    </div>
                </div>
                <div class="col-md-3 text-end">
                    <a href="add.php" class="btn btn-primary shadow-sm btn-sm"><i class="fas fa-plus fa-sm text-white-50"></i> Dodaj ćwiczenie</a>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3"><?= renderPagination($page, $totalPages) ?></div>

    <div class="card shadow mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="exercisesTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Nazwa ćwiczenia</th>
                            <th>Partie mięśniowe</th>
                            <th>Linki</th>
                            <th>Miniatura</th>
                            <th class="text-end pe-4">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exercises as $ex): ?>
                        <tr class="exercise-row">
                            <td class="ps-4 user-name-cell">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($ex['nazwa']) ?></div>
                                <?php if (!empty($ex['garmin_nazwa'])): ?>
                                    <small class="text-muted">(<?= htmlspecialchars($ex['garmin_nazwa']) ?>)</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $activeMuscles = [];
                                    foreach ($muscleTranslations as $key => $label) {
                                        if (isset($ex[$key]) && $ex[$key] > 0) {
                                            $activeMuscles[$label] = $ex[$key];
                                        }
                                    }
                                    arsort($activeMuscles); // Sortowanie od największej wartości
                                    foreach ($activeMuscles as $label => $val): ?>
                                        <span class="badge bg-light text-primary border me-1 small"><?= $label ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php if(!empty($ex['youtube_link'])): ?>
                                    <a href="<?= $ex['youtube_link'] ?>" target="_blank" class="btn btn-sm btn-outline-danger"><i class="fab fa-youtube"></i></a>
                                <?php endif; ?>
                                <?php if(!empty($ex['garmin_exercise_link'])): ?>
                                    <a href="<?= $ex['garmin_exercise_link'] ?>" target="_blank" class="btn btn-sm btn-outline-info text-dark"><i class="fas fa-running"></i></a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $imgUrl = !empty($ex['image_path']) ? "/uploads/exercises/" . $ex['image_path'] : ($ex['garmin_image_link'] ?? "");
                                ?>
                                <?php if ($imgUrl): ?>
                                    <img src="<?= $imgUrl ?>" style="width: 45px; height: 45px; object-fit: cover; border-radius: 5px;">
                                <?php else: ?>
                                    <div class="bg-light border rounded text-muted d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;"><i class="fas fa-image small"></i></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm">
                                    <a href="edit.php?id=<?= $ex['id'] ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-edit"></i></a>
                                    <a href="delete.php?id=<?= $ex['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Usunąć?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3"><?= renderPagination($page, $totalPages) ?></div>
    </div>
</div>

<script>
document.getElementById('liveSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    document.querySelectorAll('.exercise-row').forEach(row => {
        let text = row.querySelector('.user-name-cell').innerText.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
    });
});
</script>

<?php include '../includes/footer.php'; ?>