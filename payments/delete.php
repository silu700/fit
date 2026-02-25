<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM fit_payments WHERE id = ?");
    $stmt->execute([$id]);
}

// Wracamy na listę z komunikatem
header("Location: list.php?msg=deleted");
exit;