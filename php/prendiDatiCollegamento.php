<?php
// manda al js del canvas titoli ed immagini da stampare
    require "controlloLoginEffettuato.php";
    require "accessodb.php";

    $connessione = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);
    if(mysqli_connect_errno())
        die(mysqli_connect_error());

    // prendo titoli da stampare nel canvas (sono in Domanda)
    $query = "select Domanda
              from TestRegisti
              where Regista = ? and NumeroTest = ? and NumeroDomanda = ?";

    $statement = mysqli_prepare($connessione, $query);
    if (!$statement){
        die(mysqli_connect_error());
    }

    mysqli_stmt_bind_param($statement, "sii", $_SESSION["registaCorrezione"], $_SESSION["numeroQuizCorrezione"], $_SESSION["NumeroDomanda"]);
    if (!mysqli_stmt_execute($statement)){
        die(mysqli_connect_error());
    }

    mysqli_stmt_bind_result($statement, $Domanda);
    $titoli = array();

    while (mysqli_stmt_fetch($statement)){
        $serieTitoli = $Domanda;
    }

    $titoli = explode("-", $serieTitoli);

    // prendo le src delle immagini da stampare nel canvas (sono in Risposta)
    $query1 = "select Risposta
               from RisposteTest
               where Regista = ? and NumTest = ? and NumDomanda = ?";

    $statement1 = mysqli_prepare($connessione, $query1);
    if (!$statement1){
        die(mysqli_connect_error());
    }
    mysqli_stmt_bind_param($statement1, "sii", $_SESSION["registaCorrezione"], $_SESSION["numeroQuizCorrezione"], $_SESSION["NumeroDomanda"]);
    if (!mysqli_stmt_execute($statement1)){
        die(mysqli_connect_error());
    }

    mysqli_stmt_bind_result($statement1, $RispostaCandidata);

    $immagini = array();

    while (mysqli_stmt_fetch($statement1)){
        $serieImmagini = $RispostaCandidata;
    }
    $immagini = explode("-", $serieImmagini);

    // modifico il nome dell'immagine affinche' il file javascript possa risalire al path delle immagini per disegnarle nel canvas
    $immagini = array_map(function($elemento) {
        return "../img/testCol/" . $elemento;
    }, $immagini);

    // Creiamo un array associativo con i dati da inviare
    $data = array(
        'titles' => $titoli,
        'images' => $immagini
    );

    $json_data = json_encode($data);

    // Impostiamo l'header per indicare che la risposta contiene dati JSON
    header('Content-Type: application/json');

    // Restituiamo i dati JSON al file javascript che ha effettuato la richiesta di fetch
    echo $json_data;
?>
