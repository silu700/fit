<?php
// Ustawienia połączenia
$host    = 'localhost';
$db_name = 'silu_bazy';
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
    // Jeśli chcesz sprawdzić czy działa, odkomentuj linię niżej:
    // echo "Połączono!"; 
} catch (\PDOException $e) {
    echo "Błąd logowania!<br>";
    echo "Użytkownik: " . $user . "<br>";
    echo "Host: " . $host . "<br>";
    echo "Szczegóły błędu: " . $e->getMessage();
    exit;
}
?>