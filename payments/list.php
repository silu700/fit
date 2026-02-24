<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

$m = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');
$r = isset($_GET['r']) ? (int)$_GET['r'] : (int)date('Y');

$sql = "SELECT u.id, u.imie, u.nazwisko, p.id as payment_id, p.kwota, p.data_wplaty, p.metoda 
        FROM fit_users u 
        LEFT JOIN fit_payments p ON u.id = p.user_id AND p.miesiac = ? AND p.rok = ?
        WHERE u.subscription_status = 'active'
        ORDER BY u.nazwisko ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$m, $r]);
$list = $stmt->fetchAll();

$suma_wplat = 0;
$nieoplacone_count = 0;
foreach ($list as $row) {
    if ($row['payment_id']) $suma_wplat += $row['kwota'];
    else $nieoplacone_count++;
}

$miesiace = [1=>'Styczeń', 2=>'Luty', 3=>'Marzec', 4=>'Kwiecień', 5=>'Maj', 6=>'Czerwiec', 7=>'Lipiec', 8=>'Sierpień', 9=>'Wrzesień', 10=>'Październik', 11=>'Listopad', 12=>'Grudzień'];
$lata = range(date('Y') - 1, date('Y') + 2); // Zakres lat: zeszły, obecny i dwa przyszłe

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <form class="d-flex align-items-center">
                        <select name="m" class="form-select form-select-sm me-1" style="width: 130px;">
                            <?php foreach($miesiace as $num => $name): ?>
                                <option value="<?= $num ?>" <?= $m == $num ? 'selected' : '' ?>><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="r" class="form-select form-select-sm me-1" style="width: 100px;">
                            <?php foreach($lata as $rok): ?>
                                <option value="<?= $rok ?>" <?= $r == $rok ? 'selected' : '' ?>><?= $rok ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">Pokaż</button>
                    </form>
                </div>
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="filterInput" class="form-control" placeholder="Szukaj użytkownika (imię lub nazwisko)...">
                    </div>
                </div>
                <div class="col-md-3 text-end">
                    <a href="add.php" class="btn btn-sm btn-success">+ Nowa wpłata</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="p-3 bg-white border rounded shadow-sm d-flex justify-content-between align-items-center">
                <span class="text-muted fw-bold">SUMA WPŁAT:</span>
                <span class="h4 mb-0 text-success fw-bold"><?= number_format($suma_wplat, 2, ',', ' ') ?> PLN</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-3 bg-white border rounded shadow-sm d-flex justify-content-between align-items-center">
                <span class="text-muted fw-bold">BRAK WPŁAT:</span>
                <span class="h4 mb-0 text-danger fw-bold"><?= $nieoplacone_count ?> osób</span>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" id="paymentsTable">
                <thead class="table-light">
                    <tr>
                        <th>Użytkownik</th>
                        <th>Status</th>
                        <th>Kwota</th>
                        <th>Data</th>
                        <th class="text-end">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($list as $l): ?>
                    <tr>
                        <td class="user-name">
                            <a href="../users/view.php?id=<?= $l['id'] ?>" class="text-decoration-none text-dark fw-bold">
                                <?= htmlspecialchars($l['imie'] . ' ' . $l['nazwisko']) ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge <?= $l['payment_id'] ? 'bg-success' : 'bg-danger' ?>">
                                <?= $l['payment_id'] ? 'Opłacone' : 'Czeka' ?>
                            </span>
                        </td>
                        <td class="fw-bold"><?= $l['payment_id'] ? $l['kwota'].' PLN' : '-' ?></td>
                        <td><?= $l['data_wplaty'] ?? '-' ?></td>
                        <td class="text-end">
                            <?php if($l['payment_id']): ?>
                                <a href="edit.php?id=<?= $l['payment_id'] ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-edit"></i></a>
                            <?php else: ?>
                                <a href="add.php?user_id=<?= $l['id'] ?>" class="btn btn-sm btn-success px-3">Opłać</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Skrypt do filtrowania tabeli na żywo
document.getElementById('filterInput').addEventListener('keyup', function() {
    let val = this.value.toLowerCase();
    let rows = document.querySelectorAll('#paymentsTable tbody tr');
    
    rows.forEach(row => {
        let name = row.querySelector('.user-name').textContent.toLowerCase();
        row.style.display = name.includes(val) ? '' : 'none';
    });
});
</script>

<?php include $root . '/includes/footer.php'; ?>