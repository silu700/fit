<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM fit_payments WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}
header("Location: list.php?msg=deleted");
exit;