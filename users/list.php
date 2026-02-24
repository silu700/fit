<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Wychodzimy o jeden poziom do góry, bo jesteśmy w folderze /users/
require_once '../config/db.php';

try {
    $stmt = $pdo->query("SELECT u.*, g.nazwa as grupa_nazwa, g.godzina 
                         FROM fit_users u 
                         LEFT JOIN fit_groups g ON u.group_id = g.id 
                         ORDER BY u.id DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Błąd bazy: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Lista Klubowiczów</h3>
        <a href="add.php" class="btn btn-primary btn-sm">Dodaj użytkownika</a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Imię i Nazwisko</th>
                        <th>Grupa</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['imie'] . ' ' . $u['nazwisko']) ?></td>
                        <td><?= $u['grupa_nazwa'] ? htmlspecialchars($u['grupa_nazwa']) : 'Brak' ?></td>
                        <td>
                            <a href="edit.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-info">Edytuj</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>