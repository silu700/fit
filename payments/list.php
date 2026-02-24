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
    if ($row['payment_id']) $suma_wplat += (float)$row['kwota'];
    else $nieoplacone_count++;
}

$miesiace = [1=>'Styczeń', 2=>'Luty', 3=>'Marzec', 4=>'Kwiecień', 5=>'Maj', 6=>'Czerwiec', 7=>'Lipiec', 8=>'Sierpień', 9=>'Wrzesień', 10=>'Październik', 11=>'Listopad', 12=>'Grudzień'];
$lata = range(date('Y') - 1, date('Y') + 2);

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="row mb-4 mt-3">
        <div class="col-md-6"><div class="card bg-success text-white p-3 shadow-sm text-center"><h5>Zebrano: <?= number_format($suma_wplat, 2) ?> PLN</h5></div></div>
        <div class="col-md-6"><div class="card bg-danger text-white p-3 shadow-sm text-center"><h5>Brak wpłat: <?= $nieoplacone_count ?> osób</h5></div></div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
            <h6 class="m-0 font-weight-bold text-primary">Płatności: <?= $miesiace[$m] ?> <?= $r ?></h6>
            <input type="text" id="liveSearch" class="form-control form-control-sm w-25" placeholder="Szukaj...">
        </div>
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0" id="paymentsTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Użytkownik</th>
                        <th>Status</th>
                        <th>Kwota</th>
                        <th class="text-end pe-4">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $l): ?>
                    <tr class="payment-row">
                        <td class="ps-4 user-name">
                            <a href="../users/view.php?id=<?= $l['id'] ?>" class="text-decoration-none fw-bold text-dark">
                                <?= htmlspecialchars($l['imie'] . ' ' . $l['nazwisko']) ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge <?= $l['payment_id'] ? 'bg-success' : 'bg-warning text-dark' ?>">
                                <?= $l['payment_id'] ? 'Opłacone' : 'Czeka' ?>
                            </span>
                        </td>
                        <td class="fw-bold"><?= $l['payment_id'] ? number_format($l['kwota'], 2) . ' PLN' : '-' ?></td>
                        <td class="text-end pe-4">
                            <?php if($l['payment_id']): ?>
                                <div class="btn-group">
                                    <a href="edit.php?id=<?= $l['payment_id'] ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-edit"></i></a>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-payment" data-id="<?= $l['payment_id'] ?>">
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Obsługa usuwania
    const deleteButtons = document.querySelectorAll('.delete-payment');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            
            Swal.fire({
                title: 'Na pewno usunąć?',
                text: "Nie odzyskasz tej wpłaty z bazy!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Tak, usuń!',
                cancelButtonText: 'Anuluj'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Sprawdzamy czy ścieżka jest dobra
                    window.location.href = 'delete.php?id=' + id;
                }
            });
        });
    });

    // 2. Szukajka
    document.getElementById('liveSearch').addEventListener('keyup', function() {
        let val = this.value.toLowerCase();
        document.querySelectorAll('.payment-row').forEach(row => {
            let name = row.querySelector('.user-name').innerText.toLowerCase();
            row.style.display = name.includes(val) ? '' : 'none';
        });
    });
});
</script>

<?php 
// 3. Powiadomienia po akcji
if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    $icon = ($msg == 'deleted') ? 'info' : 'success';
    $title = ($msg == 'deleted') ? 'Usunięto' : 'Sukces';
    echo "<script>Swal.fire({ icon: '$icon', title: '$title', timer: 1500, showConfirmButton: false });</script>";
}
?>

<?php include $root . '/includes/header.php'; ?>
<?php include $root . '/includes/footer.php'; ?>