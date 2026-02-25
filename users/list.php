<?php
// Włączanie błędów dla debugowania
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$root = dirname(__DIR__); 
require_once $root . '/config/db.php';

try {
    // Pobieramy użytkowników oraz listę ich grup (z tabeli łączącej)
    $sql = "SELECT u.*, 
            (SELECT GROUP_CONCAT(g.nazwa SEPARATOR '|') 
             FROM fit_groups g 
             JOIN fit_user_groups ug ON g.id = ug.group_id 
             WHERE ug.user_id = u.id) as grupy_nazwy
            FROM fit_users u 
            ORDER BY u.id DESC";
            
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Błąd bazy danych: " . $e->getMessage());
}

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Lista Klubowiczów</h1>
        <a href="add.php" class="btn btn-primary shadow-sm">
            <i class="fas fa-user-plus me-2"></i> Dodaj użytkownika
        </a>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
        <div class="alert alert-success">Dane użytkownika zostały zaktualizowane!</div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Klubowicz</th>
                            <th>E-mail</th>
                            <th>Przypisane Grupy</th>
                            <th>Status</th>
                            <th class="text-center">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="5" class="text-center py-4">Brak użytkowników.</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($u['imie'] . ' ' . $u['nazwisko']) ?></div>
                                    <small class="text-muted">ID: #<?= $u['id'] ?></small>
                                </td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <?php 
                                    if (!empty($u['grupy_nazwy'])) {
                                        $grupy = explode('|', $u['grupy_nazwy']);
                                        foreach ($grupy as $g) {
                                            echo '<span class="badge bg-info text-dark me-1">' . htmlspecialchars($g) . '</span>';
                                        }
                                    } else {
                                        echo '<span class="text-muted small italic">Brak przypisania</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="badge <?= $u['subscription_status'] == 'active' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $u['subscription_status'] == 'active' ? 'Aktywny' : 'Nieaktywny' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="edit.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edytuj">
                                            <i class="fas fa-edit"></i> Edytuj
                                        </a>
                                        <a href="delete.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Usunąć?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
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

<?php include $root . '/includes/footer.php'; ?>