<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

$users = $pdo->query("SELECT id, imie, nazwisko FROM fit_users WHERE subscription_status = 'active' ORDER BY nazwisko ASC")->fetchAll();
$u_id_get = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow mx-auto" style="max-width: 600px;">
        <div class="card-header bg-dark text-white fw-bold">Nowa Wpłata</div>
        <div class="card-body">
            <form method="POST" action="save.php">
                <div class="mb-3">
                    <label class="form-label fw-bold">Szukaj Użytkownika</label>
                    <input type="text" id="userSearch" class="form-control mb-2 form-control-sm" placeholder="Wpisz nazwisko...">
                    <select name="user_id" id="userSelect" class="form-select" size="5" required>
                        <option value="" disabled <?= $u_id_get == 0 ? 'selected' : '' ?>>-- Wybierz z listy --</option>
                        <?php foreach($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= $u_id_get == $u['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['nazwisko'] . ' ' . $u['imie']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Kwota (PLN)</label>
                        <input type="number" name="kwota" class="form-control" value="150.00" step="0.01">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Metoda</label>
                        <select name="metoda" class="form-select">
                            <option>Gotówka</option>
                            <option>Przelew</option>
                            <option>Karta</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Za miesiąc</label>
                        <input type="number" name="miesiac" class="form-control" value="<?= date('n') ?>" min="1" max="12">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Za rok</label>
                        <input type="number" name="rok" class="form-control" value="<?= date('Y') ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Data wpłaty</label>
                    <input type="date" name="data_wplaty" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>

                <button type="submit" class="btn btn-primary w-100 shadow">Zapisz i wróc do listy</button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('userSearch').addEventListener('input', function() {
    let filter = this.value.toLowerCase();
    let options = document.getElementById('userSelect').options;
    for (let i = 0; i < options.length; i++) {
        if (options[i].value === "") continue;
        options[i].style.display = options[i].text.toLowerCase().includes(filter) ? "" : "none";
    }
});
</script>

<?php include $root . '/includes/footer.php'; ?>