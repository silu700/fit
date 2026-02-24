<?php
// Włączamy raportowanie błędów, żeby widzieć co jest nie tak
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$root = dirname(__DIR__);
require_once $root . '/config/db.php';

$m = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');
$r = isset($_GET['r']) ? (int)$_GET['r'] : (int)date('Y');

try {
    $sql = "SELECT u.id, u.imie, u.nazwisko, p.id as payment_id, p.kwota, p.data_wplaty, p.metoda 
            FROM fit_users u 
            LEFT JOIN fit_payments p ON u.id = p.user_id AND p.miesiac = ? AND p.rok = ?
            WHERE u.subscription_status = 'active'
            ORDER BY u.nazwisko ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$m, $r]);
    $list = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Błąd bazy danych: " . $e->getMessage());
}

$suma_wplat = 0;
$nieoplacone_count = 0;
foreach ($list as $row) {
    if ($row['payment_id']) $suma_wplat += (float)$row['kwota'];
    else $nieoplacone_count++;
}

$miesiace = [1=>'Styczeń', 2=>'Luty', 3=>'Marzec', 4=>'Kwiecień', 5=>'Maj', 6=>'Czerwiec', 7=>'Lipiec', 8=>'Sierpień', 9=>'Wrzesień', 10=>'Październik', 11=>'Listopad', 12=>'Grudzień'];
$lata = range(date('Y') - 1, date('Y') + 2);

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <h5 class="m-0 font-weight-bold text-primary"><?= $miesiace[$m] ?> <?= $r ?></h5>
                </div>
                <div class="col-md-3">
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
                        <button type="submit" class="btn btn-sm btn-dark">Pokaż</button>
                    </form>
                </div>
                <div class="col-md-4">
                    <input type="text" id="liveSearch" class="form-control form-control-sm" placeholder="Szukaj Użytkownika...">
                </div>
                <div class="col-md-2 text-end">
                    <a href="add.php" class="btn btn-sm btn-success w-100">+ Dodaj</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-success text-white shadow p-3">
                <div class="small fw-bold opacity-75">ZEBRANO:</div>
                <div class="h3 mb-0 fw-bold"><?= number_format($suma_wplat, 2, ',', ' ') ?> PLN</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-danger text-white shadow p-3">
                <div class="small fw-bold opacity-75">ZALEGŁOŚCI:</div>
                <div class="h3 mb-0 fw-bold"><?= $nieoplacone_count ?> OSÓB</div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0" id="paymentsTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Użytkownik</th>
                        <th>Status</th>
                        <th>Kwota</th>
                        <th>Data</th>
                        <th class="text-end pe-3">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $l): ?>
                    <tr class="payment-row">
                        <td class="ps-3 user-name-cell">
                            <a href="../users/view.php?id=<?= $l['id'] ?>" class="text-decoration-none fw-bold text-dark">
                                <?= htmlspecialchars($l['imie'] . ' ' . $l['nazwisko']) ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge <?= $l['payment_id'] ? 'bg-success' : 'bg-warning text-dark' ?>">
                                <?= $l['payment_id'] ? 'Opłacone' : 'Oczekuje' ?>
                            </span>
                        </td>
                        <td class="fw-bold"><?= $l['payment_id'] ? number_format($l['kwota'], 2, ',', ' ') . ' PLN' : '-' ?></td>
                        <td class="small text-muted"><?= $l['data_wplaty'] ?? '-' ?></td>
                        <td class="text-end pe-3">
                            <?php if($l['payment_id']): ?>
                                <div class="btn-group">
                                    <a href="edit.php?id=<?= $l['payment_id'] ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-edit"></i></a>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="<?= $l['payment_id'] ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
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
// Szukajka
document.getElementById('liveSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    document.querySelectorAll('.payment-row').forEach(row => {
        let name = row.querySelector('.user-name-cell').innerText.toLowerCase();
        row.style.display = name.includes(filter) ? '' : 'none';
    });
});

// Usuwanie SweetAlert2
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        Swal.fire({
            title: 'Na pewno?',
            text: "Wpis zostanie trwale usunięty!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Tak, usuń!',
            cancelButtonText: 'Anuluj'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'delete.php?id=' + id;
            }
        });
    });
});
</script>

<?php 
// Powiadomienia
if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    $icon = ($msg == 'deleted') ? 'info' : 'success';
    $text = ($msg == 'deleted') ? 'Wpłatę usunięto.' : 'Zapisano pomyślnie.';
    echo "<script>Swal.fire({ icon: '$icon', title: '$text', timer: 2000, showConfirmButton: false });</script>";
}
?>

<?php include $root . '/includes/footer.php'; ?>