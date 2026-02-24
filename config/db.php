<?php
require __DIR__ . '/../Models/Faktura.php';
require __DIR__ . '/../../config/database.php'; // $pdo

class FakturaController {
    private $fakturaModel;

    public function __construct() {
        global $pdo;
        $this->fakturaModel = new Faktura($pdo);
    }

    public function lista() {
        $faktury = $this->fakturaModel->getAll();
        $page_title = "Lista faktur";
        ob_start();
        include __DIR__ . '/../../views/faktury_lista.php';
        $content = ob_get_clean();
        include __DIR__ . '/../../views/layout.php';
    }

    public function dodaj() {
        $page_title = "Wystaw fakturę";

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dane = $_POST['faktura'];
            $pozycje = $_POST['pozycje'];
            $this->fakturaModel->create($dane, $pozycje);
            header("Location: index.php?page=faktury_lista");
            exit;
        }

        ob_start();
        include __DIR__ . '/../../views/faktury_dodaj.php';
        $content = ob_get_clean();
        include __DIR__ . '/../../views/layout.php';
    }
}
