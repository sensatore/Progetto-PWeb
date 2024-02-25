<!DOCTYPE html>
<html lang="it">
    <head>
        <title>Visualizza Test Disponibili - DirectorQuiz</title>
        <meta name="author" content="Luca Di Gasparro"> 
        <meta charset="UTF-8">
        <link rel="stylesheet" href="../css/style.css">
    </head>

    <body>
    <div class="containerTestS">

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

            $query = " select Regista, Errori, TestSuperati
                        from TestUtente 
                        where Utente = ? and DataSuperamento is null";
            $statement = mysqli_prepare($connessione, $query);
            if (!$statement){
                die(mysqli_connect_error());
            }

            mysqli_stmt_bind_param($statement, "s", $_SESSION["utente"]);
            if (!mysqli_stmt_execute($statement)){
                die(mysqli_connect_error());
            }

            // controllo se il resultset e' vuoto
            mysqli_stmt_store_result($statement);
            if (mysqli_stmt_num_rows($statement) !== 0){
                // resultset non vuoto
                echo "<table>
                        <thead>
                            <tr>
                                <th>Regista</th>
                                <th>Errori commessi</th>
                                <th>Test Superati</th>
                            </tr>
                        </thead>

                        <tbody>";
                    
                mysqli_stmt_bind_result($statement, $reg, $errori, $testSuperati);
                while (mysqli_stmt_fetch($statement)){
                    echo "<tr>";    
                    echo "<td><a class='inTabella' href='paginaRegista.php?regista=" . $reg . "'>" . $reg . "</a></td><td>" . (is_null($errori) ? "Non è ancora stato svolto nessun quiz" : $errori) . "</td><td>" . $testSuperati . "</td>";
                    echo "</tr>";
                }
                
                echo "   </tbody>
                        </table>
                        ";
                echo "<p>Puoi fare click sul nome di un <em>Regista</em> per rileggere la sua introduzione, o su <em>Test Disponibili</em> di lato per vedere tutti i test che puoi svolgere al momento.</p>";
            } else{
                // result set vuoto
                // vuol dire che non ci sono piu' test che l'utente puo' eseguire
                echo "<p>Complimenti! Hai superato tutti i test disponibili!</p>";
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