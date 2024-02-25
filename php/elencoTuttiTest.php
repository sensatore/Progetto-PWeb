<!DOCTYPE html>
<html lang="it">
    <head>
        <title>Visualizza tutti i Test - DirectorQuiz</title>
        <meta name="author" content="Luca Di Gasparro"> 
        <meta charset="UTF-8">
        <link rel="stylesheet" href="../css/style.css">
    </head>
    
    <body>
    <div class="containerTestS">
        <table>
            <thead>
                <tr>
                    <th>Regista</th>
                    <th>Data superamento</th>
                    <th>Errori commessi</th>
                    <th>Test Superati</th>
                </tr>
            </thead>

            <tbody>

                <?php
                    if (!isset($_SESSION)) {
                        session_start();
                    }
                    require "controlloLoginEffettuato.php";
                    require "accessodb.php";
                    $connessione = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);

                    if (mysqli_connect_errno()){
                        die(mysqli_connect_error());
                    }

                    $query = " select Regista, DataSuperamento, Errori, TestSuperati
                               from TestUtente 
                               where Utente = ?
                               order by 
                                    case
                                        when DataSuperamento is null then 1
                                        else 0
                                    end,
                                    DataSuperamento ASC";
                    $statement = mysqli_prepare($connessione, $query);
                    if (!$statement){
                        die(mysqli_connect_error());
                    }

                    mysqli_stmt_bind_param($statement, "s", $_SESSION["utente"]);
                    if (!mysqli_stmt_execute($statement)){
                        die(mysqli_connect_error());
                    }

                    mysqli_stmt_bind_result($statement, $reg, $data, $err, $testSuperati);

                    while (mysqli_stmt_fetch($statement)){
                        // stampo una riga della colonna con i vari dati che ho ottenuto
                        echo "<tr>";    
                        echo "<td class='" . (is_null($data) ? "nonSuperato" : "superato") . "'><a class='inTabella' href='paginaRegista.php?regista=" . $reg . "'>" . $reg . "</a></td><td>" . (is_null($data) ? "Non ancora superato" : $data) . "</td><td>" . (is_null($err) ? "Non ancora cominciato" : $err) . "</td><td>" . $testSuperati . "</td>";
                        echo "</tr>";
                    }

                ?>
            </tbody>
        </table>
        <div class='paragrafi'>
            <p>Trovi qui un elenco dei testi svolti in passato e di quelli disponibili al momento.</p><br>
            <p>Puoi fare click sul nome di un <em>Regista</em> per rileggere la sua introduzione, o su <em>Test Disponibili</em> di lato per vedere tutti i test che puoi svolgere al momento.</p>
            <br>
            <p>In verde trovi i nomi dei registi di cui hai superato il Test.</p>
            <p>In arancione trovi i nomi dei registi di cui non hai ancora superato il test.</p>
        </div>
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
        </div> 
    </body>

</html>