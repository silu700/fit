<?php
// Włączamy błędy, żebyś widział co jest nie tak, jeśli znowu "puknie"
ini_set('display_errors', 1);
error_reporting(E_ALL);

$root = dirname(__DIR__); 
require_once $root . '/config/db.php';

// Sprawdzamy czy ID jest w ogóle przekazane w linku
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    // Jeśli nie ma ID, to faktycznie nie ma kogo edytować
    die("Błąd: Nie podano ID użytkownika.");
}

// 1. Pobieramy dane użytkownika
$stmt = $pdo->prepare("SELECT * FROM fit_users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    die("Błąd: Użytkownik o ID $id nie istnieje w bazie.");
}

// 2. Pobieramy wszystkie dostępne grupy
$groups = $pdo->query("SELECT * FROM fit_groups ORDER BY nazwa ASC")->fetchAll();

// 3. Pobieramy ID grup, do których użytkownik należy
$stmt_my_groups = $pdo->prepare("SELECT group_id FROM fit_user_groups WHERE user_id = ?");
$stmt_my_groups->execute([$id]);
$my_groups = $stmt_my_groups->fetchAll(PDO::FETCH_COLUMN);

// 4. Obsługa zapisu
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $email = $_POST['email'];
    $status = $_POST['status'];
    $selected_groups = $_POST['groups'] ?? [];

    $pdo->beginTransaction();
    try {
        $sql_u = "UPDATE fit_users SET imie = ?, nazwisko = ?, email = ?, subscription_status = ? WHERE id = ?";
        $pdo->prepare($sql_u)->execute([$imie, $nazwisko, $email, $status, $id]);

        $pdo->prepare("DELETE FROM fit_user_groups WHERE user_id = ?")->execute([$id]);

        $stmt_link = $pdo->prepare("INSERT INTO fit_user_groups (user_id, group_id) VALUES (?, ?)");
        foreach ($selected_groups as $g_id) {
            $stmt_link->execute([$id, $g_id]);
        }

        $pdo->commit();
        header("Location: list.php?msg=updated");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Błąd zapisu: " . $e->getMessage());
    }
}

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edycja: <?= htmlspecialchars($user['imie'] . ' ' . $user['nazwisko']) ?></h6>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Imię</label>
                        <input type="text" name="imie" class="form-control" value="<?= htmlspecialchars($user['imie']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Nazwisko</label>
                        <input type="text" name="nazwisko" class="form-control" value="<?= htmlspecialchars($user['nazwisko']) ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label>E-mail</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label>Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= $user['subscription_status'] == 'active' ? 'selected' : '' ?>>Aktywny</option>
                        <option value="inactive" <?= $user['subscription_status'] == 'inactive' ? 'selected' : '' ?>>Nieaktywny</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Grupy:</label>
                    <div class="border p-3 rounded bg-light">
                        <?php foreach($groups as $g): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="groups[]" value="<?= $g['id'] ?>" id="g<?= $g['id'] ?>" <?= in_array($g['id'], $my_groups) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="g<?= $g['id'] ?>"><?= htmlspecialchars($g['nazwa']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                <a href="list.php" class="btn btn-secondary">Anuluj</a>
            </form>
        </div>
    </div>
</div>

<?php include $root . '/includes/footer.php'; ?>