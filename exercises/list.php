<?php
require_once '../config/db.php';

$exercises = $pdo->query("SELECT * FROM fit_exercises ORDER BY nazwa ASC")->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <h1 class="h3 mb-0 text-gray-800">Biblioteka Ćwiczeń</h1>
        
        <div class="flex-grow-1 mx-md-4 w-100">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" id="exerciseSearch" class="form-control border-start-0" placeholder="Szukaj ćwiczenia po nazwie...">
            </div>
        </div>

        <a href="add.php" class="btn btn-success shadow-sm px-4">
            <i class="fas fa-plus-circle me-2"></i> Dodaj ćwiczenie
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="exercisesTable">
                    <thead class="table-light">
                        <tr>
                            <th>Nazwa ćwiczenia</th>
                            <th>Linki Video / Dane</th>
                            <th>Miniatura</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exercises as $ex): ?>
                        <tr>
                            <td class="exercise-name"><strong><?= htmlspecialchars($ex['nazwa']) ?></strong></td>
                            <td>
                                <?php if($ex['youtube_link']): ?>
                                    <a href="<?= $ex['youtube_link'] ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                        <i class="fab fa-youtube"></i> YouTube
                                    </a>
                                <?php endif; ?>
                                <?php if($ex['garmin_exercise_link']): ?>
                                    <a href="<?= $ex['garmin_exercise_link'] ?>" target="_blank" class="btn btn-sm btn-outline-info text-dark">
                                        <i class="fas fa-running"></i> Garmin
                                    </a>
                                <?php endif; ?>
                            </td>
							<td>
								<?php if ($ex['image_path']): ?>
									<img src="/uploads/exercises/<?= $ex['image_path'] ?>" 
										 style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;" 
										 alt="foto">
								<?php else: ?>
									<div style="width: 50px; height: 50px; background: #eee; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
										<i class="fas fa-image text-muted"></i>
									</div>
								<?php endif; ?>
							</td>
                            <td>
                                <a href="edit.php?id=<?= $ex['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                                <a href="delete.php?id=<?= $ex['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Usunąć?')"><i class="fas fa-trash"></i></a>
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
// Skrypt wyszukiwarki
document.getElementById('exerciseSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#exercisesTable tbody tr');

    rows.forEach(row => {
        let name = row.querySelector('.exercise-name').textContent.toLowerCase();
        if (name.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>