<?php
// 1. Połączenie z bazą - musimy wyjść z folderu users/ do config/
require_once '../config/db.php';

// 2. Pobranie grup do listy rozwijanej
$groups = $pdo->query("SELECT id, nazwa, godzina FROM fit_groups ORDER BY godzina ASC")->fetchAll();

// 3. Obsługa formularza
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $email = $_POST['email'];
    $garmin = $_POST['garmin'];
    $group_id = !empty($_POST['group_id']) ? $_POST['group_id'] : null;

    $sql = "INSERT INTO fit_users (imie, nazwisko, email, garmin_user_link, group_id, subscription_status) 
            VALUES (?, ?, ?, ?, ?, 'active')";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$imie, $nazwisko, $email, $garmin, $group_id])) {
        // Po sukcesie przekieruj do listy
        header("Location: list.php?msg=added");
        exit;
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">Karta Nowego Klubowicza</h6>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Imię</label>
                        <input type="text" name="imie" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Nazwisko</label>
                        <input type="text" name="nazwisko" class="form-control" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>E-mail</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Grupa / Godzina</label>
                        <select name="group_id" class="form-select" required>
                            <option value="">-- Wybierz grupę --</option>
                            <?php foreach($groups as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= $g['nazwa'] ?> (<?= substr($g['godzina'],0,5) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label>Link Garmin</label>
                    <input type="url" name="garmin" class="form-control">
                </div>
                <hr>
                <button type="submit" class="btn btn-success px-4">Zapisz w systemie</button>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>