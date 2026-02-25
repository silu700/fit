<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 1. Podstawowe dane i grupy
$sql = "SELECT u.*, 
        (SELECT GROUP_CONCAT(g.nazwa SEPARATOR '|') FROM fit_groups g 
         JOIN fit_user_groups ug ON g.id = ug.group_id WHERE ug.user_id = u.id) as grupy
        FROM fit_users u WHERE u.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) die("Nie znaleziono użytkownika.");

// 2. Historia ostatnich 5 wpłat
$payments = $pdo->prepare("SELECT * FROM fit_payments WHERE user_id = ? ORDER BY rok DESC, miesiac DESC LIMIT 5");
$payments->execute([$id]);
$payment_history = $payments->fetchAll();

// 3. Plany treningowe
$plans = $pdo->prepare("SELECT * FROM fit_plans WHERE user_id = ? ORDER BY data_stworzenia DESC");
$plans->execute([$id]);
$user_plans = $plans->fetchAll();

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-circle fa-5x text-secondary"></i>
                    </div>
                    <h4><?= htmlspecialchars($user['imie'] . ' ' . $user['nazwisko']) ?></h4>
                    <span class="badge <?= $user['subscription_status'] == 'active' ? 'bg-success' : 'bg-danger' ?> mb-3">
                        <?= $user['subscription_status'] == 'active' ? 'Aktywny' : 'Nieaktywny' ?>
                    </span>
                    <hr>
                    <div class="text-start">
                        <p><strong>E-mail:</strong> <?= htmlspecialchars($user['email']) ?></p>
                        <p><strong>Grupy:</strong><br>
                            <?php 
                            if ($user['grupy']) {
                                foreach(explode('|', $user['grupy']) as $g) echo '<span class="badge bg-info text-dark me-1">'.$g.'</span>';
                            } else echo "Brak przypisania";
                            ?>
                        </p>
                    </div>
                    <a href="edit.php?id=<?= $id ?>" class="btn btn-primary btn-sm w-100 mt-2">Edytuj profil</a>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">Ostatnie wpłaty</div>
                <div class="card-body p-0">
                    <table class="table table-sm m-0">
                        <?php foreach($payment_history as $p): ?>
                        <tr>
                            <td class="ps-3"><?= $p['miesiac'] ?>/<?= $p['rok'] ?></td>
                            <td class="fw-bold"><?= $p['kwota'] ?> PLN</td>
                            <td><i class="fas fa-check-circle text-success"></i></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Plany Treningowe</h6>
                    <a href="../plans/add.php?user_id=<?= $id ?>" class="btn btn-sm btn-light">+ Nowy Plan</a>
                </div>
                <div class="card-body">
                    <?php if(empty($user_plans)): ?>
                        <p class="text-muted">Ten użytkownik nie ma jeszcze przypisanych planów.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach($user_plans as $plan): ?>
                                <a href="../plans/view.php?id=<?= $plan['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-file-alt me-2 text-primary"></i>
                                        <strong><?= htmlspecialchars($plan['nazwa_planu']) ?></strong>
                                    </div>
                                    <small class="text-muted">Utworzono: <?= date('d.m.Y', strtotime($plan['data_stworzenia'])) ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include $root . '/includes/footer.php'; ?>