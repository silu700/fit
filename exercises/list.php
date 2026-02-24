<?php
require_once '../config/db.php';

$exercises = $pdo->query("SELECT * FROM fit_exercises ORDER BY nazwa ASC")->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Biblioteka Ćwiczeń</h1>
        <a href="add.php" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle me-2"></i> Dodaj ćwiczenie
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nazwa ćwiczenia</th>
                            <th>Linki Video / Dane</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exercises as $ex): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($ex['nazwa']) ?></strong></td>
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

<?php include '../includes/footer.php'; ?>