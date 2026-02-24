<?php
// 1. Włączamy raportowanie błędów - to pokaże nam co dokładnie "boli" skrypt
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$root = dirname(__DIR__);
require_once $root . '/config/db.php';

// Pobieramy ID z adresu URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    die("Błąd: Nieprawidłowe ID użytkownika.");
}

try {
    // 2. Pobieramy dane użytkownika wraz z jego grupami
    // Upewnij się, że tabele nazywają się fit_users, fit_groups i fit_user_groups
    $sql = "SELECT u.*, 
            (SELECT GROUP_CONCAT(g.nazwa SEPARATOR '|') 
             FROM fit_groups g 
             JOIN fit_user_groups ug ON g.id = ug.group_id 
             WHERE ug.user_id = u.id) as grupy
            FROM fit_users u 
            WHERE u.id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) {
        die("Błąd: Użytkownik o ID $id nie istnieje.");
    }

    // 3. Historia ostatnich 5 wpłat
    $stmt_p = $pdo->prepare("SELECT * FROM fit_payments WHERE user_id = ? ORDER BY rok DESC, miesiac DESC LIMIT 5");
    $stmt_p->execute([$id]);
    $payment_history = $stmt_p->fetchAll();

    // 4. Pobieramy plany treningowe (jeśli tabela fit_plans już istnieje)
    // Jeśli jeszcze nie stworzyłeś tabeli fit_plans, zakomentuj te linie poniżej
    $user_plans = [];
    $stmt_plans = $pdo->prepare("SHOW TABLES LIKE 'fit_plans'");
    $stmt_plans->execute();
    if($stmt_plans->rowCount() > 0) {
        $stmt_pl = $pdo->prepare("SELECT * FROM fit_plans WHERE user_id = ? ORDER BY data_stworzenia DESC");
        $stmt_pl->execute([$id]);
        $user_plans = $stmt_pl->fetchAll();
    }

} catch (PDOException $e) {
    die("Błąd bazy danych: " . $e->getMessage());
}

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="list.php">Użytkownicy</a></li>
        <li class="breadcrumb-item active">Profil użytkownika</li>
      </ol>
    </nav>

    <div class="row">
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['imie'].'+'.$user['nazwisko']) ?>&size=128&background=random" class="rounded-circle shadow-sm">
                    </div>
                    <h4 class="mb-1"><?= htmlspecialchars($user['imie'] . ' ' . $user['nazwisko']) ?></h4>
                    <p class="text-muted small mb-3"><?= htmlspecialchars($user['email']) ?></p>
                    <div class="mb-3">
                        <span class="badge <?= $user['subscription_status'] == 'active' ? 'bg-success' : 'bg-danger' ?> p-2 px-3">
                            <?= $user['subscription_status'] == 'active' ? 'Aktywny' : 'Nieaktywny' ?>
                        </span>
                    </div>
                    <hr>
                    <div class="text-start px-3">
                        <label class="text-xs font-weight-bold text-uppercase text-muted mb-1">Przypisane Grupy</label>
                        <div>
                            <?php 
                            if (!empty($user['grupy'])) {
                                $grupy = explode('|', $user['grupy']);
                                foreach ($grupy as $g) {
                                    echo '<span class="badge bg-light text-dark border me-1 mb-1">' . htmlspecialchars($g) . '</span>';
                                }
                            } else {
                                echo '<p class="text-muted small italic">Brak przypisania do grup</p>';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="edit.php?id=<?= $id ?>" class="btn btn-outline-primary btn-sm w-100 mb-2">Edytuj Dane</a>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">Historia ostatnich wpłat</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if(empty($payment_history)): ?>
                            <li class="list-group-item text-center text-muted small">Brak historii wpłat.</li>
                        <?php else: ?>
                            <?php foreach($payment_history as $p): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Miesiąc: <strong><?= $p['miesiac'] ?>/<?= $p['rok'] ?></strong></span>
                                <span class="badge bg-light text-dark border"><?= $p['kwota'] ?> PLN</span>
                            </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-dark text-white">
                    <h6 class="m-0 font-weight-bold text-white">Plany Treningowe</h6>
                    <a href="../plans/add.php?user_id=<?= $id ?>" class="btn btn-sm btn-outline-light">Stwórz nowy plan</a>
                </div>
                <div class="card-body">
                    <?php if(empty($user_plans)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list fa-3x text-light mb-3"></i>
                            <p class="text-muted">Ten użytkownik nie posiada jeszcze planów treningowych.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nazwa planu</th>
                                        <th>Data utworzenia</th>
                                        <th class="text-end">Akcje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($user_plans as $plan): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($plan['nazwa_planu']) ?></strong></td>
                                        <td><?= date('d.m.Y', strtotime($plan['data_stworzenia'])) ?></td>
                                        <td class="text-end">
                                            <a href="../plans/view.php?id=<?= $plan['id'] ?>" class="btn btn-sm btn-info text-white">Zobacz</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include $root . '/includes/footer.php'; ?>