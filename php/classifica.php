<!DOCTYPE html>
<html lang="it">
    <head>
        <title>Classifica - DirectorQuiz</title>
        <meta name="author" content="Luca Di Gasparro"> 
        <meta charset="UTF-8">
        <link rel="stylesheet" href="../css/style.css">
    </head>

    <body>
    <div class="containerTestS">

    <?php
        require "controlloLoginEffettuato.php";
        require "accessodb.php";
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $connessione = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);

        if (mysqli_connect_errno()){
            die(mysqli_connect_error());
        }

        $query = "select Utente, count(*) as RegistiCompletati, sum(Errori) as ErroriTotali
                  from TestUtente
                  where DataSuperamento is not null
                  group by Utente
                  ORDER BY RegistiCompletati DESC, ErroriTotali DESC
                  limit 25";

        $statement = mysqli_prepare($connessione, $query);
        if (!$statement){
            die(mysqli_connect_error());
        }

        if (!mysqli_stmt_execute($statement)){
            die(mysqli_connect_error());
        }
        mysqli_stmt_bind_result($statement, $utente, $registiCompletati, $errori);

        if (mysqli_stmt_fetch($statement)){
            // se esiste almeno un record, ovver al meno un utente che abbia superato almeno un test
            $utentePresente = false;
            $posizione = 1;
            echo "<table>
                    <thead>
                        <tr>
                            <th>Posizione</th>
                            <th>Utente</th>
                            <th>Test Superati</th>
                            <th>Errori commessi</th>
                        </tr>
                    </thead>

                    <tbody>";
            do {
                echo "<tr";
                if ($utente === $_SESSION['utente']) {
                    $utentePresente = true;
                    echo " class='utenteLogin'";
                }
                echo ">";
                echo "<td>" . $posizione++ . "</td>";
                echo "<td>" . $utente . "</td>";
                echo "<td>" . $registiCompletati . "</td>";
                echo "<td>" . $errori . "</td>";
                echo "</tr>";
            }while (mysqli_stmt_fetch($statement));

            if (!$utentePresente) {
                // se l'utente non e' entrato in classifica, comunque stampo le sue classifiche
                $connessione1 = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);
                $query1 = "select count(*) as RegistiCompletati, sum(Errori) as ErroriTotali
                           from TestUtente
                           where DataSuperamento is not null and Utente = ?
                           group by ?";
                $statement1 = mysqli_prepare($connessione1, $query1);
                if (!$statement1){
                    die(mysqli_connect_error());
                }

                mysqli_stmt_bind_param($statement1, "ss", $_SESSION["utente"], $_SESSION["utente"]);
                if (!mysqli_stmt_execute($statement1)){
                    die(mysqli_connect_error());
                }
                
                mysqli_stmt_bind_result($statement1, $completatiUtente, $erroriUtente);
                echo "<tr class='utenteLogin utenteNonInClassifica'>";
                echo "<td>-</td>"; // Posizione non disponibile
                echo "<td>" . $_SESSION['utente'] . "</td>";
                if (mysqli_stmt_fetch($statement1)){
                    echo "<td>" . $completatiUtente . "</td>";
                    echo "<td>" . $erroriUtente . "</td>";
                    
                }else{
                    echo "<td> 0 </td>";
                    echo "<td> Nessun Test Superato</td>"; 
                }
    
                echo "</tr>";
            }

            echo "</tbody>";
            echo "</table>";

            echo "<p> Vedi quali altri test puoi fare per accumulare più punti cliccando su <em>Test Disponibili</em> di lato!</p>";
        } else{
            echo "<p>Nessun utente del sito ha completato tutti i quiz di almeno un regista. Sii tu il primo! Vedi quali sono disponibili cliccando su <em>Test Disponibili</em> di lato!</p>";
        }
        
    ?>
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