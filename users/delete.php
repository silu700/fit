<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

// Pobieramy ID z linku
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        // Startujemy transakcję, bo usuwamy powiązania w dwóch tabelach
        $pdo->beginTransaction();

        // 1. Najpierw usuwamy przypisania do grup (klucz obcy)
        $stmt1 = $pdo->prepare("DELETE FROM fit_user_groups WHERE user_id = ?");
        $stmt1->execute([$id]);

        // 2. Potem usuwamy samego użytkownika
        $stmt2 = $pdo->prepare("DELETE FROM fit_users WHERE id = ?");
        $stmt2->execute([$id]);

        $pdo->commit();
        header("Location: list.php?msg=deleted");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Błąd podczas usuwania: " . $e->getMessage());
    }
} else {
    header("Location: list.php");
    exit;
}