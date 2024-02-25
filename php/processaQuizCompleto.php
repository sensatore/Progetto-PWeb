<!DOCTYPE html>
<html lang="it">
<head>
    <title>Correzione Quiz - DirectorQuiz</title>
    <meta name="author" content="Luca Di Gasparro"> 
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/style.css">
</head> 


<?php

    require "controlloLoginEffettuato.php";
    require "accessodb.php";

    // controllo che i dai siano presenti
    if (!isset($_SESSION["registaCorrezione"]) || empty($_SESSION["registaCorrezione"]) || !isset($_SESSION["numeroQuizCorrezione"])) {
        echo "<script>alert('Nome del regista non specificato.'); window.history.back();</script>";
        exit();
    }

    $connessione = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);
    if(mysqli_connect_errno()){
        die(mysqli_connect_error());
    }

    // controllo di poter fare questo test
    // per evitare che ricaricare la pagina possa provocare un aumento indefinito del numero di test passati
    // o che causi errore nell'inserimento del nuovo regista se gia' inserito
    $qC = "select TestSuperati
           from TestUtente
           where DataSuperamento is null and Utente = ? and Regista = ?";

    $stC = mysqli_prepare($connessione, $qC);
    if (!$stC){
        die(mysqli_connect_error());
    }

    mysqli_stmt_bind_param($stC, "si", $_SESSION["utente"], $_SESSION["registaCorrezione"]);
    if (!mysqli_stmt_execute($stC)){
        die(mysqli_connect_error());
    }

    mysqli_stmt_store_result($stC);
    if (mysqli_stmt_num_rows($stC) === 0){
        // non esiste il record nella tabella
        // il regista non e' valido, non e' stato sbloccato o il test e' stato gia' superato
        echo "<script>alert('Test non corregibile'); window.location.href = 'paginaQuizRegista.php?regista=" . $_SESSION["registaCorrezione"] . "';</script>";
        exit();
    }

    mysqli_stmt_bind_result($stC, $tS);
    while (mysqli_stmt_fetch($stC)){
        $testSuperati = $tS;
    }

    // ora recupero il quiz di numero piu' elevato nel test del regista
    $qC1 = "select max(NumeroTest)
            from TestRegisti
            where Regista = ?";
    $stC1 = mysqli_prepare($connessione, $qC1);
    if (!$stC1){
        die(mysqli_connect_error());
    }

    mysqli_stmt_bind_param($stC1, "s", $_SESSION["registaCorrezione"]);
    if (!mysqli_stmt_execute($stC1)) {
        die(mysqli_connect_error());
    }

    mysqli_stmt_bind_result($stC1, $mTest);
    while (mysqli_stmt_fetch($stC1)){
        $maxTest = $mTest;
    }

    if (($_SESSION["numeroQuizCorrezione"] > 0 and $_SESSION["numeroQuizCorrezione"] != ($testSuperati+1)) or ($_SESSION["numeroQuizCorrezione"] === 0 and $testSuperati !== $maxTest)){
        echo "<script>alert('Test non corregibile'); window.location.href = 'paginaQuizRegista.php?regista=" . $_SESSION["registaCorrezione"] . "';</script>";
        exit();
    }

    
    // prendo dati necessari per la correzione
    $query = "select Tipo, NumeroDomanda, RispCorretta
              from TestRegisti
              where Regista = ? and NumeroTest = ?";

    $statement = mysqli_prepare($connessione, $query);
    if (!$statement){
        die(mysqli_connect_error());
    }

    mysqli_stmt_bind_param($statement, "si", $_SESSION["registaCorrezione"], $_SESSION["numeroQuizCorrezione"]);
    if (!mysqli_stmt_execute($statement)){
        die(mysqli_connect_error());
    }
    mysqli_stmt_bind_result($statement, $Tipo, $NumeroDomanda, $RispostaCorretta);

    $nErrori = 0;
    $totDomande = 0;
    while (mysqli_stmt_fetch($statement)){
        if ($Tipo !== "collegamento"){
            // i dati da confrontare li ho ottenuti direttamente dal form
            if ($Tipo === "testo"){
                // porto la stringa in strlower per uniformita
                $_POST["domanda_" . $NumeroDomanda] = strtolower($_POST["domanda_" . $NumeroDomanda]);
            }
            if ($RispostaCorretta !== $_POST["domanda_" . $NumeroDomanda]){
                // l'utente ha dato la risposta sbagliata
                $nErrori ++;
            }
            $totDomande = $NumeroDomanda;
        }else{
            //require "gestisciCollegamento.php";
            // ho bisogno di logica aggiuntiva per ricavare le risposte dal canvas
            $totDomande = $NumeroDomanda;
            $rispostaCollegamento = $_SESSION["datiPerCollegamento"];
            $sottostringe = explode("-", $rispostaCollegamento);
            foreach ($sottostringe as &$substring) {
                $substring = str_replace("../img/testCol/", "", $substring);
            }
            $risposteC = implode("-", $sottostringe);

            if ($risposteC !== $RispostaCorretta){
                $nErrori++;
            }
        }
    } 

    $percentCorrette = ($totDomande - $nErrori) / $totDomande;
    if ($percentCorrette >= 0.6){
        // l'utente ha passato il test
        // aggiorno il database aggiungendo gli errori e il test corrente
        $query1 = "update TestUtente
                   set TestSuperati = TestSuperati+1, Errori = IFNULL(Errori, 0) + ?
                   where Utente = ? and Regista = ?";

        $statement1 = mysqli_prepare($connessione, $query1);
        if (!$statement1){
            die(mysqli_connect_error());
        }
        mysqli_stmt_bind_param($statement1, "iss", $nErrori, $_SESSION["utente"], $_SESSION["registaCorrezione"]);

        if (!mysqli_stmt_execute($statement1)){
            die(mysqli_connect_error());
        }

        if ($_SESSION["numeroQuizCorrezione"] == 0){
            // l'utente ha passato il quiz finale
            // aggiorno di conseguenza il database

            $query2 = "update TestUtente
                       set DataSuperamento = ?
                       where Utente = ? and Regista = ?";

            $statement2 = mysqli_prepare($connessione, $query2);
            if (!$statement2){
                die(mysqli_connect_error());
            }

            $dataCorrente = date("Y-m-d");
            mysqli_stmt_bind_param($statement2, "sss", $dataCorrente, $_SESSION["utente"], $_SESSION["registaCorrezione"]);
            if (!mysqli_stmt_execute($statement2)){
                die(mysqli_connect_error());
            }

            // ora aggiorno di nuovo la tabella TestUtente per inserire un record sul regista sbloccato, se esiste

            // recupero il regista sbloccato
            $queryC = "select Sblocca
                       from Registi
                       where Nome = ?";

            $statementC = mysqli_prepare($connessione, $queryC);
            if (!$statementC){
                die(mysqli_connect_error());
            }

            mysqli_stmt_bind_param($statementC, "s", $_SESSION["registaCorrezione"]);
            if (!mysqli_stmt_execute($statementC)){
                die(mysqli_connect_error());
            }

            mysqli_stmt_bind_result($statementC, $regB);
            mysqli_stmt_store_result($statementC);
            while (mysqli_stmt_fetch($statementC)){
                $registaBloccato = $regB;
                if ($registaBloccato !== null){
                    // esiste un regista che ho appena sbloccato
                    $queryIns = "insert into TestUtente(Utente, Regista, DataSuperamento, Errori, TestSuperati)
                                 values (?, ?, null, null, 0)";

                    $statementIns = mysqli_prepare($connessione, $queryIns);
                    if (!$statementIns){
                        die(mysqli_connect_error());
                    }

                    mysqli_stmt_bind_param($statementIns, "ss", $_SESSION["utente"], $registaBloccato);
                    if (!mysqli_stmt_execute($statementIns)){
                        die(mysqli_connect_error());
                    }
                }
            }
            
        }
    }
    
