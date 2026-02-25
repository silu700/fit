<?php
$root = realpath(__DIR__ . '/..');
require_once $root . '/config/db.php';

// Zakładam, że tabela to fit_training_plans lub fit_plans - sprawdź to w bazie!
try {
    $plans = $pdo->query("SELECT * FROM fit_training_plans ORDER BY id DESC")->fetchAll();
} catch (Exception $e) {
    // Jeśli tabela nie istnieje, ten błąd wyświetli się zamiast błędu 500
    die("Błąd bazy danych (sprawdź nazwę tabeli): " . $e->getMessage());
}

include $root . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Plany Treningowe</h2>
    <a href="add.php" class="btn btn-primary">+ Dodaj plan</a>
</div>

<div class="row">
    <?php foreach($plans as $plan): ?>
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5><?= htmlspecialchars($plan['nazwa_planu']) ?></h5>
                <p class="text-muted small">ID: <?= $plan['id'] ?></p>
                <a href="view.php?id=<?= $plan['id'] ?>" class="btn btn-sm btn-info text-white">Zobacz</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include $root . '/includes/footer.php'; ?>