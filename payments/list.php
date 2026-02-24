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
$lata = range(date('Y') - 1, date('Y') + 2);

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <form class="d-flex">
                        <select name="m" class="form-select form-select-sm me-1">
                            <?php foreach($miesiace as $num => $name): ?>
                                <option value="<?= $num ?>" <?= $m == $num ? 'selected' : '' ?>><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="r" class="form-select form-select-sm me-1">
                            <?php foreach($lata as $rok): ?>
                                <option value="<?= $rok ?>" <?= $rok == $r ? 'selected' : '' ?>><?= $rok ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary px-3">Pokaż</button>
                    </form>
                </div>
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                        <input type="text" id="liveSearch" class="form-control" placeholder="Filtruj listę użytkowników...">
                    </div>
                </div>
                <div class="col-md-3 text-end">
                    <a href="add.php" class="btn btn-sm btn-success shadow-sm">+ Nowa wpłata</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 text-white">
        <div class="col-md-6">
            <div class="card bg-success shadow py-3 text-center">
                <div class="small opacity-75 fw-bold">SUMA WPŁAT (<?= $miesiace[$m] ?>)</div>
                <div class="h3 mb-0 fw-bold"><?= number_format($suma_wplat, 2, ',', ' ') ?> PLN</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-danger shadow py-3 text-center">
                <div class="small opacity-75 fw-bold">BRAK WPŁAT</div>
                <div class="h3 mb-0 fw-bold"><?= $nieoplacone_count ?> osób</div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" id="paymentsTable">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Użytkownik</th>
                        <th>Status</th>
                        <th>Kwota</th>
                        <th>Data</th>
                        <th class="text-end pe-4">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($list as $l): ?>
                    <tr class="payment-row">
                        <td class="ps-4 user-name-cell">
                            <a href="../users/view.php?id=<?= $l['id'] ?>" class="text-decoration-none text-dark fw-bold">
                                <?= htmlspecialchars($l['imie'] . ' ' . $l['nazwisko']) ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge rounded-pill <?= $l['payment_id'] ? 'bg-success' : 'bg-warning text-dark' ?>">
                                <?= $l['payment_id'] ? 'Opłacone' : 'Oczekuje' ?>
                            </span>
                        </td>
                        <td class="fw-bold"><?= $l['payment_id'] ? number_format($l['kwota'], 2, ',', ' ') . ' PLN' : '-' ?></td>
                        <td class="small text-muted"><?= $l['data_wplaty'] ?? '-' ?></td>
                        <td class="text-end pe-4">
                            <?php if($l['payment_id']): ?>
                                <div class="btn-group">
                                    <a href="edit.php?id=<?= $l['payment_id'] ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-edit"></i></a>