<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 1. Pobieramy dane o płatności wraz z danymi klienta
$stmt = $pdo->prepare("
    SELECT p.*, u.imie, u.nazwisko 
    FROM fit_payments p 
    JOIN fit_users u ON p.user_id = u.id 
    WHERE p.id = ?
");
$stmt->execute([$id]);
$payment = $stmt->fetch();

if (!$payment) {
    die("Błąd: Nie znaleziono takiej płatności.");
}

// 2. Obsługa zapisu zmian
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kwota = $_POST['kwota'];
    $miesiac = $_POST['miesiac'];
    $rok = $_POST['rok'];
    $data_wplaty = $_POST['data_wplaty'];
    $metoda = $_POST['metoda'];

    try {
        $sql = "UPDATE fit_payments SET kwota = ?, miesiac = ?, rok = ?, data_wplaty = ?, metoda = ? WHERE id = ?";
        $pdo->prepare($sql)->execute([$kwota, $miesiac, $rok, $data_wplaty, $metoda, $id]);
        
        header("Location: index.php?msg=updated");
        exit;
    } catch (Exception $e) {
        die("Błąd zapisu: " . $e->getMessage());
    }
}

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow mb-4" style="max-width: 500px;">
        <div class="card-header py-3 bg-info text-white">
            <h6 class="m-0 font-weight-bold">Edytuj wpłatę: <?= htmlspecialchars($payment['imie'] . ' ' . $payment['nazwisko']) ?></h6>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Klubowicz</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($payment['imie'] . ' ' . $payment['nazwisko']) ?>" disabled>
                    <small class="text-muted">Nie można zmienić przypisania osoby (usuń i dodaj nową, jeśli to błąd).</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kwota (PLN)</label>
                    <input type="number" name="kwota" class="form-control" value="<?= $payment['kwota'] ?>" step="0.01" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Miesiąc</label>
                        <input type="number" name="miesiac" class="form-control" value="<?= $payment['miesiac'] ?>" min="1" max="12" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Rok</label>
                        <input type="number" name="rok" class="form-control" value="<?= $payment['rok'] ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Data wpłaty</label>
                    <input type="date" name="data_wplaty" class="form-control" value="<?= $payment['data_wplaty'] ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Metoda płatności</label>
                    <select name="metoda" class="form-select">
                        <option value="Gotówka" <?= $payment['metoda'] == 'Gotówka' ? 'selected' : '' ?>>Gotówka</option>
                        <option value="Przelew" <?= $payment['metoda'] == 'Przelew' ? 'selected' : '' ?>>Pr