<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

// Pobieranie parametrów filtra (domyślnie obecny miesiąc i rok)
$m = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');
$r = isset($_GET['r']) ? (int)$_GET['r'] : (int)date('Y');

// Pobieramy Użytkowników i ich statusy płatności dla wybranego okresu
$sql = "SELECT u.id, u.imie, u.nazwisko, p.id as payment_id, p.kwota, p.data_wplaty, p.metoda 
        FROM fit_users u 
        LEFT JOIN fit_payments p ON u.id = p.user_id AND p.miesiac = ? AND p.rok = ?
        WHERE u.subscription_status = 'active'
        ORDER BY u.nazwisko ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$m, $r]);
$list = $stmt->fetchAll();

// Obliczenia do nagłówka (podsumowanie)
$suma_wplat = 0;
$nieoplacone_count = 0;
foreach ($list as $row) {
    if ($row['payment_id']) {
        $suma_wplat += $row['kwota'];
    } else {
        $nieoplacone_count++;
    }
}

$miesiace = [1=>'Styczeń', 2=>'Luty', 3=>'Marzec', 4=>'Kwiecień', 5=>'Maj', 6=>'Czerwiec', 7=>'Lipiec', 8=>'Sierpień', 9=>'Wrzesień', 10=>'Październik', 11=>'Listopad', 12=>'Grudzień'];
$lata = range(date('Y') - 1, date('Y') + 2); // Zakres: rok wstecz i 2 lata do przodu

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h4 class="m-0 text-gray-800"><?= $miesiace[$m] ?> <?= $r ?></h4>
                    <form class="d-flex mt-2">
                        <select name="m" class="form-select form-select-sm me-1" style="width: 130px;">
                            <?php foreach($miesiace as $num => $name): ?>
                                <option value="<?= $num ?>" <?= $m == $num ? 'selected' : '' ?>><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="r" class="form-select form-select-sm me-1" style="width: 100px;">
                            <?php foreach($lata as $rok): ?>
                                <option value="<?= $rok ?>" <?= $rok == $r ? 'selected' : '' ?>><?= $rok ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-primary">Pokaż</button>
                    </form>
                </div>
                
                <div class="col-md-5">
                    <label class="small fw-bold text-muted text-uppercase">Szybkie szukanie Użytkownika:</label>
                    <div class="input-group input-group-sm shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="liveSearch" class="form-control border-start-0" placeholder="Wpisz imię lub nazwisko...">
                    </div>
                </div>

                <div class="col-md-3 text-end mt-3 mt-md-0">
                    <a href="add.php" class="btn btn-primary shadow-sm btn-sm">
                        <i class="fas fa-plus fa-sm text-white-50"></i> Nowa Wpłata
                    </a>
                </div>
            </div>
        </div>
    </div>

<div class="card shadow mb-4 border-0 bg-white">
    <div class="card-body p-2 px-4">
        <div class="row align-items-center">
            <div class="col-md-3 border-end">
                <span class="text-muted small text-uppercase fw-bold">Okres:</span>
                <span class="ms-2 fw-bold text-dark"><?= $miesiace[$m] ?> <?= $r ?></span>
            </div>
            <div class="col-md-4 border-end text-center">
                <span class="text-muted small text-uppercase fw-bold">Suma wpłat:</span>
                <span class="ms-2 fs-5 fw-bold text-success"><?= number_format($suma_wplat, 2, ',', ' ') ?> PLN</span>
            </div>
            <div class="col-md-3 border-end text-center">
                <span class="text-muted small text-uppercase fw-bold">Brak wpłat:</span>
                <span class="ms-2 fs-5 fw-bold text-danger"><?= $nieoplacone_count ?> osób</span>
            </div>
            <div class="col-md-2 text-end">
                <a href="add.php" class="btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus"></i> Nowa wpłata
                </a>
            </div>
        </div>
    </div>
</div>

    <div class="card shadow mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="paymentsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Użytkownik</th>
                            <th>Status</th>
                            <th>Kwota</th>
                            <th>Data</th>
                            <th>Metoda</th>
                            <th class="text-end pe-4">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($list as $l): ?>
                        <tr class="payment-row <?= !$l['payment_id'] ? 'table-light' : '' ?>">
                            <td class="ps-4 user-name-cell">
                                <a href="../users/view.php?id=<?= $l['id'] ?>" class="text-decoration-none text-dark fw-bold">
                                    <?= htmlspecialchars($l['imie'] . ' ' . $l['nazwisko']) ?>
                                </a>
                            </td>
                            <td>
                                <?php if($l['payment_id']): ?>
                                    <span class="badge bg-success rounded-pill px-3">Opłacone</span>
                                <?php else: ?>
                                    <span class="badge bg-danger rounded-pill px-3">Czeka</span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold text-dark">
                                <?= $l['payment_id'] ? number_format($l['kwota'], 2, ',', ' ') . ' PLN' : '-' ?>
                            </td>
                            <td class="text-muted small"><?= $l['data_wplaty'] ?? '-' ?></td>
                            <td><span class="small"><?= $l['metoda'] ?? '-' ?></span></td>
                            <td class="text-end pe-4">
                                <?php if($l['payment_id']): ?>
                                    <div class="btn-group shadow-sm">
                                        <a href="edit.php?id=<?= $l['payment_id'] ?>" class="btn btn-sm btn-outline-info" title="Edytuj">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $l['payment_id'] ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Usunąć tę wpłatę?')" 
                                           title="Usuń">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <a href="add.php?user_id=<?= $l['id'] ?>" class="btn btn-sm btn-success px-3 shadow-sm">
                                        <i class="fas fa-hand-holding-usd me-1"></i> Opłać
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
</div>

<script>
document.getElementById('liveSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('.payment-row');

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

<?php include $root . '/includes/footer.php'; ?>