<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

// Pobieramy grupy i liczymy ilu mają przypisanych klubowiczów
$sql = "SELECT g.*, 
        (SELECT COUNT(*) FROM fit_users u WHERE u.group_id = g.id) as liczba_osob,
        (SELECT COUNT(*) FROM fit_schedule s WHERE s.group_id = g.id) as liczba_treningow
        FROM fit_groups g 
        ORDER BY g.nazwa ASC";
$groups = $pdo->query($sql)->fetchAll();

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Grupy Treningowe</h1>
        <a href="add.php" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus me-2"></i> Nowa Grupa
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nazwa</th>
                            <th>Opis</th>
                            <th>Klubowicze</th>
                            <th>Terminy w tyg.</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groups as $g): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($g['nazwa']) ?></strong></td>
                            <td><small class="text-muted"><?= htmlspecialchars($g['opis'] ?? 'Brak opisu') ?></small></td>
                            <td><span class="badge bg-secondary"><?= $g['liczba_osob'] ?> os.</span></td>
                            <td><span class="badge bg-info text-dark"><?= $g['liczba_treningow'] ?></span></td>
                            <td>
                                <a href="edit.php?id=<?= $g['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                                <a href="delete.php?id=<?= $g['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Usunąć grupę?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include $root . '/includes/footer.php'; ?>