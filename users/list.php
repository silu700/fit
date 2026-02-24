<?php
require_once '../config/db.php';

// Pobieramy użytkowników wraz z nazwą ich grupy (JOIN)
$sql = "SELECT u.*, g.nazwa as grupa_nazwa, g.godzina 
        FROM fit_users u 
        LEFT JOIN fit_groups g ON u.group_id = g.id 
        ORDER BY u.id DESC";
$users = $pdo->query($sql)->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Lista Użytkowników</h1>
        <a href="add.php" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle me-2"></i> Dodaj nowego klubowicza
        </a>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Pomyślnie dodano użytkownika!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Imię i Nazwisko</th>
                            <th>E-mail</th>
                            <th>Grupa / Godzina</th>
                            <th>Status</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Brak użytkowników w bazie. Kliknij "Dodaj", aby stworzyć pierwszego.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><strong><?php echo $user['imie'] . ' ' . $user['nazwisko']; ?></strong></td>
                                <td><?php echo $user['email']; ?></td>
                                <td>
                                    <?php if($user['grupa_nazwa']): ?>
                                        <span class="badge bg-info text-dark">
                                            <?php echo $user['grupa_nazwa']; ?> (<?php echo substr($user['godzina'], 0, 5); ?>)
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Brak grupy</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $user['subscription_status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $user['subscription_status'] == 'active' ? 'Aktywny' : 'Nieaktywny'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-secondary" title="Edytuj">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="view_plans.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary" title="Plany">
                                            <i class="fas fa-calendar-alt"></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger" title="Usuń" onclick="return confirm('Usunąć tego użytkownika?')">
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

<?php include '../includes/footer.php'; ?>