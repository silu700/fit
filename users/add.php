<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

$groups = $pdo->query("SELECT * FROM fit_groups ORDER BY nazwa ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $email = $_POST['email'];
    $status = $_POST['status'];
    $selected_groups = $_POST['groups'] ?? [];

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO fit_users (imie, nazwisko, email, subscription_status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$imie, $nazwisko, $email, $status]);
        $user_id = $pdo->lastInsertId();

        if (!empty($selected_groups)) {
            $ins = $pdo->prepare("INSERT INTO fit_user_groups (user_id, group_id) VALUES (?, ?)");
            foreach ($selected_groups as $g_id) { $ins->execute([$user_id, $g_id]); }
        }
        $pdo->commit();
        header("Location: list.php?msg=added");
        exit;
    } catch (Exception $e) { $pdo->rollBack(); die("Błąd: " . $e->getMessage()); }
}

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid pt-4"> <div class="card shadow border-0 mx-auto" style="max-width: 550px;">
        <div class="card-header bg-primary text-white py-3">
            <h6 class="m-0 fw-bold"><i class="fas fa-user-plus me-2"></i>Dodaj Nowego Użytkownika</h6>
        </div>
        <div class="card-header py-3 bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">Zaksięguj wpłatę</h6>
            <a href="list.php" class="btn btn-sm btn-light">Wróć</a>
        </div>
        <div class="card-body p-4">
            <form method="POST" id="addUserForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Imię</label>
                        <input type="text" name="imie" id="inputImie" class="form-control" placeholder="np. Jan" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Nazwisko</label>
                        <input type="text" name="nazwisko" id="inputNazwisko" class="form-control" placeholder="np. Kowalski" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-muted">Email</label>
                    <input type="email" name="email" id="inputEmail" class="form-control" placeholder="jan@kowalski.pl" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-muted">Status konta</label>
                    <select name="status" class="form-select fw-bold text-primary">
                        <option value="active">Aktywny</option>
                        <option value="inactive">Nieaktywny</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase text-muted">Przypisz do grup</label>
                    <div class="border p-3 rounded bg-light" style="max-height: 150px; overflow-y: auto;">
                        <?php foreach($groups as $g): ?>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="groups[]" value="<?= $g['id'] ?>" id="g<?= $g['id'] ?>">
                                <label class="form-check-label small" for="g<?= $g['id'] ?>">
                                    <?= htmlspecialchars($g['nazwa']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" id="submitBtn" class="btn btn-secondary btn-lg shadow-sm" disabled>
                        Utwórz użytkownika
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const inputImie = document.getElementById('inputImie');
    const inputNazwisko = document.getElementById('inputNazwisko');
    const inputEmail = document.getElementById('inputEmail');
    const submitBtn = document.getElementById('submitBtn');

    function checkInputs() {
        // Sprawdzamy czy imię i nazwisko mają chociaż 2 znaki
        const isReady = inputImie.value.trim().length > 1 && 
                        inputNazwisko.value.trim().length > 1 && 
                        inputEmail.value.includes('@');

        if (isReady) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-secondary');
            submitBtn.classList.add('btn-primary');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-secondary');
        }
    }

    [inputImie, inputNazwisko, inputEmail].forEach(el => {
        el.addEventListener('input', checkInputs);
    });
</script>

<?php include $root . '/includes/footer.php'; ?>