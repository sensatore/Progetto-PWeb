<!DOCTYPE html>
<html lang="it">
    <head>
        <title>Quiz del Regista - DirectorQuiz</title>
        <meta name="author" content="Luca Di Gasparro"> 
        <meta charset="UTF-8">
        <link rel="stylesheet" href="../css/style.css">
    </head>
    <body>
    <div class="containerTestS">
        <table>
            <thead>
                <tr><th>Quiz</th>
                <th>Esito</th></tr>
            </thead>
            <tbody>
    <?php
        require "controlloLoginEffettuato.php";
        if (!isset($_GET["regista"]) || empty($_GET["regista"])) {
            echo "<script>alert('Nome del regista non specificato.'); window.history.back();</script>";
            exit();
        } 
            
        require "accessodb.php";
        $connessione = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);
        if (mysqli_connect_errno()){
            die(mysqli_connect_error());
        }

        // controllo se l'utente ha sbloccato il regista
        // se non lo ha sbloccato non posso fargli vedere i test
        $queryC = "select BloccatoDa
                   from Registi
                   where Nome = ?";
        $statementC = mysqli_prepare($connessione, $queryC);
        if (!$statementC){
            die(mysqli_connect_error());
        }
        
        mysqli_stmt_bind_param($statementC, "s", $_GET["regista"]);
        if (!mysqli_stmt_execute($statementC)) {
            die(mysqli_connect_error());
        }

        mysqli_stmt_bind_result($statementC, $regBloc);
        while (mysqli_stmt_fetch($statementC)){
            $registaDaControllare = $regBloc;
        }
        if ($registaDaControllare !== null){
            // esiste un regista che blocca quello attuale
            $queryC1 = "select DataSuperamento
                        from TestUtente
                        where Utente = ? and Regista = ?";
            $statementC1 = mysqli_prepare($connessione, $queryC1);
            if (!$statementC1){
                die(mysqli_connect_error());
            }

            mysqli_stmt_bind_param($statementC1, "ss", $_SESSION["utente"], $registaDaControllare);
            if (!mysqli_stmt_execute($statementC1)) {
                die(mysqli_connect_error());
            }

            mysqli_stmt_store_result($statementC1);
            if (mysqli_stmt_num_rows($statementC1) === 0){
                // non esiste il record nella tabella
                // l'utente non ha ancora sbloccato il regista che blocca quello attuale
                echo "<script>alert('Non hai ancora sbloccato questo regista! Ritorno ai test sbloccati!'); window.location.href = 'testSbloccati.php';</script>";
                exit();
            }

            mysqli_stmt_bind_result($statementC1, $dataC);
            while (mysqli_stmt_fetch($statementC1)){
                $dataSuperamento = $dataC;
            }
            if ($dataSuperamento === null){
                // l'utente non ha superato il regista bloccante 
                echo "<script>alert('Non hai ancora sbloccato questo regista! Ritorno ai test sbloccati!'); window.location.href = 'testSbloccati.php';</script>";
                exit();
            }
        }

        // ricavo i dati per stampare una tabella di tutti i quiz disponibili per il regista
        $query = "select max(NumeroTest)
                  from TestRegisti
                  where Regista = ?";
        $statement = mysqli_prepare($connessione, $query);
        if (!$statement){
            die(mysqli_connect_error());
        }

        mysqli_stmt_bind_param($statement, "s", $_GET["regista"]);
        if (!mysqli_stmt_execute($statement)) {
            die(mysqli_connect_error());
        }

        mysqli_stmt_bind_result($statement, $mTest);
        while (mysqli_stmt_fetch($statement)){
            $maxTest = $mTest;
        }
        if ($maxTest === null){
            // vuol dire che la query non ha dato risultato, quindi max rimanda null
            // significa che non esiste un regista col nome passato in GET
            echo "<script>alert('Nome del regista non valido. Ritorno al menù principale.'); window.location.href = 'index.php';</script>";
            exit();
        }

        // controllo quanti test sono stati gia' superati
        $query1 = "select TestSuperati
                   from TestUtente
                   where Utente = ? and Regista = ?";
        $statement1 = mysqli_prepare($connessione, $query1);
        if (!$statement1){
            die(mysqli_connect_error());
        }

        mysqli_stmt_bind_param($statement1, "ss", $_SESSION["utente"], $_GET["regista"]);
        if (!mysqli_stmt_execute($statement1)){
            die(mysqli_connect_error());
        }

        mysqli_stmt_bind_result($statement1, $testP);
        $testPassati = 0;
        while (mysqli_stmt_fetch($statement1)){
            $testPassati = $testP;
        }

        for ($n=1; $n <= $maxTest; $n++){
            echo "<tr>";
            echo "<td>" . ($n === ($testPassati+1) ? ("<a class='testFattibile' href='paginaTest.php?regista=" . $_GET["regista"] . "&numeroQuiz=" . $n . "'>" . $n . "</a>") : $n) . "</td>";
            echo "<td" . (($n <= $testPassati) ? " class='superato'>Superato" : " class='nonSuperato'>Non superato" ) . "</td>";                
            echo "</tr>";
        }

        // caso test finale con indice 0
        echo "<tr>";
        echo ($testPassati === $maxTest) ? "<td><a class='testFattibile' href='paginaTest.php?regista=" . $_GET["regista"] . "&numeroQuiz=0'>Quiz Finale</a></td>" : "<td>Quiz Finale</td>";   
        echo "<td" . (($testPassati === ($maxTest+1)) ? " class='superato'>Superato" : " class='nonSuperato'>Non superato" ) . "</td>";          
        echo "</tr>";
        echo "</tbody>
            </table>";

        $registaSbloccato = null;
        if ($testPassati === $maxTest+1){
            // ha passato tutti i test
            $query2 = "select Sblocca
                       from Registi
                       where Nome = ?";
            $statement2 = mysqli_prepare($connessione, $query2);
            if (!$statement2){
                die(mysqli_connect_error());
            }

            mysqli_stmt_bind_param($statement2, "s", $_GET["regista"]);
            if (!mysqli_stmt_execute($statement2)){
                die(mysqli_connect_error());
            }

            mysqli_stmt_bind_result($statement2, $nuovoReg);
            while (mysqli_stmt_fetch($statement2)){
                $registaSbloccato = $nuovoReg;
            }

            echo "<div class=paragrafi>";
            echo "<p>Complimenti! Hai superato tutti i quiz di questo regista!</p>";
            echo "<p>Puoi trovare altri test che puoi eseguire ora cliccando su <em>Test Disponibili</em> di lato! </p>";
            if ($registaSbloccato !== null){
                echo "<p>Ora puoi accedere ad un nuovo regista: hai sbloccato <em>" . $registaSbloccato . "</em>! Puoi leggere una breve introduzione su di lui cliccando sul link di lato.</p>";
            }
            echo "</div>";
        }else{
            echo "<div class=paragrafi>";
            echo "<p>Facendo click sul numero di un quiz evidenziato, puoi eseguirlo!</p>";
            echo "<br>";
            echo "<p>Prova a superare tutti i quiz per completare questo regista!</p>";
            echo "</div>";
        }
    ?>

        <nav>
            <ul>
                <li> <a class="pagPrin" href="index.php">Pagina Principale</a> </li>
                <li> <a class="ridireziona" href="testSbloccati.php">Test Disponibili</a></li>
                <?php if ($registaSbloccato !== null) { echo '<li><a class="nuovoRegista" href="paginaRegista.php?regista=' . $registaSbloccato . '">' . $registaSbloccato . '</a></li>'; } ?>
                <li> <a class="logout" href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
    </body>
</html>