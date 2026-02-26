<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

// Pobieramy aktywnych użytkowników do wyboru
$users = $pdo->query("SELECT id, imie, nazwisko FROM fit_users WHERE subscription_status = 'active' ORDER BY nazwisko ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = (int)$_POST['user_id'];
    $data_start = $_POST['data_start'];
    $data_koniec = $_POST['data_koniec'];

    try {
        $sql = "INSERT INTO fit_training_plans (user_id, data_start, data_koniec, czy_aktywny) VALUES (?, ?, ?, 1)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $data_start, $data_koniec]);
        
        $plan_id = $pdo->lastInsertId();
        // Po utworzeniu "ramy" planu, lecimy od razu do dodawania ćwiczeń
        header("Location: edit.php?id=" . $plan_id);
        exit;
    } catch (Exception $e) {
        die("Błąd przy tworzeniu planu: " . $e->getMessage());
    }
}

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid pt-4">
    <div class="card shadow border-0 mx-auto" style="max-width: 600px;">
        <div class="card-header bg-primary text-white py-3">
            <h6 class="m-0 fw-bold"><i class="fas fa-clipboard-list me-2"></i>Nowy Plan Treningowy</h6>
        </div>
        <div class="card-body p-4">
            <form method="POST">
                <div class="mb-4 bg-light p-3 rounded border">
                    <label class="form-label small fw-bold text-uppercase text-primary">Wybierz Klubowicza</label>
                    <input type="text" id="userSearch" class="form-control form-control-sm mb-2" placeholder="Szukaj nazwiska...">
                    <select name="user_id" id="userSelect" class="form-select" size="5" required>
                        <?php foreach($users as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nazwisko'] . ' ' . $u['imie']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-uppercase">Data rozpoczęcia</label>
                        <input type="date" name="data_start" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-uppercase">Data zakończenia</label>
                        <input type="date" name="data_koniec" class="form-control" value="<?= date('Y-m-d', strtotime('+1 month')) ?>" required>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" id="submitBtn" class="btn btn-primary btn-lg shadow-sm" disabled>
                        Utwórz plan i dodaj ćwiczenia <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                    <a href="list.php" class="btn btn-link text-muted small text-decoration-none text-center">Anuluj</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const userSelect = document.getElementById('userSelect');
    const userSearch = document.getElementById('userSearch');
    const submitBtn = document.getElementById('submitBtn');

    // Wyszukiwarka
    userSearch.addEventListener('input', function() {
        let filter = this.value.toLowerCase();
        Array.from(userSelect.options).forEach(opt => {
            opt.style.display = opt.text.toLowerCase().includes(filter) ? '' : 'none';
        });
    });

    // Odblokowanie przycisku
    userSelect.addEventListener('change', () => {
        submitBtn.disabled = false;
    });
</script>

<?php include $root . '/includes/footer.php'; ?>