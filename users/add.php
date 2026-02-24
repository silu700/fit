<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: list.php"); exit; }

// 1. Pobieramy dane użytkownika
$stmt = $pdo->prepare("SELECT * FROM fit_users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) { die("Użytkownik nie istnieje."); }

// 2. Pobieramy wszystkie dostępne grupy
$groups = $pdo->query("SELECT * FROM fit_groups ORDER BY nazwa ASC")->fetchAll();

// 3. Pobieramy ID grup, do których użytkownik JUŻ należy
$stmt_my_groups = $pdo->prepare("SELECT group_id FROM fit_user_groups WHERE user_id = ?");
$stmt_my_groups->execute([$id]);
$my_groups = $stmt_my_groups->fetchAll(PDO::FETCH_COLUMN); // zwraca prostą tablicę z ID

// 4. Obsługa zapisu formularza
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $email = $_POST['email'];
    $status = $_POST['status'];
    $selected_groups = $_POST['groups'] ?? [];

    $pdo->beginTransaction();
    try {
        // Aktualizacja danych podstawowych
        $sql_u = "UPDATE fit_users SET imie = ?, nazwisko = ?, email = ?, subscription_status = ? WHERE id = ?";
        $pdo->prepare($sql_u)->execute([$imie, $nazwisko, $email, $status, $id]);

        // Aktualizacja grup: najpierw usuwamy wszystkie stare powiązania...
        $pdo->prepare("DELETE FROM fit_user_groups WHERE user_id = ?")->execute([$id]);

        // ...a potem dodajemy nowe wybrane
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
    <div class="card shadow mb-4" style="max-width: 800px;">
        <div class="card-header py-3 bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold">Edycja Klubowicza: <?= htmlspecialchars($user['imie'] . ' ' . $user['nazwisko']) ?></h6>
            <a href="list.php" class="btn btn-sm btn-light">Wróć do listy</a>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Imię</label>
                        <input type="text" name="imie" class="form-control" value="<?= htmlspecialchars($user['imie']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nazwisko</label>
                        <input type="text" name="nazwisko" class="form-control" value="<?= htmlspecialchars($user['nazwisko']) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Status subskrypcji</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= $user['subscription_status'] == 'active' ? 'selected' : '' ?>>Aktywny</option>
                        <option value="inactive" <?= $user['subscription_status'] == 'inactive' ? 'selected' : '' ?>>Nieaktywny</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-primary">Przypisane Grupy</label>
                    <div class="border p-3 rounded bg-light" style="max-height: 250px; overflow-y: auto;">
                        <?php foreach($groups as $g): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="groups[]" 
                                       value="<?= $g['id'] ?>" 
                                       id="g<?= $g['id'] ?>"
                                       <?= in_array($g['id'], $my_groups) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="g<?= $g['id'] ?>">
                                    <?= htmlspecialchars($g['nazwa']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <small class="text-muted italic">Zaznacz lub odznacz grupy, aby zaktualizować członkostwo.</small>
                </div>

                <hr>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-lg">Zapisz zmiany</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include $root . '/includes/footer.php'; ?>