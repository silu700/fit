<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        // 1. Najpierw pobieramy nazwę obrazka, żeby wiedzieć co usunąć z dysku
        $stmt = $pdo->prepare("SELECT image_path FROM fit_exercises WHERE id = ?");
        $stmt->execute([$id]);
        $ex = $stmt->fetch();

        if ($ex) {
            // 2. Jeśli ćwiczenie ma obrazek, usuwamy go fizycznie z folderu
            if (!empty($ex['image_path'])) {
                $file_path = $root . '/uploads/exercises/' . $ex['image_path'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }

            // 3. Usuwamy rekord z bazy danych
            $delete = $pdo->prepare("DELETE FROM fit_exercises WHERE id = ?");
            $delete->execute([$id]);
        }

        header("Location: list.php?msg=deleted");
        exit;

    } catch (PDOException $e) {
        die("Błąd podczas usuwania: " . $e->getMessage());
    }
} else {
    header("Location: list.php");
    exit;
}