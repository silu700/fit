<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';
$users = $pdo->query("SELECT id, imie, nazwisko FROM fit_users WHERE subscription_status = 'active' ORDER BY nazwisko ASC")->fetchAll();
$u_id_get = $_GET['user_id'] ?? 0;

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="card shadow mx-auto" style="max-width: 500px;">
        <div class="card-header bg-primary text-white">Nowa Wpłata</div>
        <div class="card-body">
            <form action="save.php" method="POST">
                <label class="form-label fw-bold">Wybierz Użytkownika</label>
                <select name="user_id" id="userSelector" class="form-select mb-3" size="5" required>
                    <option value="" disabled <?= !$u_id_get ? 'selected' : '' ?>>-- WYBIERZ OSOBĘ --</option>
                    <?php foreach($users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $u_id_get == $u['id'] ? 'selected' : '' ?>><?= $u['nazwisko'].' '.$u['imie'] ?></option>
                    <?php endforeach; ?>
                </select>

                <div class="mb-3">
                    <label class="form-label">Kwota (PLN)</label>
                    <input type="number" name="kwota" class="form-control" value="150.00" required>
                </div>

                <div class="row mb-4">
                    <div class="col"><label>Miesiąc</label><input type="number" name="miesiac" class="form-control" value="<?= date('n') ?>"></div>
                    <div class="col"><label>Rok</label><input type="number" name="rok" class="form-control" value="<?= date('Y') ?>"></div>
                </div>

                <button type="submit" id="saveBtn" class="btn btn-primary w-100" <?= !$u_id_get ? 'disabled' : '' ?>>Zaksięguj wpłatę</button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('userSelector').onchange = function() {
    document.getElementById('saveBtn').disabled = false;
};
</script>
<?php include $root . '/includes/footer.php'; ?>