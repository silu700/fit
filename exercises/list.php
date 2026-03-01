<?php
require_once '../config/db.php';

// 1. Parametry
$search = isset($_GET['s']) ? trim($_GET['s']) : '';
$kat = isset($_GET['kat']) ? $_GET['kat'] : '';
$limit = 20; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 2. Pobieranie kategorii
$categories = $pdo->query("SELECT DISTINCT kategoria FROM fit_exercises WHERE kategoria IS NOT NULL AND kategoria != '' ORDER BY kategoria ASC")->fetchAll(PDO::FETCH_COLUMN);

// 3. SQL z filtrami
$whereClauses = [];
$params = [];

if ($kat) {
    $whereClauses[] = "kategoria = :kat";
    $params[':kat'] = $kat;
}
if ($search) {
    $whereClauses[] = "(nazwa LIKE :s OR garmin_nazwa LIKE :s)";
    $params[':s'] = "%$search%";
}

$whereSql = !empty($whereClauses) ? " WHERE " . implode(" AND ", $whereClauses) : "";

// Liczenie rekordów
$countSql = "SELECT COUNT(*) FROM fit_exercises" . $whereSql;
$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$totalExercises = $stmtCount->fetchColumn();
$totalPages = ceil($totalExercises / $limit);

// Pobieranie danych (LIMIT i OFFSET wstawione bezpośrednio jako INT dla stabilności)
$sql = "SELECT * FROM fit_exercises" . $whereSql . " ORDER BY nazwa ASC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
$exercises = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $exercises->bindValue($key, $val);
}
$exercises->execute();
$list = $exercises->fetchAll();

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

function renderPagination($page, $totalPages, $kat, $search) {
    if ($totalPages <= 1) return '';
    $q = [];
    if ($kat) $q['kat'] = $kat;
    if ($search) $q['s'] = $search;
    $html = '<nav><ul class="pagination pagination-sm m-0">';
    $html .= '<li class="page-item '.($page <= 1 ? 'disabled' : '').'"><a class="page-link" href="?'.http_build_query(array_merge($q, ['page' => $page - 1])).'">Poprzednia</a></li>';
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)) {
            $html .= '<li class="page-item '.($page == $i ? 'active' : '').'"><a class="page-link" href="?'.http_build_query(array_merge($q, ['page' => $i])).'">'.$i.'</a></li>';
        }
    }
    $html .= '<li class="page-item '.($page >= $totalPages ? 'disabled' : '').'"><a class="page-link" href="?'.http_build_query(array_merge($q, ['page' => $page + 1])).'">Następna</a></li>';
    $html .= '</ul></nav>';
    return $html;
}
?>

<div class="container-fluid">
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body">
            <form method="GET" action="list.php" id="searchForm" class="row align-items-center">
                <div class="col-md-4">
                    <h1 class="h4 m-0 text-gray-800">Biblioteka Ćwiczeń</h1>
                    <small class="text-muted">Znaleziono: <?= $totalExercises ?></small>
                </div>
                <div class="col-md-5">
                    <?php if($kat): ?><input type="hidden" name="kat" value="<?= htmlspecialchars($kat) ?>"><?php endif; ?>
                    <div class="input-group input-group-sm shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="s" id="liveSearch" class="form-control border-start-0" 
                               placeholder="Live search na stronie / Enter by szukać w bazie..." 
                               value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-3 text-end">
                    <a href="add.php" class="btn btn-primary shadow-sm btn-sm"><i class="fas fa-plus fa-sm text-white-50"></i> Dodaj</a>
                </div>
            </form>
        </div>
    </div>

    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="list.php" class="btn btn-sm <?= $kat == '' ? 'btn-dark' : 'btn-outline-dark' ?> rounded-pill px-3">Wszystkie</a>
        <?php foreach($categories as $c): ?>
            <a href="?kat=<?= urlencode($c) ?><?= $search ? '&s='.urlencode($search) : '' ?>" class="btn btn-sm <?= $kat == $c ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill px-3">
                <?= htmlspecialchars($c) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="mb-3 text-start"><?= renderPagination($page, $totalPages, $kat, $search) ?></div>

    <div class="card shadow mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Nazwa ćwiczenia</th>
                            <th>Mięśnie</th>
                            <th>Linki</th>
                            <th>Własna</th>
                            <th>Garmin</th>
                            <th class="text-end pe-4">Akcje</th>
                        </tr>
                    </thead>
                    <tbody id="exercisesTableBody">
                        <?php foreach ($list as $ex): ?>
                        <tr class="exercise-row">
                            <td class="ps-4 user-name-cell">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($ex['nazwa']) ?></div>
                                <?php if (!empty($ex['garmin_nazwa'])): ?>
                                    <small class="text-muted">(<?= htmlspecialchars($ex['garmin_nazwa']) ?>)</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $active = [];
                                    foreach ($muscleTranslations as $key => $label) {
                                        if (!empty($ex[$key]) && $ex[$key] > 0) $active[$label] = $ex[$key];
                                    }
                                    arsort($active);
                                    foreach (array_slice($active, 0, 3, true) as $label => $val): ?>
                                        <span class="d-block small mb-1"><i class="fas fa-caret-right text-primary me-1"></i><?= $label ?></span>
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
        <div class="card-footer bg-white py-3 text-start"><?= renderPagination($page, $totalPages, $kat, $search) ?></div>
    </div>
</div>

<script>
// Live Search na bieżącej stronie
document.getElementById('liveSearch').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') return; // Pozwól formularzowi wysłać zapytanie do bazy
    
    let filter = this.value.toLowerCase();
    document.querySelectorAll('.exercise-row').forEach(row => {
        let text = row.querySelector('.user-name-cell').innerText.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
    });
});
</script>

<?php include '../includes/footer.php'; ?>