<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 1. Pobieramy dane o płatności
$stmt = $pdo->prepare("SELECT p.*, u.imie, u.nazwisko FROM fit_payments p JOIN fit_users u ON p.user_id = u.id WHERE p.id = ?");
$stmt->execute([$id]);
$payment = $stmt->fetch();

if (!$payment) { die("Błąd: Nie znaleziono płatności."); }

// 2. Obsługa zapisu (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kwota = $_POST['kwota'];
    $miesiac = (int)$_POST['miesiac'];
    $rok = (int)$_POST['rok'];
    $data_wplaty = $_POST['data_wplaty'];
    $metoda = $_POST['metoda'];

    $sql = "UPDATE fit_payments SET kwota = ?, miesiac = ?, rok = ?, data_wplaty = ?, metoda = ? WHERE id = ?";
    $pdo->prepare($sql)->execute([$kwota, $miesiac, $rok, $data_wplaty, $metoda, $id]);
    
    header("Location: list.php?m=$miesiac&r=$rok&msg=updated");
    exit;
}

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow border-0" style="width: 100%; max-width: 500px;">
        <div class="card-header bg-primary text-white py-3">
            <h6 class="m-0 fw-bold"><i class="fas fa-edit me-2"></i>Edytuj wpłatę</h6>
        </div>
        <div class="card-body p-4">
            <div class="mb-4 text-center">
                <h5 class="text-dark fw-bold"><?= htmlspecialchars($payment['imie'] . ' ' . $payment['nazwisko']) ?></h5>
                <p class="text-muted small">ID Płatności: #<?= $payment['id'] ?></p>
            </div>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase">Kwota (PLN)</label>
                    <input type="number" name="kwota" class="form-control form-control-lg" value="<?= $payment['kwota'] ?>" step="0.01" required>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-uppercase">Miesiąc</label>
                        <input type="number" name="miesiac" class="form-control" value="<?= $payment['miesiac'] ?>" min="1" max="12" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-uppercase">Rok</label>
                        <input type="number" name="rok" class="form-control" value="<?= $payment['rok'] ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase">Data wpłaty</label>
                    <input type="date" name="data_wplaty" class="form-control" value="<?= $payment['data_wplaty'] ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase">Metoda</label>
                    <select name="metoda" class="form-select">
                        <option value="Gotówka" <?= $payment['metoda'] == 'Gotówka' ? 'selected' : '' ?>>Gotówka</option>
                        <option value="Przelew" <?= $payment['metoda'] == 'Przelew' ? 'selected' : '' ?>>Przelew</option>
                        <option value="Karta" <?= $payment['metoda'] == 'Karta' ? 'selected' : '' ?>>Karta</option>
                    </select>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                        <i class="fas fa-save me-2"></i>Zapisz zmiany
                    </button>
                    <a href="list.php?m=<?= $payment['miesiac'] ?>&r=<?= $payment['rok'] ?>" class="btn btn-link text-muted mt-1">Anuluj</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include $root . '/includes/footer.php'; ?>