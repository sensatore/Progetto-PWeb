<?php
    if (!isset($_SESSION)) {
        session_start();
    }

    if (!isset($_SESSION["utente"])){
        // non e' stato effettuato il login
        // dato che le pagine del sito necessitano tutte di accedere a dei dati di utenti, e' necessario effettuarlo
        header("Location: accedi.php");
        exit();
    }

?>