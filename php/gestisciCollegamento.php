<?php
// preleva le risposte date dall'utente mediante il canvas
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");

    if (!isset($_SESSION)) {
        session_start();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Controlla se ci sono dati JSON nel corpo della richiesta
        $jsonData = json_decode(file_get_contents('php://input'), true);
        if ($jsonData !== null) {
            // dati ricevuti come json
            $_SESSION["datiPerCollegamento"] = $jsonData;
            echo json_encode(["message" => "PHP: Dati ricevuti e memorizzati correttamente", "data" => $jsonData]);        
        } else {
            echo json_encode(["error" => "Nessun dato JSON ricevuto"]);
        }
    }

?>
