<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

$sql = "SELECT p.*, u.imie, u.nazwisko 
        FROM fit_training_plans p 
        JOIN fit_users u ON p.user_id = u.id 
        ORDER BY p.id DESC";
$plans = $pdo->query($sql)->fetchAll();

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid pt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Plany Treningowe</h1>
        <a href="add.php" class="btn btn-primary shadow-sm"><i class="fas fa-plus me-2"></i>Nowy Plan</a>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-light text-uppercase small fw-bold">
                    <tr>
                        <th class="ps-4">Użytkownik</th>
                        <th>Okres obowiązywania</th>
                        <th>Status</th>
                        <th class="text-center">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($plans as $p): ?>
                    <tr>
                        <td class="ps-4"><strong><?= htmlspecialchars($p['imie'].' '.$p['nazwisko']) ?></strong></td>
                        <td><?= $p['data_start'] ?> — <?= $p['data_koniec'] ?></td>
                        <td>
                            <span class="badge <?= $p['czy_aktywny'] ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $p['czy_aktywny'] ? 'Aktywny' : 'Archiwalny' ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <a href="delete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Usunąć cały plan?')"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include $root . '/includes/footer.php'; ?>