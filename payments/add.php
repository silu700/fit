<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

// Pobieramy tylko aktywnych użytkowników
$users = $pdo->query("SELECT id, imie, nazwisko FROM fit_users WHERE subscription_status = 'active' ORDER BY nazwisko ASC")->fetchAll();

$u_id_get = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow mx-auto" style="max-width: 600px;">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">Zaksięguj wpłatę</h6>
            <a href="list.php" class="btn btn-sm btn-light">Wróć</a>
        </div>
        <div class="card-body">
            <form method="POST" action="save.php">
                
                <div class="mb-4 p-3 bg-light border rounded">
                    <label class="form-label fw-bold text-primary"><i class="fas fa-user me-2"></i>Wybierz Użytkownika</label>
                    
                    <div class="input-group input-group-sm mb-2">
                        <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="userSearchInput" class="form-control" placeholder="Zacznij wpisywać nazwisko, aby przefiltrować listę...">
                    </div>

                    <select name="user_id" id="userSelect" class="form-select" size="5" required>
                        <option value="" disabled <?= $u_id_get == 0 ? 'selected' : '' ?>>-- Wybierz osobę z listy --</option>
                        <?php foreach($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= $u_id_get == $u['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['nazwisko'] . ' ' . $u['imie']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text mt-1">Kliknij na osobę powyżej, aby ją zatwierdzić.</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Kwota (PLN)</label>
                        <input type="number" name="kwota" class="form-control form-control-lg text-success fw-bold" 
                               value="150.00" step="0.01" placeholder="0.00">
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

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Za miesiąc</label>
                        <select name="miesiac" class="form-select">
                            <?php 
                            $m_list = [1=>'Styczeń',2=>'Luty',3=>'Marzec',4=>'Kwiecień',5=>'Maj',6=>'Czerwiec',7=>'Lipiec',8=>'Sierpień',9=>'Wrzesień',10=>'Październik',11=>'Listopad',12=>'Grudzień'];
                            foreach($m_list as $num => $name): ?>
                                <option value="<?= $num ?>" <?= date('n') == $num ? 'selected' : '' ?>><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Rok</label>
                        <input type="number" name="rok" class="form-control" value="<?= date('Y') ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Data otrzymania pieniędzy</label>
                    <input type="date" name="data_wplaty" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 shadow">
                    <i class="fas fa-save me-2"></i> Zapisz wpłatę
                </button>
            </form>
        </div>
    </div>
</div>

<script>
// Funkcja filtrowania selecta na żywo
document.getElementById('userSearchInput').addEventListener('input', function() {
    let filter = this.value.toLowerCase();
    let select = document.getElementById('userSelect');
    let options = select.options;

    for (let i = 0; i < options.length; i++) {
        let txt = options[i].text.toLowerCase();
        // Nie ukrywamy opcji domyślnej "Wybierz osobę"
        if (options[i].value === "") continue;
        
        if (txt.includes(filter)) {
            options[i].style.display = "";
        } else {
            options[i].style.display = "none";
        }
    }
});
</script>

<?php include $root . '/includes/footer.php'; ?>