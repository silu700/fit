<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 1. Pobieramy dane planu, usera i jego grupy
$sql = "SELECT p.*, u.imie, u.nazwisko, ug.group_id, s.godzina 
        FROM fit_training_plans p 
        JOIN fit_users u ON p.user_id = u.id 
        LEFT JOIN fit_user_groups ug ON u.id = ug.user_id
        LEFT JOIN fit_schedule s ON ug.group_id = s.group_id
        WHERE p.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$plan = $stmt->fetch();

if (!$plan) die("Nie znaleziono planu.");

// 2. Obsługa zapisu ćwiczenia
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_exercise'])) {
    $ex_id = (int)$_POST['exercise_id'];
    $kolejnosc = (int)$_POST['kolejnosc'];
    $sets = $_POST['sets'];
    $reps = $_POST['reps'];
    $weight = $_POST['weight'];

    $sql = "INSERT INTO fit_plan_details (plan_id, exercise_id, kolejnosc, sets, reps, weight) VALUES (?, ?, ?, ?, ?, ?)";
    $pdo->prepare($sql)->execute([$id, $ex_id, $kolejnosc, $sets, $reps, $weight]);
    header("Location: edit.php?id=$id&msg=added");
    exit;
}

// 3. Pobieramy obecne ćwiczenia w tym planie
$details = $pdo->prepare("SELECT d.*, e.nazwa FROM fit_plan_details d JOIN fit_exercises e ON d.exercise_id = e.id WHERE d.plan_id = ? ORDER BY d.kolejnosc ASC");
$details->execute([$id]);
$current_exercises = $details->fetchAll();

// 4. LOGIKA KOLIZJI: Sprawdzamy co robi reszta grupy w tym samym czasie
$kolizje = [];
if ($plan['group_id']) {
    $sql_k = "SELECT e.nazwa, u.imie, d.kolejnosc 
              FROM fit_plan_details d
              JOIN fit_training_plans p ON d.plan_id = p.id
              JOIN fit_users u ON p.user_id = u.id
              JOIN fit_user_groups ug ON u.id = ug.user_id
              JOIN fit_exercises e ON d.exercise_id = e.id
              WHERE ug.group_id = ? AND p.id != ? AND p.czy_aktywny = 1";
    $stmt_k = $pdo->prepare($sql_k);
    $stmt_k->execute([$plan['group_id'], $id]);
    $kolizje = $stmt_k->fetchAll();
}

$exercises = $pdo->query("SELECT id, nazwa FROM fit_exercises ORDER BY nazwa ASC")->fetchAll();

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid pt-4">
    <div class="row">
        <div class="col-md-5">
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="m-0 fw-bold"><i class="fas fa-plus-circle me-2"></i>Dodaj ćwiczenie nr <?= count($current_exercises) + 1 ?></h6>
                </div>
                <div class="card-body">
                    <?php if(!empty($kolizje)): ?>
                        <div class="alert alert-warning py-2 mb-3 shadow-sm">
                            <small class="fw-bold"><i class="fas fa-exclamation-triangle me-1"></i> W Twojej grupie zajęte:</small>
                            <ul class="mb-0 ps-3 small">
                                <?php foreach($kolizje as $k): ?>
                                    <li><strong><?= $k['kolejnosc'] ?>.</strong> <?= htmlspecialchars($k['nazwa']) ?> (<?= $k['imie'] ?>)</li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="add_exercise" value="1">
                        <input type="hidden" name="kolejnosc" value="<?= count($current_exercises) + 1 ?>">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Wybierz Maszynę/Ćwiczenie</label>
                            <select name="exercise_id" class="form-select form-select-lg border-primary">
                                <?php foreach($exercises as $ex): ?>
                                    <option value="<?= $ex['id'] ?>"><?= htmlspecialchars($ex['nazwa']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-4"><label class="small fw-bold">Serie</label><input type="text" name="sets" class="form-control" placeholder="3" required></div>
                            <div class="col-4"><label class="small fw-bold">Powt.</label><input type="text" name="reps" class="form-control" placeholder="12" required></div>
                            <div class="col-4"><label class="small fw-bold">Obciąż.</label><input type="text" name="weight" class="form-control" placeholder="50kg"></div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 shadow">Dodaj do planu</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark">Plan dla: <?= htmlspecialchars($plan['imie'] . ' ' . $plan['nazwisko']) ?></h6>
                    <span class="badge bg-info text-dark">Grupa: <?= $plan['godzina'] ? substr($plan['godzina'],0,5) : 'Brak' ?></span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th class="ps-3">Kolejność</th>
                                <th>Ćwiczenie</th>
                                <th>Parametry</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($current_exercises as $d): ?>
                            <tr>
                                <td class="ps-4 text-primary fw-bold">#<?= $d['kolejnosc'] ?></td>
                                <td><strong><?= htmlspecialchars($d['nazwa']) ?></strong></td>
                                <td><?= $d['sets'] ?>x<?= $d['reps'] ?> | <?= $d['weight'] ?></td>
                                <td class="text-end pe-3"><a href="delete_item.php?id=<?= $d['id'] ?>&plan_id=<?= $id ?>" class="text-danger"><i class="fas fa-trash"></i></a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include $root . '/includes/footer.php'; ?>