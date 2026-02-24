<?php
// 1. Włączamy raportowanie błędów - TYLKO DO TESTÓW
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. Poprawne ścieżki - wychodzimy o jeden folder wyżej (..)
require_once '../config/db.php';

try {
    // Pobranie użytkowników z nazwami grup
    $sql = "SELECT u.*, g.nazwa as grupa_nazwa, g.godzina 
            FROM fit_users u 
            LEFT JOIN fit_groups g ON u.group_id = g.id 
            ORDER BY u.id DESC";
    $users = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    die("Błąd zapytania do bazy: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Użytkownicy</h1>
        <a href="add.php" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle me-2"></i> Dodaj klubowicza
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Imię i Nazwisko</th>
                            <th>E-mail</th>
                            <th>Grupa (Godzina)</th>
                            <th>Status</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="5" class="text-center">Brak użytkowników.</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($user['imie'] . ' ' . $user['nazwisko']) ?></strong></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <?php if($user['grupa_nazwa']): ?>
                                        <span class="badge bg-info text-dark">
                                            <?= htmlspecialchars($user['grupa_nazwa']) ?> (<?= substr($user['godzina'], 0, 5) ?>)
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Brak</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $user['subscription_status'] == 'active' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $user['subscription_status'] == 'active' ? 'Aktywny' : 'Nieaktywny' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                                    <a href="delete.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Usunąć?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>