<?php
$root = realpath(__DIR__ . '/..');
require_once $root . '/config/db.php';

$m = $_GET['m'] ?? date('n');
$r = $_GET['r'] ?? date('Y');

$sql = "SELECT u.id, u.imie, u.nazwisko, p.id as p_id, p.kwota 
        FROM fit_users u 
        LEFT JOIN fit_payments p ON u.id = p.user_id AND p.miesiac = ? AND p.rok = ?
        WHERE u.subscription_status = 'active' ORDER BY u.nazwisko ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$m, $r]);
$list = $stmt->fetchAll();

include $root . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Płatności</h2>
    <a href="add.php" class="btn btn-success">+ Nowa wpłata</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <input type="text" id="liveSearch" class="form-control mb-3" placeholder="Szukaj użytkownika...">
        <table class="table table-hover">
            <thead>
                <tr><th>Użytkownik</th><th>Status</th><th>Kwota</th><th>Akcja</th></tr>
            </thead>
            <tbody>
                <?php foreach($list as $l): ?>
                <tr class="payment-row">
                    <td class="user-name"><?= htmlspecialchars($l['imie'] . ' ' . $l['nazwisko']) ?></td>
                    <td><?= $l['p_id'] ? '<span class="text-success">Opłacone</span>' : '<span class="text-danger">Brak</span>' ?></td>
                    <td><?= $l['p_id'] ? $l['kwota'].' PLN' : '-' ?></td>
                    <td>
                        <?php if($l['p_id']): ?>
                            <a href="delete.php?id=<?= $l['p_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Usunąć wpłatę?')">Usuń</a>
                        <?php else: ?>
                            <a href="add.php?user_id=<?= $l['id'] ?>" class="btn btn-sm btn-outline-primary">Opłać</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('liveSearch').onkeyup = function() {
    let val = this.value.toLowerCase();
    document.querySelectorAll('.payment-row').forEach(row => {
        let name = row.querySelector('.user-name').innerText.toLowerCase();
        row.style.display = name.includes(val) ? '' : 'none';
    });
};
</script>
<?php include $root . '/includes/footer.php'; ?>