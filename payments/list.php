<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

$m = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');
$r = isset($_GET['r']) ? (int)$_GET['r'] : (int)date('Y');

// Pobieramy dane użytkowników i ich statusy płatności
$sql = "SELECT u.id, u.imie, u.nazwisko, p.id as payment_id, p.kwota, p.data_wplaty, p.metoda 
        FROM fit_users u 
        LEFT JOIN fit_payments p ON u.id = p.user_id AND p.miesiac = ? AND p.rok = ?
        WHERE u.subscription_status = 'active'
        ORDER BY u.nazwisko ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$m, $r]);
$list = $stmt->fetchAll();

// Obliczenia do nagłówka
$suma_wplat = 0;
$nieoplacone_count = 0;
foreach ($list as $row) {
    if ($row['payment_id']) $suma_wplat += $row['kwota'];
    else $nieoplacone_count++;
}

$miesiace = [1=>'Styczeń', 2=>'Luty', 3=>'Marzec', 4=>'Kwiecień', 5=>'Maj', 6=>'Czerwiec', 7=>'Lipiec', 8=>'Sierpień', 9=>'Wrzesień', 10=>'Październik', 11=>'Listopad', 12=>'Grudzień'];

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow mb-4 border-left-primary">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <h4 class="m-0 text-gray-800"><?= $miesiace[$m] ?> <?= $r ?></h4>
                    <form class="d-flex mt-2">
                        <select name="m" class="form-select form-select-sm me-1">
                            <?php foreach($miesiace as $num => $name): ?>
                                <option value="<?= $num ?>" <?= $m == $num ? 'selected' : '' ?>><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Zmień</button>
                    </form>
                </div>
                <div class="col-md-3 text-center border-start">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Suma wpłat</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($suma_wplat, 2, ',', ' ') ?> PLN</div>
                </div>
                <div class="col-md-3 text-center border-start">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Brak wpłat</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $nieoplacone_count ?> osób</div>
                </div>
                <div class="col-md-3 text-end">
                    <a href="add.php" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus fa-sm text-white-50"></i> Nowa Wpłata
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Użytkownik</th>
                            <th>Status</th>
                            <th>Kwota</th>
                            <th>Data wpłaty</th>
                            <th>Metoda</th>
                            <th class="text-center">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($list as $l): ?>
                        <tr class="<?= !$l['payment_id'] ? 'table-light' : '' ?>">
                            <td>
                                <a href="../users/view.php?id=<?= $l['id'] ?>" class="text-decoration-none text-dark fw-bold">
                                    <?= htmlspecialchars($l['imie'] . ' ' . $l['nazwisko']) ?>
                                </a>
                            </td>
                            <td>
                                <?php if($l['payment_id']): ?>
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i> OK</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">CZEKA</span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold"><?= $l['kwota'] ? number_format($l['kwota'], 2, ',', ' ') . ' PLN' : '-' ?></td>
                            <td><?= $l['data_wplaty'] ?? '-' ?></td>
                            <td><small><?= $l['metoda'] ?? '-' ?></small></td>
                            <td class="text-center">
                                <?php if($l['payment_id']): ?>
                                    <div class="btn-group">
                                        <a href="edit.php?id=<?= $l['payment_id'] ?>" class="btn btn-sm btn-outline-info" title="Edytuj wpłatę">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $l['payment_id'] ?>" class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Usunąć wpłatę?')" title="Usuń wpłatę">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <a href="add.php?user_id=<?= $l['id'] ?>" class="btn btn-sm btn-success px-3 shadow-sm">Opłać</a>
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

<?php include $root . '/includes/footer.php'; ?>