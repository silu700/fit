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

<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

// 1. Parametry filtra daty (domyślnie obecny miesiąc i rok)
$m = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');
$r = isset($_GET['r']) ? (int)$_GET['r'] : (int)date('Y');

// 2. Pobieranie danych z bazy (tylko aktywni użytkownicy i ich wpłaty dla danego okresu)
$sql = "SELECT u.id, u.imie, u.nazwisko, p.id as payment_id, p.kwota, p.data_wplaty, p.metoda 
        FROM fit_users u 
        LEFT JOIN fit_payments p ON u.id = p.user_id AND p.miesiac = ? AND p.rok = ?
        WHERE u.subscription_status = 'active'
        ORDER BY u.nazwisko ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$m, $r]);
$list = $stmt->fetchAll();

// 3. Obliczanie sumy wpłat
$suma = 0;
foreach($list as $row) { 
    if($row['payment_id']) $suma += (float)$row['kwota']; 
}

$miesiace = [1=>'Styczeń',2=>'Luty',3=>'Marzec',4=>'Kwiecień',5=>'Maj',6=>'Czerwiec',7=>'Lipiec',8=>'Sierpień',9=>'Wrzesień',10=>'Październik',11=>'Listopad',12=>'Grudzień'];
$lata = [2024, 2025, 2026];

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="card shadow mb-4 border-0">
        <div class="card-body">
            <div class="row align-items-center g-3">
                <div class="col-md-4">
                    <form class="d-flex gap-2">
                        <select name="m" class="form-select form-select-sm">
                            <?php foreach($miesiace as $n=>$name): ?>
                                <option value="<?= $n ?>" <?= $m == $n ? 'selected' : '' ?>><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="r" class="form-select form-select-sm">
                            <?php foreach($lata as $rok): ?>
                                <option value="<?= $rok ?>" <?= $r == $rok ? 'selected' : '' ?>><?= $rok ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-sm btn-primary">Pokaż</button>
                    </form>
                </div>

                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="liveSearch" class="form-control border-start-0" placeholder="Wpisz imię lub nazwisko...">
                    </div>
                </div>

                <div class="col-md-4 text-end">
                    <span class="me-3 fw-bold text-success fs-5">Suma: <?= number_format($suma, 2, ',', ' ') ?> PLN</span>
                    <a href="add.php" class="btn btn-sm btn-success shadow-sm">+ Nowa wpłata</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow border-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0" id="paymentsTable">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Użytkownik</th>
                        <th>Status</th>
                        <th>Kwota</th>
                        <th>Metoda</th>
                        <th class="text-end pe-4">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($list) > 0): ?>
                        <?php foreach($list as $l): ?>
                        <tr class="payment-row">
                            <td class="ps-4 fw-bold user-name-cell">
                                <?= htmlspecialchars($l['imie'].' '.$l['nazwisko']) ?>
                            </td>
                            <td>
                                <span class="badge rounded-pill <?= $l['payment_id'] ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $l['payment_id'] ? 'Opłacone' : 'Czeka' ?>
                                </span>
                            </td>
                            <td class="fw-bold">
                                <?= $l['payment_id'] ? number_format($l['kwota'], 2, ',', ' ') . ' PLN' : '-' ?>
                            </td>
                            <td class="small text-muted"><?= htmlspecialchars($l['metoda'] ?? '-') ?></td>
                            <td class="text-end pe-4">
                                <?php if($l['payment_id']): ?>
                                    <div class="btn-group shadow-sm">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-modern" data-id="<?= $l['payment_id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <a href="add.php?user_id=<?= $l['id'] ?>" class="btn btn-sm btn-success px-3">
                                        <i class="fas fa-cash-register me-1"></i> Opłać
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-4">Brak aktywnych użytkowników w systemie.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Logika Wyszukiwania na żywo (Live Filter)
    const searchInput = document.getElementById('liveSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            const rows = document.querySelectorAll('.payment-row');
            
            rows.forEach(row => {
                const nameCell = row.querySelector('.user-name-cell');
                if (nameCell) {
                    const name = nameCell.textContent.toLowerCase();
                    row.style.display = name.includes(term) ? '' : 'none';
                }
            });
        });
    }

    // 2. Logika Nowoczesnego Usuwania (SweetAlert2)
    document.querySelectorAll('.btn-delete-modern').forEach(btn => {
        btn.onclick = function(e) {
            e.preventDefault();
            const paymentId = this.getAttribute('data-id');
            
            Swal.fire({
                title: 'Na pewno usunąć?',
                text: "Ta wpłata zostanie trwale skasowana z historii finansowej.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Tak, usuń wpłatę',
                cancelButtonText: 'Anuluj',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'delete.php?id=' + paymentId;
                }
            });
        };
    });
});
</script>

<?php 
// 3. Obsługa komunikatów o sukcesie (np. po usunięciu)
if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    echo "<script>Swal.fire({ icon: 'info', title: 'Usunięto', text: 'Wpłata została skasowana.', timer: 2000, showConfirmButton: false });</script>";
}
?>

<?php include $root . '/includes/footer.php'; ?>

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