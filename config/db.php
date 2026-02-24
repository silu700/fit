<?php
// Ustawienia połączenia
$host    = 'localhost';
$db_name = 'silu_ksef';
$user    = 'silu_ksef';
$pass    = 'Ksef01234'; // Twoje hasło do bazy
$charset = 'utf8mb4';

// Opcje PDO dla bezpieczeństwa i wygody
$dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Pokazuj błędy
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      // Zwracaj tablice asocjacyjne
    PDO::ATTR_EMULATE_PREPARES   => false,                 // Używaj prawdziwych przygotowanych zapytań
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // W razie błędu wyświetl czytelny komunikat
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}
?>