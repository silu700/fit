<?php
require_once '../config/db.php';

// Pobieramy grupy i liczymy od razu ilu jest w nich zapisanych osób
$sql = "SELECT g.*, (SELECT COUNT(*) FROM fit_users u WHERE u.group_id = g.id) as members_count 
        FROM fit_groups g 
        ORDER BY g.godzina ASC";
$groups = $pdo->query($sql)->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Grupy i Terminy</h1>
        <a href="add.php" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Dodaj Grupę
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Nazwa</th>
                            <th>Godzina</th>
                            <th>Liczba osób</th>
                            <th>Opis</th>
                            <th width="150">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groups as $group): ?>
                        <tr>
                            <td><strong><?php echo $group['nazwa']; ?></strong></td>
                            <td><span class="badge bg-info text-dark"><?php echo substr($group['godzina'], 0, 5); ?></span></td>
                            <td><?php echo $group['members_count']; ?> os.</td>
                            <td><?php echo $group['opis']; ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $group['id']; ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                                <a href="delete.php?id=<?php echo $group['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Czy na pewno?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>