<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

$groups = $pdo->query("SELECT * FROM fit_groups ORDER BY nazwa ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $email = $_POST['email'];
    $selected_groups = $_POST['groups'] ?? []; // Tablica wybranych ID grup

    $pdo->beginTransaction();
    try {
        // 1. Dodaj użytkownika
        $stmt = $pdo->prepare("INSERT INTO fit_users (imie, nazwisko, email, subscription_status) VALUES (?, ?, ?, 'active')");
        $stmt->execute([$imie, $nazwisko, $email]);
        $userId = $pdo->lastInsertId();

        // 2. Przypisz do wybranych grup
        $stmtLink = $pdo->prepare("INSERT INTO fit_user_groups (user_id, group_id) VALUES (?, ?)");
        foreach ($selected_groups as $g_id) {
            $stmtLink->execute([$userId, $g_id]);
        }

        $pdo->commit();
        header("Location: list.php?msg=added");
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Błąd: " . $e->getMessage());
    }
}

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow" style="max-width: 700px;">
        <div class="card-header bg-primary text-white"><h5>Nowy Klubowicz</h5></div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3"><label>Imię</label><input type="text" name="imie" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label>Nazwisko</label><input type="text" name="nazwisko" class="form-control" required></div>
                </div>
                <div class="mb-3"><label>E-mail</label><input type="email" name="email" class="form-control" required></div>
                
                <div class="mb-3">
                    <label class="form-label">Przypisz do grup (możesz wybrać kilka)</label>
                    <div class="border p-3 rounded bg-light" style="max-height: 200px; overflow-y: auto;">
                        <?php foreach($groups as $g): ?>
                            <div class="form-check">
                                <input class="form-check-box" type="checkbox" name="groups[]" value="<?= $g['id'] ?>" id="g<?= $g['id'] ?>">
                                <label class="form-check-label" for="g<?= $g['id'] ?>">
                                    <?= htmlspecialchars($g['nazwa']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <small class="text-muted">Zaznacz wszystkie grupy, do których należy użytkownik.</small>
                </div>

                <button type="submit" class="btn btn-success w-100">Zapisz Klubowicza</button>
            </form>
        </div>
    </div>
</div>
<?php include $root . '/includes/footer.php'; ?>