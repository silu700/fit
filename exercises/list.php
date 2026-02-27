<?php
require_once '../config/db.php';

$exercises = $pdo->query("SELECT * FROM fit_exercises ORDER BY nazwa ASC")->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h1 class="h4 m-0 text-gray-800">Biblioteka Ćwiczeń</h1>
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
                                <?php if ($ex['image_path']): ?>
                                    <img src="/uploads/exercises/<?= $ex['image_path'] ?>" 
                                         style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px;" 
                                         alt="foto">
                                <?php else: ?>
                                    <div style="width: 40px; height: 40px; background: #eee; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image text-muted small"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm">
                                    <a href="edit.php?id=<?= $ex['id'] ?>" class="btn btn-sm btn-outline-info" title="Edytuj">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?= $ex['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Usunąć?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Skrypt wyszukiwania identyczny jak w payments.php
document.getElementById('liveSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('.exercise-row');

    rows.forEach(row => {
        let name = row.querySelector('.user-name-cell').textContent.toLowerCase();
        if (name.includes(filter)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>