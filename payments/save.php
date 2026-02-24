<?php
$root = dirname(__DIR__);
require_once $root . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Pobieramy dane z formularza
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $kwota = $_POST['kwota'];
    $miesiac = (int)$_POST['miesiac'];
    $rok = (int)$_POST['rok'];
    $data_wplaty = $_POST['data_wplaty'];
    $metoda = $_POST['metoda'];

    // Prosta walidacja - upewniamy się, że mamy użytkownika
    if ($user_id > 0) {
        try {
            $sql = "INSERT INTO fit_payments (user_id, kwota, miesiac, rok, data_wplaty, metoda) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $kwota, $miesiac, $rok, $data_wplaty, $metoda]);

            // Po sukcesie wracamy do LISTY płatności (list.php)
            header("Location: list.php?msg=added");
            exit;
        } catch (PDOException $e) {
            die("Błąd zapisu do bazy danych: " . $e->getMessage());
        }
    } else {
        die("Błąd: Nie wybrano poprawnego użytkownika.");
    }
} else {
    // Jeśli ktoś wejdzie w ten plik bezpośrednio (bez POST), cofnij go na listę
    header("Location: list.php");
    exit;
}