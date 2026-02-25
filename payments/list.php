<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

// Pobieranie miesiąca i roku (domyślnie obecne)
$m = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');
$r = isset($_GET['r']) ? (int)$_GET['r'] : (int)date('Y');

// Zapytanie - pobieramy wszystkich aktywnych i sprawdzamy wpłatę na dany miesiąc
$sql = "SELECT u.id as user_id, u.imie, u.nazwisko, p.id as p_id, p.kwota, p.data_wplaty 
        FROM fit_users u 
        LEFT JOIN fit_payments p ON u.id = p.user_id AND p.miesiac = ? AND p.rok = ?
        WHERE u.subscription_status = 'active' 
        ORDER BY u.nazwisko ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$m, $r]);
$list = $stmt->fetchAll();

// Proste podsumowanie
$total_collected = 0;
$paid_count = 0;
foreach($list as $row) {
    if($row['p_id']) {
        $total_collected += $row['kwota'];
        $paid_count++;
    }
}

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Płatności: <?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>-<?= $r ?></h2>
        <div>
            <a href="?m=<?= ($m==1?12:$m-1) ?>&r=<?= ($m==1?$r-1:$r) ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-chevron-left"></i></a>
            <a href="?m=<?= ($m==12?1:$m+1) ?>&r=<?= ($m==12?$r+1:$r) ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-chevron-right"></i></a>
            <a href="add.php" class="btn btn-success ms-2">Dodaj wpłatę</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body text-center">
                    <h6 class="mb-1">Zebrano</h6>
                    <h4><?= number_format($total_collected, 2, ',', ' ') ?> PLN</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body text-center">
                    <h6 class="mb-1">Opłacone</h6>
                    <h4><?= $paid_count ?> / <?= count($list) ?></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Klubowicz</th>
                        <th>Status</th>
                        <th>Kwota</th>
                        <th>Data wpłaty</th>
                        <th class="text-end">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($list as $l): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($l['imie'] . ' ' . $l['nazwisko']) ?></td>
                        <td>
                            <?php if($l['p_id']): ?>
                                <span class="badge bg-success-soft text-success border border-success px-3">Opłacone</span>
                            <?php else: ?>
                                <span class="badge bg-danger-soft text-danger border border-danger px-3">Brak wpłaty</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $l['p_id'] ? number_format($l['kwota'], 2, ',', ' ') . ' PLN' : '-' ?></td>
                        <td class="text-muted small"><?= $l['data_wplaty'] ?? '-' ?></td>
                        <td class="text-end">
                            <?php if($l['p_id']): ?>
                                <a href="delete.php?id=<?= $l['p_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Usunąć tę wpłatę?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php else: ?>
                                <a href="add.php?user_id=<?= $l['user_id'] ?>&m=<?= $m ?>&r=<?= $r ?>" class="btn btn-sm btn-primary">
                                    Opłać
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include $root . '/includes/footer.php'; ?>