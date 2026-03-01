<?php
require_once '../config/db.php';

// 1. Parametry filtra i pagynacji
$kat = isset($_GET['kat']) ? $_GET['kat'] : '';
$limit = 20; // Zmieniono na 20
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 2. Pobieranie unikalnych kategorii do nawigacji
$categories = $pdo->query("SELECT DISTINCT kategoria FROM fit_exercises WHERE kategoria IS NOT NULL AND kategoria != '' ORDER BY kategoria ASC")->fetchAll(PDO::FETCH_COLUMN);

// 3. Budowanie zapytania z uwzględnieniem filtra
$where = $kat ? "WHERE kategoria = :kat" : "";
$countSql = "SELECT COUNT(*) FROM fit_exercises $where";
$stmtCount = $pdo->prepare($countSql);
if($kat) $stmtCount->bindValue(':kat', $kat);
$stmtCount->execute();
$totalExercises = $stmtCount->fetchColumn();
$totalPages = ceil($totalExercises / $limit);

$sql = "SELECT * FROM fit_exercises $where ORDER BY nazwa ASC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
if($kat) $stmt->bindValue(':kat', $kat);
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

function renderPagination($page, $totalPages, $kat) {
    if ($totalPages <= 1) return '';
    $urlParam = $kat ? "&kat=".urlencode($kat) : "";
    $html = '<nav><ul class="pagination pagination-sm m-0">'; // Usunięto justify-content-center dla wyrównania do lewej
    $html .= '<li class="page-item '.($page <= 1 ? 'disabled' : '').'"><a class="page-link" href="?page='.($page-1).$urlParam.'">Poprzednia</a></li>';
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)) {
            $html .= '<li class="page-item '.($page == $i ? 'active' : '').'"><a class="page-link" href="?page='.$i.$urlParam.'">'.$i.'</a></li>';
        }
    }
    $html .= '<li class="page-item '.($page >= $totalPages ? 'disabled' : '').'"><a class="page-link" href="?page='.($page+1).$urlParam.'">Następna</a></li>';
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
                    <small class="text-muted">Pozycji: <?= $totalExercises ?> (Filtr: <?= $kat ?: 'Wszystkie' ?>)</small>
                </div>
                <div class="col-md-5">
                    <div class="input-group input-group-sm shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="liveSearch" class="form-control border-start-0" placeholder="Szukaj na tej stronie...">
                    </div>
                </div>
                <div class="col-md-3 text-end">
                    <a href="add.php" class="btn btn-primary shadow-sm btn-sm"><i class="fas fa-plus fa-sm text-white-50"></i> Dodaj</a>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="list.php" class="btn btn-sm <?= $kat == '' ? 'btn-dark' : 'btn-outline-dark' ?> rounded-pill px-3">Wszystkie</a>
        <?php foreach($categories as $c): ?>
            <a href="?kat=<?= urlencode($c) ?>" class="btn btn-sm <?= $kat == $c ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill px-3">
                <?= htmlspecialchars($c) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="mb-3 text-start"><?= renderPagination($page, $totalPages, $kat) ?></div>

    <div class="card shadow mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="exercisesTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Nazwa ćwiczenia</th>
                            <th>Partie mięśniowe</th>
                            <th>Linki</th>
                            <th>Miniatura Własna</th>
                            <th>Miniatura Garmin</th>
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
                                        if (!empty($ex[$key]) && (int)$ex[$key] > 0) {
                                            $activeMuscles[$label] = (int)$ex[$key];
                                        }
                                    }
                                    arsort($activeMuscles);
                                    $topMuscles = array_slice($activeMuscles, 0, 3, true);
                                    foreach ($topMuscles as $label => $val): ?>
                                        <span class="d-block small mb-1"><i class="fas fa-caret-right text-primary me-1"></i><?= $label ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php if(!empty($ex['youtube_link'])): ?>
                                    <a href="<?= $ex['youtube_link'] ?>" target="_blank" class="btn btn-sm btn-outline-danger" title="YouTube"><i class="fab fa-youtube"></i></a>
                                <?php endif; ?>
                                <?php if(!empty($ex['garmin_exercise_link'])): ?>
                                    <a href="<?= $ex['garmin_exercise_link'] ?>" target="_blank" class="btn btn-sm btn-outline-info text-dark" title="Garmin"><i class="fas fa-running"></i></a>
                                <?php endif; ?>
                            </td>
							<td>
								<?php if (!empty($ex['image_path'])): ?>
									<img src="/uploads/exercises/<?= $ex['image_path'] ?>" class="border shadow-sm" style="width: 150px; height: 103px; object-fit: cover; border-radius: 5px;">
								<?php endif; ?>
							</td>
							<td>
								<?php if (!empty($ex['garmin_image_link'])): ?>
									<img src="<?= $ex['garmin_image_link'] ?>" class="border shadow-sm" style="width: 150px; height: 103px; object-fit: cover; border-radius: 5px;">
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
        <div class="card-footer bg-white py-3 text-start"><?= renderPagination($page, $totalPages, $kat) ?></div>
    </div>
</div>

<script>
document.getElementById('liveSearch').addEventListener('keyup', function() {
    // Pobieramy wpisaną frazę i zamieniamy na małe litery
    let filter = this.value.toLowerCase();
    
    // Przeszukujemy każdy wiersz tabeli
    document.querySelectorAll('.exercise-row').forEach(row => {
        // Celujemy wyłącznie w komórkę z nazwami (nazwa + garmin_nazwa)
        let nameCell = row.querySelector('.user-name-cell');
        
        if (nameCell) {
            let text = nameCell.innerText.toLowerCase();
            
            // Jeśli tekst komórki zawiera filtr, pokaż wiersz, w przeciwnym razie ukryj
            if (text.includes(filter)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>