<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: list.php"); exit; }

// 1. Pobieramy dane grupy
$stmt = $pdo->prepare("SELECT * FROM fit_groups WHERE id = ?");
$stmt->execute([$id]);
$group = $stmt->fetch();

if (!$group) { die("Grupa nie istnieje."); }

// 2. Obsługa zapisu
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nazwa = $_POST['nazwa'];
    $opis = $_POST['opis'];

    try {
        $sql = "UPDATE fit_groups SET nazwa = ?, opis = ? WHERE id = ?";
        $pdo->prepare($sql)->execute([$nazwa, $opis, $id]);
        
        header("Location: list.php?msg=updated");
        exit;
    } catch (Exception $e) {
        die("Błąd zapisu: " . $e->getMessage());
    }
}

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow mb-4" style="max-width: 600px;">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">Edytuj Grupę: <?= htmlspecialchars($group['nazwa']) ?></h6>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nazwa Grupy</label>
                    <input type="text" name="nazwa" class="form-control" 
                           value="<?= htmlspecialchars($group['nazwa']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Opis / Cel grupy</label>
                    <textarea name="opis" class="form-control" rows="4"><?= htmlspecialchars($group['opis']) ?></textarea>
                </div>

                <div class="alert alert-info small">
                    <i class="fas fa-info-circle me-2"></i> 
                    Pamiętaj, że dni i godziny zajęć dla tej grupy edytujesz w sekcji 
                    <a href="../calendar/index.php" class="alert-link">Kalendarz</a>.
                </div>

                <hr>
                <div class="d-flex justify-content-between">
                    <a href="list.php" class="btn btn-secondary">Anuluj</a>
                    <button type="submit" class="btn btn-success px-4">Zapisz zmiany</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include $root . '/includes/footer.php'; ?>