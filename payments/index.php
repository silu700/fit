<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

$m = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');
$r = isset($_GET['r']) ? (int)$_GET['r'] : (int)date('Y');

// 1. Pobieramy wszystkich AKTYWNYCH użytkowników i sprawdzamy ich wpłatę za dany miesiąc
$sql = "SELECT u.id, u.imie, u.nazwisko, p.id as payment_id, p.kwota, p.data_wplaty, p.metoda 
        FROM fit_users u 
        LEFT JOIN fit_payments p ON u.id = p.user_id AND p.miesiac = ? AND p.rok = ?
        WHERE u.subscription_status = 'active'
        ORDER BY u.nazwisko ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$m, $r]);
$list = $stmt->fetchAll();

$miesiace = [1=>'Styczeń', 2=>'Luty', 3=>'Marzec', 4=>'Kwiecień', 5=>'Maj', 6=>'Czerwiec', 7=>'Lipiec', 8=>'Sierpień', 9=>'Wrzesień', 10=>'Październik', 11=>'Listopad', 12=>'Grudzień'];

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Płatności: <?= $miesiace[$m] ?> <?= $r ?></h3>
        <div class="d-flex">
            <form class="d-flex me-2">
                <select name="m" class="form-select form-select-sm me-1">
                    <?php foreach($miesiace as $num => $name): ?>
                        <option value="<?= $num ?>" <?= $m == $num ? 'selected' : '' ?>><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-secondary">Pokaż</button>
            </form>
            <a href="add.php" class="btn btn-primary btn-sm">+ Dodaj wpłatę</a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Klubowicz</th>
                        <th>Status</th>
                        <th>Kwota</th>
                        <th>Data wpłaty</th>
                        <th>Metoda</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($list as $l): ?>
                    <tr class="<?= !$l['payment_id'] ? 'table-danger' : '' ?>">
                        <td><strong><?= htmlspecialchars($l['imie'] . ' ' . $l['nazwisko']) ?></strong></td>
                        <td>
                            <?php if($l['payment_id']): ?>
                                <span class="badge bg-success text-white">Opłacone</span>
                            <?php else: ?>
                                <span class="badge bg-danger text-white">BRAK WPŁATY</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $l['kwota'] ? $l['kwota'].' PLN' : '-' ?></td>
                        <td><?= $l['data_wplaty'] ?? '-' ?></td>
                        <td><?= $l['metoda'] ?? '-' ?></td>
                        <td>
                            <?php if($l['payment_id']): ?>
                                <a href="edit.php?id=<?= $l['payment_id'] ?>" class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i></a>
                                <a href="delete.php?id=<?= $l['payment_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Usunąć wpłatę?')"><i class="fas fa-trash"></i></a>
                            <?php else: ?>
                                <a href="add.php?user_id=<?= $l['id'] ?>" class="btn btn-sm btn-success">Opłać</a>
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