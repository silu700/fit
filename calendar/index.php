<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

// Pobieramy grupy do selecta
$groups = $pdo->query("SELECT id, nazwa FROM fit_groups ORDER BY nazwa ASC")->fetchAll();

// Obsługa dodawania terminu do fit_schedule
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_schedule'])) {
    $g_id = $_POST['group_id'];
    $day = $_POST['dzien'];
    $time = $_POST['godzina'];
    $stmt = $pdo->prepare("INSERT INTO fit_schedule (group_id, dzien_tygodnia, godzina) VALUES (?, ?, ?)");
    $stmt->execute([$g_id, $day, $time]);
    header("Location: index.php?msg=added");
    exit;
}

// Pobieramy cały grafik z fit_schedule
$sql = "SELECT s.*, g.nazwa as grupa_nazwa 
        FROM fit_schedule s 
        JOIN fit_groups g ON s.group_id = g.id 
        ORDER BY s.dzien_tygodnia, s.godzina";
$schedule = $pdo->query($sql)->fetchAll();

$dni = [1 => 'Poniedziałek', 2 => 'Wtorek', 3 => 'Środa', 4 => 'Czwartek', 5 => 'Piątek', 6 => 'Sobota', 7 => 'Niedziela'];

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <h1 class="h3 mb-4">Kalendarz Zajęć</h1>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Dodaj termin do grafiku</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Grupa</label>
                            <select name="group_id" class="form-select" required>
                                <?php foreach($groups as $g): ?>
                                    <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nazwa']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dzień</label>
                            <select name="dzien" class="form-select">
                                <?php foreach($dni as $num => $nazwa): ?>
                                    <option value="<?= $num ?>"><?= $nazwa ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Godzina</label>
                            <input type="time" name="godzina" class="form-control" required>
                        </div>
                        <button type="submit" name="add_schedule" class="btn btn-success w-100">Zapisz w grafiku</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Dzień</th>
                                <th>Godzina</th>
                                <th>Grupa</th>
                                <th>Akcja</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($schedule as $item): ?>
                            <tr>
                                <td><?= $dni[$item['dzien_tygodnia']] ?></td>
                                <td><strong><?= substr($item['godzina'], 0, 5) ?></strong></td>
                                <td><?= htmlspecialchars($item['grupa_nazwa']) ?></td>
                                <td>
                                    <a href="delete.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger">Usuń</a>
                                </td>
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