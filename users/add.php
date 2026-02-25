<?php
$root = dirname(__DIR__); 
require_once $root . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $email = $_POST['email'];
    $status = $_POST['status'];

    try {
        $stmt = $pdo->prepare("INSERT INTO fit_users (imie, nazwisko, email, subscription_status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$imie, $nazwisko, $email, $status]);
        header("Location: list.php?msg=added");
        exit;
    } catch (Exception $e) {
        die("Błąd: " . $e->getMessage());
    }
}

include $root . '/includes/header.php';
include $root . '/includes/sidebar.php';
?>

<div class="container-fluid d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow border-0" style="width: 100%; max-width: 500px;">
        <div class="card-header bg-success text-white py-3">
            <h6 class="m-0 fw-bold"><i class="fas fa-user-plus me-2"></i>Dodaj Nowego Użytkownika</h6>
        </div>
        <div class="card-body p-4">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase">Imię</label>
                    <input type="text" name="imie" class="form-control" placeholder="np. Jan" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase">Nazwisko</label>
                    <input type="text" name="nazwisko" class="form-control" placeholder="np. Kowalski" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="jan@kowalski.pl" required>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase">Status</label>
                    <select name="status" class="form-select text-success fw-bold">
                        <option value="active">Aktywny</option>
                        <option value="inactive">Nieaktywny</option>
                    </select>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-lg shadow-sm">Utwórz użytkownika</button>
                    <a href="list.php" class="btn btn-link text-muted">Wróć do listy</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include $root . '/includes/footer.php'; ?>