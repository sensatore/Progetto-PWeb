<!DOCTYPE html>
<html lang="it">
    <head>
        <title>Pagina Principale - DirectorQuiz</title>
        <meta name="author" content="Luca Di Gasparro"> 
        <meta charset="UTF-8">
        <link rel="stylesheet" href="../css/style.css">
    </head>

    <body>
        <div class="containerIndex">
        <nav>
            <ul>
                <li> <a class="pagPrin" href="index.php">Pagina Principale</a> </li>
                <li> <a class="ridireziona" href="testSbloccati.php">Test Disponibili</a></li>
                <li> <a class="ridireziona" href="elencoTuttiTest.php">Cronologia Test</a></li>
                <li> <a class="classifica" href="classifica.php">Classifica Utenti</a></li>
                <li> <a class="guida" href="guida.html">Guida Utente</a></li>
                <li> <a class="logout" href="logout.php">Logout</a></li>
            </ul>
        </nav>


<?php

    require "accessodb.php";
    require "controlloLoginEffettuato.php";

    $connessione = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);
    if(mysqli_connect_errno())
        die(mysqli_connect_error());

    // recupero il numero di registi completati dall'utente
    $query = "select count(*) 
            from TestUtente  
            where Utente = ? and DataSuperamento is not null";
    
    $statement = mysqli_prepare($connessione, $query);
    if (!$statement){
        die(mysqli_connect_error());
    }

    mysqli_stmt_bind_param($statement, "s", $_SESSION["utente"]);
    if (!mysqli_stmt_execute($statement)){
        die(mysqli_connect_error());
    }

    mysqli_stmt_bind_result($statement, $resultSet);

    while (mysqli_stmt_fetch($statement)){
        $testPassati = $resultSet;
    }

    //recupero il numero totale di registi 
    $query1 = "select count(*)
            from Registi ";
    
    $statement1 = mysqli_prepare($connessione, $query1);
    if (!$statement1){
        die(mysqli_connect_error());
    }

    if (!mysqli_stmt_execute($statement1)){
        die(mysqli_connect_error());
    }

    mysqli_stmt_bind_result($statement1, $resultSet1);

    while (mysqli_stmt_fetch($statement1)){
        $testTotali = $resultSet1;
    }
    echo '<div>';
    echo '<p>Hai superato <span style="color: green;">' . $testPassati . '</span> su un totale di <span style="color: blue;">' . $testTotali . '</span>, quindi hai completato i test al <span style="color: orange;">' . ($testTotali === 0 ? 0 : ($testPassati / $testTotali) * 100) . '%</span></p>';
    echo '<br> <p> Usa il menù qui di fianco per navigare tra le sezioni del sito!</p>';
    echo '</div>';
?>
        </div>
    </body>

</html>


