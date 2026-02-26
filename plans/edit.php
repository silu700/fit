<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 1. Pobieramy dane planu i usera
$stmt = $pdo->prepare("SELECT p.*, u.imie, u.nazwisko FROM fit_training_plans p JOIN fit_users u ON p.user_id = u.id WHERE p.id = ?");
$stmt->execute([$id]);
$plan = $stmt->fetch();

if (!$plan) die("Nie znaleziono planu.");

// 2. Obsługa dodawania ćwiczenia do planu (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_exercise'])) {
    $ex_id = (int)$_POST['exercise_id'];
    $sets = $_POST['sets'];
    $reps = $_POST['reps'];
    $weight = $_POST['weight'];

    $sql = "INSERT INTO fit_plan_details (plan_id, exercise_id, sets, reps, weight) VALUES (?, ?, ?, ?, ?)";
    $pdo->prepare($sql)->execute([$id, $ex_id, $sets, $reps, $weight]);
    header("Location: edit.php?id=$id&msg=added");
    exit;
}

// 3. Pobieramy aktualne ćwiczenia w tym planie
$stmt_details = $pdo->prepare("SELECT d.*, e.nazwa FROM fit_plan_details d JOIN fit_exercises e ON d.exercise_id = e.id WHERE d.plan_id = ?");
$stmt_details->execute([$id]);
$details = $stmt_details->fetchAll();

// 4. Pobieramy listę wszystkich ćwiczeń do selecta
$exercises = $pdo->query("SELECT id, nazwa FROM fit_exercises ORDER BY nazwa ASC")->fetchAll();

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid pt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 fw-bold">Dodaj ćwiczenie do planu</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="add_exercise" value="1">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Wybierz ćwiczenie</label>
                            <select name="exercise_id" class="form-select" required>
                                <?php foreach($exercises as $ex): ?>
                                    <option value="<?= $ex['id'] ?>"><?= htmlspecialchars($ex['nazwa']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Serie</label>
                                <input type="text" name="sets" class="form-control" placeholder="np. 3" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Powtórzenia</label>
                                <input type="text" name="reps" class="form-control" placeholder="np. 12" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Obciążenie (kg/opis)</label>
                            <input type="text" name="weight" class="form-control" placeholder="np. 50kg lub masa ciała">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 shadow-sm">Dodaj do listy</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary">Rozpiska: <?= htmlspecialchars($plan['imie'] . ' ' . $plan['nazwisko']) ?></h6>
                    <a href="list.php" class="btn btn-sm btn-outline-secondary">Zakończ edycję</a>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Ćwiczenie</th>
                                <th>Serie</th>
                                <th>Powt.</th>
                                <th>Ciężar</th>
                                <th>Akcja</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($details as $d): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($d['nazwa']) ?></strong></td>
                                <td><?= htmlspecialchars($d['sets']) ?></td>
                                <td><?= htmlspecialchars($d['reps']) ?></td>
                                <td><?= htmlspecialchars($d['weight']) ?></td>
                                <td>
                                    <a href="delete_item.php?id=<?= $d['id'] ?>&plan_id=<?= $id ?>" class="text-danger"><i class="fas fa-times"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($details)): ?>
                                <tr><td colspan="5" class="text-center text-muted">Plan jest jeszcze pusty.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include $root . '/includes/footer.php'; ?>