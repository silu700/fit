<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("INSERT INTO fit_payments (user_id, kwota, miesiac, rok, data_wplaty, metoda) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['user_id'], $_POST['kwota'], $_POST['miesiac'], $_POST['rok'], $_POST['data_wplaty'], $_POST['metoda']]);
    header("Location: index.php");
    exit;
}

$u_id = $_GET['user_id'] ?? '';
$users = $pdo->query("SELECT id, imie, nazwisko FROM fit_users WHERE subscription_status = 'active'")->fetchAll();

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>
<div class="container-fluid">
    <div class="card shadow" style="max-width: 500px;">
        <div class="card-header">Zaksięguj wpłatę</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label>Klubowicz</label>
                    <select name="user_id" class="form-select">
                        <?php foreach($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= $u_id == $u['id'] ? 'selected' : '' ?>><?= $u['imie'].' '.$u['nazwisko'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Kwota</label>
                    <input type="number" name="kwota" class="form-control" value="150" step="0.01">
                </div>
                <div class="row mb-3">
                    <div class="col"><label>Miesiąc</label><input type="number" name="miesiac" class="form-control" value="<?= date('n') ?>"></div>
                    <div class="col"><label>Rok</label><input type="number" name="rok" class="form-control" value="<?= date('Y') ?>"></div>
                </div>
                <div class="mb-3">
                    <label>Data wpłaty</label>
                    <input type="date" name="data_wplaty" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="mb-3">
                    <label>Metoda</label>
                    <select name="metoda" class="form-select">
                        <option>Gotówka</option><option>Przelew</option><option>Karta</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Zapisz</button>
            </form>
        </div>
    </div>
</div>
<?php include $root . '/includes/footer.php'; ?>