<?php
// Wymuszamy pokazywanie błędów, żeby zamiast 500 zobaczyć opis błędu
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Dynamiczne ustalanie ścieżki do katalogu głównego
$root = dirname(__DIR__); 

require_once $root . '/config/db.php';

try {
    $users = $pdo->query("SELECT u.*, g.nazwa as grupa_nazwa FROM fit_users u LEFT JOIN fit_groups g ON u.group_id = g.id ORDER BY u.id DESC")->fetchAll();
} catch (PDOException $e) {
    die("Błąd zapytania: " . $e->getMessage());
}

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Lista Klubowiczów</h2>
        <a href="add.php" class="btn btn-primary">Dodaj użytkownika</a>
    </div>
    <div class="card shadow">
        <div class="card-body">
            <table class="table">
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
                        <td><?= htmlspecialchars($u['grupa_nazwa'] ?? 'Brak') ?></td>
                        <td><a href="edit.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-info">Edytuj</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include $root . '/includes/footer.php'; ?>