?>

    <body>
    <div class='containerCorrezione'>
    <nav>
        <ul>
            <li> <a class="pagPrin" href="index.php">Pagina Principale</a> </li>
            <li> <a class="ridireziona" href="testSbloccati.php">Test Disponibili</a></li>
            <li> <a class="ridireziona" href="paginaQuizRegista.php?regista=<?php echo $_SESSION["registaCorrezione"]; ?>">Torna ai test di <?php echo $_SESSION["registaCorrezione"]; ?></a></li>
            <li> <a class="logout" href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="paragrafi">
    <?php
        $punteggio = $totDomande - $nErrori;
        $percentCorrette = $punteggio / $totDomande;
        $statoTest = ($percentCorrette >= 0.6) ? "<span style='color: green;'><strong>superato</strong></span>" : "<span style='color: red;'><strong>non superato</strong></span>";
        echo "<p> Hai totalizzato $punteggio punti su $totDomande: test $statoTest</p>";
    ?>


        <p>Di seguito le domande con in verde la risposta corretta e in rosso quella data, se sbagliata. Nel caso di domande testuali o di collegamento, trovi in blu la risposta corretta.</p>
    </div>
<?php
    // ristampa il test evidenziando le risposte corrette
    $connessione = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);
    if(mysqli_connect_errno())
        die(mysqli_connect_error());

    $query = "select Domanda, Tipo, NumeroDomanda, RispCorretta
              from TestRegisti
              where Regista = ? and NumeroTest = ?";

    $statement = mysqli_prepare($connessione, $query);
    mysqli_stmt_bind_param($statement, "si", $_SESSION["registaCorrezione"], $_SESSION["numeroQuizCorrezione"]);

    mysqli_stmt_execute($statement);

    // salvo i risultati nel buffer cosi non contrasta con il successivo prepared statement nel caso di tipo 'opzioni'
    mysqli_stmt_store_result($statement);
    mysqli_stmt_bind_result($statement, $Domanda, $Tipo, $NumeroDomanda, $RispostaCorretta);
    echo "<div class='formQuiz'>";
    while (mysqli_stmt_fetch($statement)){
        switch ($Tipo) {
            case "opzioni":
                $query1 = "select Risposta
                           from RisposteTest
                           where Regista = ? and NumTest = ? and NumDomanda = ?";
                $statement1 = mysqli_prepare($connessione, $query1);
                if (!$statement1){
                    die(mysqli_connect_error());
                }
                mysqli_stmt_bind_param($statement1, "sii", $_SESSION["registaCorrezione"], $_SESSION["numeroQuizCorrezione"], $NumeroDomanda);

                if (!mysqli_stmt_execute($statement1)){
                    die(mysqli_connect_error());
                };
                mysqli_stmt_bind_result($statement1, $rispCandidata);
                
                echo '  <div class ="domanda Opzioni">
                        <fieldset>
                        <legend><strong>' . $NumeroDomanda . '.</strong> ' . $Domanda . '</legend>
                         ';

                $contatoreRisposte = 'A';
                while (mysqli_stmt_fetch($statement1)){
                    echo '
                        <input disabled type="radio" id="risposta' . $contatoreRisposte . '_' . $NumeroDomanda . '" name="domanda_' . $NumeroDomanda . '" value="' . $rispCandidata . '"/>';
                        echo '<label';
                        if ($rispCandidata === $RispostaCorretta) {
                            echo ' class="superato"';
                        } else if ($rispCandidata === $_POST["domanda_" . $NumeroDomanda]) {
                            echo ' class="nonSuperato"';
                        }
                        echo ' for="risposta' . $contatoreRisposte . '_' . $NumeroDomanda . '">' . $rispCandidata . '</label>';
                        
                    $contatoreRisposte++;
                }
                echo "   </fieldset>
                        </div>";
                break ;

            case "testo":
                $contatoreRisposte = 'A';
                echo '
                    <div class="domanda Testo">
                    <label for="risposta' . $contatoreRisposte . '_' . $NumeroDomanda . '"><strong>' . $NumeroDomanda . '.</strong> ' . $Domanda . '</label><br>
                            <input disabled type="text" id="risposta' . $contatoreRisposte . '_' . $NumeroDomanda . '" name="domanda_' .$NumeroDomanda . '" value="' . $RispostaCorretta .'"';
                if ($RispostaCorretta === $_POST["domanda_" . $NumeroDomanda]){
                    echo 'class="superato"';
                    echo '/>';
                }else{
                    echo 'class="sbagliato"';
                    echo '/>';
                    echo "<p class='nonSuperato'>La risposta data era: ". $_POST["domanda_" . $NumeroDomanda];
                }      
                            
                echo '          
                        </div>';
                break;
            
            case "collegamento":
                echo ' <div class="domanda Collegamento">';
                echo '<p><strong>' . $NumeroDomanda . '</strong>. Collega i titoli dei film ai fotogrammi tratti dal film stesso</p>';
                echo '<script src="../javascript/correzioneCanvas.js"></script>'; // definizione della variabile che comporta la stampa del canvas per la correzione
                
                // riscrivo le immagini col loro path relativo al file javascript
                $elementi = explode("-", $RispostaCorretta);
                foreach ($elementi as &$elemento) {
                    $elemento = "../img/testCol/" . $elemento;
                }
                $RispostaCorretta = implode("-", $elementi);

                echo "<script> window.RispostaCollegamento = " . json_encode($RispostaCorretta) . "; window.RisposteDate = " . json_encode($_SESSION["datiPerCollegamento"]) . ";</script>";
                echo '<script src="../javascript/testSuQuizCollegamento.js"></script>';
                
                echo "<canvas id='canvas' width='1100' height='600'></canvas></div>";
                break;
        }   

    }
    echo "</div>"

?>
        
        </div>
    </body>

</html>
