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
            <form method="POST" action="save.php" id="paymentForm">
                <div class="mb-3">
                    <label class="form-label fw-bold">Szukaj i wybierz Użytkownika</label>
                    <input type="text" id="userSearch" class="form-control mb-2" placeholder="Zacznij pisać nazwisko...">
                    
                    <select name="user_id" id="userSelect" class="form-select" size="5" required>
                        <option value="" disabled <?= $u_id_get == 0 ? 'selected' : '' ?>>-- MUSISZ WYBRAĆ OSOBĘ --</option>
                        <?php foreach($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= $u_id_get == $u['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['nazwisko'] . ' ' . $u['imie']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Kwota (PLN)</label>
                        <input type="number" name="kwota" class="form-control" value="150.00" step="0.01">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Metoda</label>
                        <select name="metoda" class="form-select">
                            <option>Gotówka</option>
                            <option>Przelew</option>
                            <option>Karta</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Za miesiąc</label>
                        <input type="number" name="miesiac" class="form-control" value="<?= date('n') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Za rok</label>
                        <input type="number" name="rok" class="form-control" value="<?= date('Y') ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Data wpłaty</label>
                    <input type="date" name="data_wplaty" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>

                <button type="submit" id="submitBtn" class="btn btn-primary w-100 shadow" <?= $u_id_get == 0 ? 'disabled' : '' ?>>
                    Zaksięguj wpłatę
                </button>
            </form>
        </div>
    </div>
</div>

<script>
// 1. Logika blokady przycisku
const userSelect = document.getElementById('userSelect');
const submitBtn = document.getElementById('submitBtn');

userSelect.addEventListener('change', function() {
    if (this.value !== "") {
        submitBtn.disabled = false;
        submitBtn.classList.remove('btn-secondary');
        submitBtn.classList.add('btn-primary');
    } else {
        submitBtn.disabled = true;
    }
});

// 2. Filtrowanie listy
document.getElementById('userSearch').addEventListener('input', function() {
    let filter = this.value.toLowerCase();
    let options = userSelect.options;
    for (let i = 0; i < options.length; i++) {
        if (options[i].value === "") continue;
        options[i].style.display = options[i].text.toLowerCase().includes(filter) ? "" : "none";
    }
});
</script>

<?php include $root . '/includes/footer.php'; ?>