<?php
// Włączamy podgląd błędów, żebyś widział co jest nie tak zamiast białej strony
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$root = dirname(__DIR__);
require_once $root . '/config/db.php';

// Pobieramy grupy do listy (bezpieczne zapytanie)
try {
    $groups = $pdo->query("SELECT id, nazwa FROM fit_groups ORDER BY nazwa ASC")->fetchAll();
} catch (Exception $e) {
    $groups = []; // Jeśli tabela grup nie istnieje, nie wywalaj całego skryptu
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $imie = $_POST['imie'] ?? '';
    $nazwisko = $_POST['nazwisko'] ?? '';
    $email = $_POST['email'] ?? '';
    $status = $_POST['status'] ?? 'active';
    $selected_groups = $_POST['groups'] ?? [];

    try {
        $pdo->beginTransaction();

        // 1. Dodajemy użytkownika
        $stmt = $pdo->prepare("INSERT INTO fit_users (imie, nazwisko, email, subscription_status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$imie, $nazwisko, $email, $status]);
        $user_id = $pdo->lastInsertId();

        // 2. Przypisujemy grupy (tylko jeśli tabela fit_user_groups istnieje)
        if (!empty($selected_groups) && $user_id) {
            $ins = $pdo->prepare("INSERT INTO fit_user_groups (user_id, group_id) VALUES (?, ?)");
            foreach ($selected_groups as $g_id) {
                $ins->execute([$user_id, (int)$g_id]);
            }
        }

        $pdo->commit();
        header("Location: list.php?msg=added");
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        die("Błąd bazy danych: " . $e->getMessage());
    }
}

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid d-flex justify-content-center align-items-center" style="min-height: 80vh; padding: 40px 0;">
    <div class="card shadow border-0" style="width: 100%; max-width: 500px;">
        <div class="card-header bg-primary text-white py-3">
            <h6 class="m-0 fw-bold"><i class="fas fa-user-plus me-2"></i>Dodaj Nowego Użytkownika</h6>
        </div>
        <div class="card-body p-4">
            <form method="POST">
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold text-uppercase text-muted">Imię</label>
                        <input type="text" name="imie" class="form-control" placeholder="Jan" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold text-uppercase text-muted">Nazwisko</label>
                        <input type="text" name="nazwisko" class="form-control" placeholder="Kowalski" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-muted">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="email@domena.pl" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-muted">Status</label>
                    <select name="status" class="form-select fw-bold text-primary">
                        <option value="active">Aktywny</option>
                        <option value="inactive">Nieaktywny</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase text-muted">Grupy</label>
                    <div class="border p-2 rounded bg-light" style="max-height: 120px; overflow-y: auto;">
                        <?php foreach($groups as $g): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="groups[]" value="<?= $g['id'] ?>" id="g<?= $g['id'] ?>">
                                <label class="form-check-label small" for="g<?= $g['id'] ?>"><?= htmlspecialchars($g['nazwa']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                        Zapisz użytkownika
                    </button>
                    <a href="list.php" class="btn btn-link text-muted small">Anuluj</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include $root . '/includes/footer.php'; ?>