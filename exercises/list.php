<?php
require_once '../config/db.php';

// 1. Parametry wejściowe
$search = isset($_GET['s']) ? trim($_GET['s']) : '';
$kat = isset($_GET['kat']) ? $_GET['kat'] : '';
$page = (isset($_GET['page']) && (int)$_GET['page'] > 0) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// 2. Pobieranie unikalnych kategorii do filtrów
$categories = $pdo->query("SELECT DISTINCT kategoria FROM fit_exercises WHERE kategoria IS NOT NULL AND kategoria != '' ORDER BY kategoria ASC")->fetchAll(PDO::FETCH_COLUMN);

// 3. Logika niezależnego szukania: Szukanie ma priorytet i czyści kategorię
$where = [];
if ($search !== '') {
    $s_quoted = $pdo->quote('%' . $search . '%');
    $where[] = "(nazwa LIKE $s_quoted OR garmin_nazwa LIKE $s_quoted)";
    $kat = ''; // Jeśli szukamy frazy, ignorujemy filtr kategorii w SQL
} elseif ($kat !== '') {
    $where[] = "kategoria = " . $pdo->quote($kat);
}

$whereSql = !empty($where) ? " WHERE " . implode(" AND ", $where) : "";

// 4. Liczenie rekordów i pobieranie danych (stabilny SQL bez błędu 500)
$totalExercises = (int)$pdo->query("SELECT COUNT(*) FROM fit_exercises" . $whereSql)->fetchColumn();
$totalPages = ceil($totalExercises / $limit);

$sql = "SELECT * FROM fit_exercises $whereSql ORDER BY nazwa ASC LIMIT $limit OFFSET $offset";
$list = $pdo->query($sql)->fetchAll();

// Tłumaczenia mięśni do wyświetlania Top 3
$muscleTranslations = [
    'muscle_abs' => 'Brzuch', 'muscle_obliques' => 'Skośne', 'muscle_lower_back' => 'Prostowniki',
    'muscle_glutes' => 'Pośladki', 'muscle_quads' => 'Czworogłowe', 'muscle_hamstrings' => 'Dwugłowe',
    'muscle_adductors' => 'Przywodziciele', 'muscle_abductors' => 'Odwodziciele', 'muscle_calves' => 'Łydki',
    'muscle_chest' => 'Klatka', 'muscle_shoulders' => 'Barki', 'muscle_biceps' => 'Biceps',
    'muscle_triceps' => 'Triceps', 'muscle_forearm' => 'Przedramię', 'muscle_lats' => 'Najszerszy',
    'muscle_traps' => 'Kaptury', 'muscle_hips' => 'Biodra'
];

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body">
            <form method="GET" action="list.php" class="row align-items-center">
                <div class="col-md-4">
                    <h1 class="h4 m-0 text-gray-800">Biblioteka Ćwiczeń</h1>
                    <small class="text-muted">Znaleziono: <?= $totalExercises ?> pozycji</small>
                </div>
                <div class="col-md-5">
                    <div class="input-group input-group-sm shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="s" id="liveSearch" class="form-control border-start-0" 
                               placeholder="Szukaj w całej bazie..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                        <button class="btn btn-primary" type="submit">Szukaj</button>
                    </div>
                </div>
                <div class="col-md-3 text-end">
                    <a href="add.php" class="btn btn-primary shadow-sm btn-sm"><i class="fas fa-plus fa-sm text-white-50"></i> Dodaj nowe</a>
                </div>
            </form>
        </div>
    </div>

    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="list.php" class="btn btn-sm <?= ($kat == '' && $search == '') ? 'btn-dark' : 'btn-outline-dark' ?> rounded-pill px-3 shadow-sm">Wszystkie</a>
        <?php foreach($categories as $c): ?>
            <a href="?kat=<?= urlencode($c) ?>" class="btn btn-sm <?= ($kat == $c) ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill px-3 shadow-sm">
                <?= htmlspecialchars($c) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="mb-3 text-start">
        <?php if ($totalPages > 1): ?>
            <nav><ul class="pagination pagination-sm m-0">
                <?php 
                $baseUrl = "?" . ($search ? "s=".urlencode($search) : "kat=".urlencode($kat));
                for ($i = 1; $i <= $totalPages; $i++): 
                    if($i == 1 || $i == $totalPages || ($i >= $page-2 && $i <= $page+2)): ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $baseUrl ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endif; endfor; ?>
            </ul></nav>
        <?php endif; ?>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 25%;">Nazwa ćwiczenia</th>
                            <th style="width: 18%;">Mięśnie (Top 3)</th>
                            <th>Linki</th>
                            <th>Miniatura Własna</th>
                            <th>Miniatura Garmin</th>
                            <th class="text-end pe-4">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($list as $ex): ?>
                        <tr class="exercise-row">
                            <td class="ps-4 user-name-cell">
                                <a href="view.php?id=<?= $ex['id'] ?>" class="text-decoration-none">
                                    <div class="fw-bold text-primary mb-0"><?= htmlspecialchars($ex['nazwa'] ?? '') ?></div>
                                </a>
                                <small class="text-muted" style="font-size: 0.75rem;">(<?= htmlspecialchars($ex['garmin_nazwa'] ?? '') ?>)</small>
                            </td>
                            <td>
                                <?php 
                                    $active = [];
                                    foreach ($muscleTranslations as $key => $label) {
                                        if (!empty($ex[$key]) && $ex[$key] > 0) $active[$label] = (int)$ex[$key];
                                    }
                                    arsort($active);
                                    foreach (array_slice($active, 0, 3, true) as $label => $val): ?>
                                        <span class="d-block small text-dark mb-1" style="font-size: 0.75rem;">
                                            <i class="fas fa-caret-right text-primary me-1"></i><?= $label ?> (<?= $val ?>)
                                        </span>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php if(!empty($ex['youtube_link'])): ?>
                                    <a href="<?= $ex['youtube_link'] ?>" target="_blank" class="btn btn-sm btn-outline-danger me-1"><i class="fab fa-youtube"></i></a>
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
                                    <a href="view.php?id=<?= $ex['id'] ?>" class="btn btn-sm btn-outline-primary" title="Podgląd"><i class="fas fa-eye"></i></a>
                                    <a href="edit.php?id=<?= $ex['id'] ?>" class="btn btn-sm btn-outline-info" title="Edytuj"><i class="fas fa-edit"></i></a>
                                    <a href="delete.php?id=<?= $ex['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Czy na pewno chcesz usunąć to ćwiczenie?')" title="Usuń"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($list)): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">Brak ćwiczeń spełniających kryteria.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3 text-start">
            <?php if ($totalPages > 1): ?>
                <nav><ul class="pagination pagination-sm m-0">
                    <?php for ($i = 1; $i <= $totalPages; $i++): if($i == 1 || $i == $totalPages || ($i >= $page-2 && $i <= $page+2)): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $baseUrl ?>&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endif; endfor; ?>
                </ul></nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// LIVE SEARCH: Błyskawiczne filtrowanie tego, co jest widoczne na stronie
document.getElementById('liveSearch').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') return; // Pozwól formularzowi przeładować stronę dla szukania w całej bazie
    
    let filter = this.value.toLowerCase();
    document.querySelectorAll('.exercise-row').forEach(row => {
        let text = row.querySelector('.user-name-cell').innerText.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
    });
});
</script>

<?php include '../includes/footer.php'; ?>