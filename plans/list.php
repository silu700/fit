<?php
require_once '../config/db.php';

// Parametry pagynacji
$limit = 50;
$page = isset($_GET['page']) ? (int)$GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Pobieranie łącznej liczby rekordów dla pagynacji
$totalExercises = $pdo->query("SELECT COUNT(*) FROM fit_exercises")->fetchColumn();
$totalPages = ceil($totalExercises / $limit);

// Pobieranie danych z limitem i offsetem
$stmt = $pdo->prepare("SELECT * FROM fit_exercises ORDER BY nazwa ASC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$exercises = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
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
                    <label class="small fw-bold text-muted text-uppercase">Szybkie szukanie ćwiczenia:</label>
                    <div class="input-group input-group-sm shadow-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="liveSearch" class="form-control border-start-0" placeholder="Wpisz nazwę ćwiczenia...">
                    </div>
                </div>

                <div class="col-md-3 text-end mt-3 mt-md-0">
                    <a href="add.php" class="btn btn-primary shadow-sm btn-sm">
                        <i class="fas fa-plus fa-sm text-white-50"></i> Dodaj ćwiczenie
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="exercisesTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Nazwa ćwiczenia</th>
                            <th>Linki Video / Dane</th>
                            <th>Miniatura</th>
                            <th class="text-end pe-4">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exercises as $ex): ?>
                        <tr class="exercise-row">
                            <td class="ps-4 user-name-cell">
                                <strong><?= htmlspecialchars($ex['nazwa']) ?></strong>
                            </td>
                            <td>
                                <?php if($ex['youtube_link']): ?>
                                    <a href="<?= $ex['youtube_link'] ?>" target="_blank" class="btn btn-sm btn-outline-danger" title="YouTube">
                                        <i class="fab fa-youtube"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if($ex['garmin_exercise_link']): ?>
                                    <a href="<?= $ex['garmin_exercise_link'] ?>" target="_blank" class="btn btn-sm btn-outline-info text-dark" title="Garmin">
                                        <i class="fas fa-running"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    // Fallback obrazka: image_path -> garmin_image_link -> placeholder
                                    $src = "/uploads/exercises/" . ($ex['image_path'] ?: 'default.png');
                                    if (!$ex['image_path'] && $ex['garmin_image_link']) {
                                        $src = $ex['garmin_image_link'];
                                    } elseif (!$ex['image_path'] && !$ex['garmin_image_link']) {
                                        $src = null;
                                    }
                                ?>
                                <?php if ($src): ?>
                                    <img src="<?= $src ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px;" 
                                         onerror="this.src='https://via.placeholder.com/40?text=?'">
                                <?php else: ?>
                                    <div style="width: 40px; height: 40px; background: #eee; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image text-muted small"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm">
                                    <a href="edit.php?id=<?= $ex['id'] ?>" class="btn btn-sm btn-outline-info" title="Edytuj"><i class="fas fa-edit"></i></a>
                                    <a href="delete.php?id=<?= $ex['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Usunąć?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white border-top-0 py-3">
            <nav aria-label="Nawigacja stron">
                <ul class="pagination pagination-sm m-0 justify-content-center">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>">Poprzednia</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">Następna</a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('liveSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('.exercise-row');

    rows.forEach(row => {
        let name = row.querySelector('.user-name-cell').textContent.toLowerCase();
        row.style.display = name.includes(filter) ? "" : "none";
    });
});
</script>

<?php include '../includes/footer.php'; ?>