<?php
require_once '../config/db.php';

// 1. Pobranie ID i weryfikacja
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        // 2. Pobieramy informacje o zdjęciu przed usunięciem rekordu
        $stmt = $pdo->prepare("SELECT image_path FROM fit_exercises WHERE id = ?");
        $stmt->execute([$id]);
        $exercise = $stmt->fetch();

        if ($exercise) {
            // 3. Usuwanie fizycznego pliku z serwera
            if (!empty($exercise['image_path'])) {
                $filePath = "../uploads/exercises/" . $exercise['image_path'];
                if (file_exists($filePath)) {
                    unlink($filePath); // Usuwa plik
                }
            }

            // 4. Usuwanie rekordu z bazy danych
            $deleteStmt = $pdo->prepare("DELETE FROM fit_exercises WHERE id = ?");
            $deleteStmt->execute([$id]);

            // przekierowanie z komunikatem sukcesu
            header("Location: list.php?msg=deleted");
            exit;
        } else {
            die("Błąd: Nie znaleziono ćwiczenia o podanym ID.");
        }
    } catch (PDOException $e) {
        die("Błąd bazy danych: " . $e->getMessage());
    }
} else {
    header("Location: list.php");
    exit;
}