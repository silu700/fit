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
        <div class="card-header py-3 bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">Zaksięguj wpłatę</h6>
            <a href="list.php" class="btn btn-sm btn-light">Wróć</a>
        </div>
        <div class="card-body">
            <form method="POST" action="save.php" id="paymentForm">
                <div class="mb-4 p-3 bg-light border rounded">
                    <label class="form-label fw-bold text-primary"><i class="fas fa-user me-2"></i>Wybierz Użytkownika</label>
                    <div class="input-group input-group-sm mb-2">
                        <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="userSearchInput" class="form-control" placeholder="Szukaj nazwiska...">
                    </div>

                    <select name="user_id" id="userSelect" class="form-select" size="5" required>
                        <option value="" disabled <?= $u_id_get == 0 ? 'selected' : '' ?>>-- Wybierz osobę z listy --</option>
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
                        <input type="number" name="kwota" class="form-control form-control-lg text-success fw-bold" value="150.00" step="0.01">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Metoda</label>
                        <select name="metoda" class="form-select form-select-lg">
                            <option>Gotówka</option>
                            <option>Przelew</option>
                            <option>Karta</option>
                        </select>
                    </div>
                </div>

                <button type="submit" id="submitBtn" class="btn btn-primary btn-lg w-100 shadow" disabled>
                    <i class="fas fa-save me-2"></i> Zapisz wpłatę
                </button>
            </form>
        </div>
    </div>
</div>

<script>
const userSelect = document.getElementById('userSelect');
const submitBtn = document.getElementById('submitBtn');
const searchInput = document.getElementById('userSearchInput');

// Funkcja odblokowująca przycisk
function validateForm() {
    if (userSelect.value && userSelect.value !== "") {
        submitBtn.disabled = false;
        submitBtn.classList.remove('btn-secondary');
        submitBtn.classList.add('btn-primary');
    } else {
        submitBtn.disabled = true;
    }
}

// Sprawdzenie przy zmianie selecta
userSelect.addEventListener('change', validateForm);

// Sprawdzenie na starcie (jeśli user_id jest w URL)
window.onload = validateForm;

// Wyszukiwarka
searchInput.addEventListener('input', function() {
    let filter = this.value.toLowerCase();
    let options = userSelect.options;
    for (let i = 0; i < options.length; i++) {
        if (options[i].value === "") continue;
        options[i].style.display = options[i].text.toLowerCase().includes(filter) ? "" : "none";
    }
});
</script>

<?php include $root . '/includes/footer.php'; ?>