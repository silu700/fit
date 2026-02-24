<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

$users = $pdo->query("SELECT id, imie, nazwisko FROM fit_users WHERE subscription_status = 'active' ORDER BY nazwisko ASC")->fetchAll();

$u_id_get = $_GET['user_id'] ?? '';

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow mx-auto" style="max-width: 600px;">
        <div class="card-header bg-primary text-white">Dodaj wpłatę Użytkownika</div>
        <div class="card-body">
            <form method="POST" action="save.php"> <div class="mb-3">
                    <label class="form-label fw-bold">Znajdź Użytkownika</label>
                    <input list="usersList" id="userSearch" class="form-control" placeholder="Wpisz nazwisko lub imię..." required>
                    <input type="hidden" name="user_id" id="user_id_real" value="<?= $u_id_get ?>">
                    <datalist id="usersList">
                        <?php foreach($users as $u): ?>
                            <option data-id="<?= $u['id'] ?>" value="<?= $u['imie'].' '.$u['nazwisko'] ?>" <?= $u_id_get == $u['id'] ? 'selected' : '' ?>></option>
                        <?php endforeach; ?>
                    </datalist>
                    <small class="text-muted">Zacznij pisać, aby przefiltrować listę.</small>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label fw-bold">Kwota (PLN)</label>
                        <input type="number" name="kwota" class="form-control" value="150.00" step="0.01" placeholder="np. 150.00">
                    </div>
                    <div class="col">
                        <label class="form-label fw-bold">Metoda</label>
                        <select name="metoda" class="form-select">
                            <option>Gotówka</option>
                            <option>Przelew</option>
                            <option>Karta</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col"><label class="form-label">Miesiąc</label><input type="number" name="miesiac" class="form-control" value="<?= date('n') ?>"></div>
                    <div class="col"><label class="form-label">Rok</label><input type="number" name="rok" class="form-control" value="<?= date('Y') ?>"></div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Data faktycznej wpłaty</label>
                    <input type="date" name="data_wplaty" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>

                <button type="submit" class="btn btn-primary w-100 shadow-sm">Zaksięguj wpłatę</button>
            </form>
        </div>
    </div>
</div>

<script>
// Skrypt łączący wyszukiwarkę (datalist) z ukrytym polem ID
document.getElementById('userSearch').addEventListener('input', function(e) {
    let input = e.target;
    let list = document.getElementById('usersList');
    let hiddenInput = document.getElementById('user_id_real');
    let option = Array.from(list.options).find(opt => opt.value === input.value);
    
    if (option) {
        hiddenInput.value = option.getAttribute('data-id');
    }
});

// Jeśli weszliśmy z linku "Opłać" (mamy user_id w GET), ustawiamy imię i nazwisko w polu
window.onload = function() {
    let hiddenInput = document.getElementById('user_id_real');
    if(hiddenInput.value !== "") {
        let list = document.getElementById('usersList');
        let option = Array.from(list.options).find(opt => opt.getAttribute('data-id') === hiddenInput.value);
        if(option) document.getElementById('userSearch').value = option.value;
    }
}
</script>

<?php include $root . '/includes/footer.php'; ?>