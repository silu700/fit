<?php
require_once '../config/db.php';

// 1. Parametry wyszukiwania, filtra i pagynacji
$search = isset($_GET['s']) ? trim($_GET['s']) : '';
$kat = isset($_GET['kat']) ? $_GET['kat'] : '';
$limit = 20; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 2. Pobieranie unikalnych kategorii
$categories = $pdo->query("SELECT DISTINCT kategoria FROM fit_exercises WHERE kategoria IS NOT NULL AND kategoria != '' ORDER BY kategoria ASC")->fetchAll(PDO::FETCH_COLUMN);

// 3. Budowanie warunków WHERE dla SQL
$params = [];
$whereClauses = [];

if ($kat) {
    $whereClauses[] = "kategoria = :kat";
    $params[':kat'] = $kat;
}

if ($search) {
    // Szukamy w nazwie oraz garmin_nazwa
    $whereClauses[] = "(nazwa LIKE :s OR garmin_nazwa LIKE :s)";
    $params[':s'] = "%$search%";
}

$whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

// 4. Liczenie rekordów i pobieranie danych
$countSql = "SELECT COUNT(*) FROM fit_exercises $whereSql";
$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$totalExercises = $stmtCount->fetchColumn();
$totalPages = ceil($totalExercises / $limit);

$sql = "SELECT * FROM fit_exercises $whereSql ORDER BY nazwa ASC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
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

function renderPagination($page, $totalPages, $kat, $search) {
    if ($totalPages <= 1) return '';
    $query = [];
    if ($kat) $query['kat'] = $kat;
    if ($search) $query['s'] = $search;
    
    $html = '<nav><ul class="pagination pagination-sm m-0">';
    
    $prevQuery = array_merge($query, ['page' => $page - 1]);
    $html .= '<li class="page-item '.($page <= 1 ? 'disabled' : '').'"><a class="page-link" href="?'.http_build_query($prevQuery).'">Poprzednia</a></li>';
    
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)) {
            $currQuery = array_merge($query, ['page' => $i]);
            $html .= '<li class="page-item '.($page == $i ? 'active' : '').'"><a class="page-link" href="?'.http_build_query($currQuery).'">'.$i.'</a></li>';
        }
    }
    
    $nextQuery = array_merge($query, ['page' => $page + 1]);
    $html .= '<li class="page-item '.($page >= $totalPages ? 'disabled' : '').'"><a class="page-link" href="?'.http_build_query($nextQuery).'">Następna</a></li>';
    
    $html .= '</ul></nav>';
    return $html;
}
?>

<div class="container-fluid">
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body">
            <form method="GET" action="list.php" class="row align-items-center">
                <div class="col-md-4">
                    <h1 class="h4 m-0 text-gray-800">Biblioteka Ćwiczeń</h1>
                    <small class="text-muted">Znaleziono: <?= $totalExercises ?></small>
                </div>
                <div class="col-md-5">
                    <?php if($kat): ?><input type="hidden" name="kat" value="<?= htmlspecialchars($kat) ?>"><?php endif; ?>
                    <div class="input-group input-group-sm shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="s" class="form-control border-start-0" placeholder="Szukaj w całej bazie..." value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-primary" type="submit">Szukaj</button>
                    </div>
                </div>
                <div class="col-md-3 text-end">
                    <a href="add.php" class="btn btn-primary shadow-sm btn-sm"><i class="fas fa-plus fa-sm text-white-50"></i> Dodaj</a>
                </div>
            </form>
        </div>
    </div>

    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="list.php<?= $search ? '?s='.urlencode($search) : '' ?>" class="btn btn-sm <?= $kat == '' ? 'btn-dark' : 'btn-outline-dark' ?> rounded-pill px-3">Wszystkie</a>
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
                            <th>Partie mięśniowe</th>
                            <th>Linki</th>
                            <th>Miniatura Własna</th>
                            <th>Miniatura Garmin</th>
                            <th class="text-end pe-4">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exercises as $ex): ?>
                        <tr>
                            <td class="ps-4">
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
                                    foreach (array_slice($activeMuscles, 0, 3, true) as $label => $val): ?>
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

<?php include '../includes/footer.php'; ?>