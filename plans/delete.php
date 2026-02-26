<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Kaskadowe usuwanie (fit_plan_details usunie się samo jeśli masz klucze obce, 
    // ale na wszelki wypadek robimy to ręcznie jeśli nie masz)
    $pdo->prepare("DELETE FROM fit_plan_details WHERE plan_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM fit_training_plans WHERE id = ?")->execute([$id]);
}

header("Location: list.php?msg=deleted");
exit;