<?php
require_once '../config/db.php';

// Pobieramy grupy i liczymy ilu użytkowników jest do każdej przypisanych
$sql = "SELECT g.*, (SELECT COUNT(*) FROM fit_users u WHERE u.group_id = g.id) as ilosc_osob 
        FROM fit_groups g 
        ORDER BY g.godzina ASC";
$groups = $pdo->query($sql)->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Grupy / Sploty Godzinowe</h1>
        <a href="add.php" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle me-2"></i> Dodaj nową grupę
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nazwa grupy</th>
                            <th>Godzina rozpoczęcia</th>
                            <th>Liczba klubowiczów</th>
                            <th>Opis</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groups as $g): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($g['nazwa']) ?></strong></td>
                            <td><span class="badge bg-dark fs-6"><?= substr($g['godzina'], 0, 5) ?></span></td>
                            <td><?= $g['ilosc_osob'] ?> os.</td>
                            <td><small class="text-muted"><?= htmlspecialchars($g['opis']) ?></small></td>
                            <td>
                                <a href="edit.php?id=<?= $g['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                                <a href="delete.php?id=<?= $g['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Usunąć grupę?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($groups)): ?>
                            <tr><td colspan="5" class="text-center">Brak grup. Dodaj pierwszą, aby przypisać użytkowników.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>