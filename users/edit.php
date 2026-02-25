<?php
$root = dirname(__DIR__); 
require_once $root . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id === 0) { die("Błąd: Nie podano ID."); }

// 1. Pobieramy dane użytkownika
$stmt = $pdo->prepare("SELECT * FROM fit_users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) { die("Błąd: Użytkownik nie istnieje."); }

// 2. Pobieramy grupy
$groups = $pdo->query("SELECT * FROM fit_groups ORDER BY nazwa ASC")->fetchAll();
$stmt_my = $pdo->prepare("SELECT group_id FROM fit_user_groups WHERE user_id = ?");
$stmt_my->execute([$id]);
$my_groups = $stmt_my->fetchAll(PDO::FETCH_COLUMN);

// 3. Zapis zmian
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $email = $_POST['email'];
    $status = $_POST['status'];
    $selected_groups = $_POST['groups'] ?? [];

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("UPDATE fit_users SET imie = ?, nazwisko = ?, email = ?, subscription_status = ? WHERE id = ?");
        $stmt->execute([$imie, $nazwisko, $email, $status, $id]);

        $pdo->prepare("DELETE FROM fit_user_groups WHERE user_id = ?")->execute([$id]);
        $ins = $pdo->prepare("INSERT INTO fit_user_groups (user_id, group_id) VALUES (?, ?)");
        foreach ($selected_groups as $g_id) { $ins->execute([$id, $g_id]); }

        $pdo->commit();
        header("Location: list.php?msg=updated");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Błąd: " . $e->getMessage());
    }
}

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid d-flex justify-content-center align-items-center" style="min-height: 80vh; padding: 20px 0;">
    <div class="card shadow border-0" style="width: 100%; max-width: 550px;">
        <div class="card-header bg-primary text-white py-3">
            <h6 class="m-0 fw-bold"><i class="fas fa-user-edit me-2"></i>Edytuj Użytkownika</h6>
        </div>
        <div class="card-body p-4">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold text-uppercase">Imię</label>
                        <input type="text" name="imie" class="form-control" value="<?= htmlspecialchars($user['imie']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold text-uppercase">Nazwisko</label>
                        <input type="text" name="nazwisko" class="form-control" value="<?= htmlspecialchars($user['nazwisko']) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase">Status konta</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= $user['subscription_status'] == 'active' ? 'selected' : '' ?>>Aktywny</option>
                        <option value="inactive" <?= $user['subscription_status'] == 'inactive' ? 'selected' : '' ?>>Nieaktywny</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase">Przypisane Grupy</label>
                    <div class="border p-3 rounded bg-light" style="max-height: 150px; overflow-y: auto;">
                        <?php foreach($groups as $g): ?>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="groups[]" value="<?= $g['id'] ?>" id="g<?= $g['id'] ?>" <?= in_array($g['id'], $my_groups) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="g<?= $g['id'] ?> small"><?= htmlspecialchars($g['nazwa']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                        <i class="fas fa-save me-2"></i>Zapisz zmiany
                    </button>
                    <a href="list.php" class="btn btn-link text-muted mt-1">Anuluj</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include $root . '/includes/footer.php'; ?>