<?php
require_once '../config/db.php';

// 1. Pobieramy dostępne grupy, żeby wyświetlić je w select
$stmt_groups = $pdo->query("SELECT id, nazwa, godzina FROM fit_groups ORDER BY godzina ASC");
$groups = $stmt_groups->fetchAll();

// 2. Obsługa zapisu po kliknięciu "Zapisz"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $email = $_POST['email'];
    $garmin = $_POST['garmin'];
    $group_id = !empty($_POST['group_id']) ? $_POST['group_id'] : null;

    $sql = "INSERT INTO fit_users (imie, nazwisko, email, garmin_user_link, group_id, subscription_status) 
            VALUES (?, ?, ?, ?, ?, 'active')";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([$imie, $nazwisko, $email, $garmin, $group_id]);
        header("Location: list.php?msg=user_added");
        exit;
    } catch (PDOException $e) {
        $error = "Błąd: " . $e->getMessage();
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-white py-3">
                    <h5 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-plus me-2"></i>Nowy Klubowicz</h5>
                </div>
                <div class="card-body p-4">
                    
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Imię</label>
                                <input type="text" name="imie" class="form-control" placeholder="np. Jan" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nazwisko</label>
                                <input type="text" name="nazwisko" class="form-control" placeholder="np. Kowalski" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">E-mail</label>
                                <input type="email" name="email" class="form-control" placeholder="jan@kowalski.pl" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Przypisz do slotu (Grupa/Godzina)</label>
                                <select name="group_id" class="form-select" required>
                                    <option value="">-- Wybierz grupę --</option>
                                    <?php foreach ($groups as $group): ?>
                                        <option value="<?php echo $group['id']; ?>">
                                            <?php echo $group['nazwa'] . " (" . substr($group['godzina'], 0, 5) . ")"; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">Link do profilu Garmin</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fab fa-bluetooth-b"></i></span>
                                    <input type="url" name="garmin" class="form-control" placeholder="https://connect.garmin.com/...">
                                </div>
                            </div>

                            <div class="col-12 border-top pt-4 mt-4">
                                <button type="submit" class="btn btn-primary px-5 shadow-sm">
                                    <i class="fas fa-check-circle me-2"></i>Zatwierdź i dodaj
                                </button>
                                <a href="list.php" class="btn btn-link text-secondary">Anuluj</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>