<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

// Parametry filtra daty
$m = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');
$r = isset($_GET['r']) ? (int)$_GET['r'] : (int)date('Y');

// Pobieranie danych
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
    <div class="card shadow mb-4 border-0 bg-light">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <form class="d-flex align-items-center">
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
                        <button type="submit" class="btn btn-sm btn-primary px-3">Filtruj</button>
                    </form>
                </div>
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="liveSearch" class="form-control border-start-0" placeholder="Szukaj Użytkownika...">
                    </div>
                </div>
                <div class="col-md-3 text-end">
                    <a href="add.php" class="btn btn-sm btn-success shadow-sm fw-bold">+ NOWA WPŁATA</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-success text-white shadow p-3 border-0">
                <div class="small fw-bold opacity-75 text-uppercase">Wpłaty: <?= $miesiace[$m] ?></div>
                <div class="h3 mb-0 fw-bold"><?= number_format($suma_wplat, 2, ',', ' ') ?> PLN</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-danger text-white shadow p-3 border-0">
                <div class="small fw-bold opacity-75 text-uppercase">Brak wpłaty</div>
                <div class="h3 mb-0 fw-bold"><?= $nieoplacone_count ?> osób</div>
            </div>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="paymentsTable">
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
                        <?php foreach ($list as $l): ?>
                        <tr class="payment-row">
                            <td class="ps-4 user-name-cell">
                                <a href="../users/view.php?id=<?= $l['id'] ?>" class="text-decoration-none fw-bold text-dark">
                                    <?= htmlspecialchars($l['imie'] . ' ' . $l['nazwisko']) ?>
                                </a>
                            </td>
                            <td>
                                <span class="badge rounded-pill <?= $l['payment_id'] ? 'bg-success' : 'bg-warning text-dark' ?>">
                                    <?= $l['payment_id'] ? 'OPŁACONE' : 'CZEKA' ?>
                                </span>
                            </td>
                            <td class="fw-bold"><?= $l['payment_id'] ? number_format($l['kwota'], 2, ',', ' ') . ' PLN' : '-' ?></td>
                            <td class="small text-muted"><?= $l['data_wplaty'] ?? '-' ?></td>
                            <td class="text-end pe-4">
                                <?php if($l['payment_id']): ?>
                                    <div class="btn-group">
                                        <a href="edit.php?id=<?= $l['payment_id'] ?>" class="btn btn-sm btn-outline-info border-0"><i class="fas fa-edit"></i></a>
										<button type="button" 
												class="btn btn-sm btn-outline-danger border-0 super-unique-delete-class" 
												data-id="<?= $l['payment_id'] ?>">
											<i class="fas fa-trash"></i>
										</button>
                                    </div>
                                <?php else: ?>
                                    <a href="add.php?user_id=<?= $l['id'] ?>" class="btn btn-sm btn-success px-4 rounded-pill fw-bold shadow-sm">OPŁAĆ</a>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Używamy nowej, unikalnej klasy
    document.querySelectorAll('.super-unique-delete-class').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation(); // Jeszcze silniejsze zatrzymanie innych skryptów

            const id = this.getAttribute('data-id');
            Swal.fire({
                title: 'Potwierdź usunięcie',
                text: "Wpłata zniknie z historii.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Tak, usuń!',
                cancelButtonText: 'Anuluj'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'delete.php?id=' + id;
                }
            });
        });
    });
});

    // 2. SZUKAJKA NA ŻYWO
    const search = document.getElementById('liveSearch');
    if(search) {
        search.addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            document.querySelectorAll('.payment-row').forEach(row => {
                let name = row.querySelector('.user-name-cell').innerText.toLowerCase();
                row.style.display = name.includes(filter) ? '' : 'none';
            });
        });
    }
});
</script>

<?php 
// 3. POWIADOMIENIA PO PRZEKIEROWANIU
if(isset($_GET['msg'])) {
    $m_type = $_GET['msg'];
    $icon = ($m_type == 'deleted') ? 'info' : 'success';
    $title = ($m_type == 'deleted') ? 'Usunięto!' : 'Zapisano!';
    echo "<script>Swal.fire({ icon: '$icon', title: '$title', timer: 1500, showConfirmButton: false });</script>";
}
?>

<?php include $root . '/includes/footer.php'; ?>