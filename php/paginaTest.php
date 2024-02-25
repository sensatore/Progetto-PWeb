<!DOCTYPE html>
<html lang="it">
<head>
    <title>Test - DirectorQuiz</title>
    <script src="../javascript/testSuQuizCollegamento.js"></script>
    <meta name="author" content="Luca Di Gasparro"> 
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/style.css">
</head> 

<body>
<p id='introForm'>Rispondi alle seguenti domande per completare il test:</p>
<ul>
    <li>Se ci sono delle opzioni, fai click su una di esse per selezionarle</li>
    <li>Se c'è uno spazio bianco, usalo per scrivere da tastiera la risposta</li>
    <li>Se ci sono immagini e titoli, traccia delle linee dai titoli all'immagine del film corrispondente</li>
</ul>
<p>Al termine clicca sul bottone per consegnare le tue risposte</p>
<br>

    <div class='formQuiz'>
    
    <form id="quizForm" method="POST" action="processaQuizCompleto.php">

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

        // eseguo controlli sui due parametri get per assicurarmi che si riferiscano ad un quiz accessibile per l'utente
        if (!isset($_GET["regista"]) || empty($_GET["regista"]) || !isset($_GET['numeroQuiz']) || $_GET['numeroQuiz'] === '' || !is_numeric($_GET['numeroQuiz'])) {
            echo "<script>alert('Nome del regista e/o numero di quiz non specificati o non validi.'); window.location.href = 'testSbloccati.php';</script>";
            exit();
        }

        $queryC = "select TestSuperati
                   from TestUtente
                   where DataSuperamento is null and Utente = ? and Regista = ?";

        $statementC = mysqli_prepare($connessione, $queryC);
        if (!$statementC){
            die(mysqli_connect_error());
        }
        
        mysqli_stmt_bind_param($statementC, "si", $_SESSION["utente"], $_GET["regista"]);

        if (!mysqli_stmt_execute($statementC)){
            die(mysqli_connect_error());
        }
        mysqli_stmt_store_result($statementC);
        if (mysqli_stmt_num_rows($statementC) === 0){
            // non esiste il record nella tabella
            // il regista non e' valido, non e' stato sbloccato o il test e' stato gia' superato
            echo "<script>alert('Regista e/o numero di quiz errati.'); window.location.href = 'testSbloccati.php';</script>";
            exit();
        }

        // recupero quanti quiz del regista sono stati gia' superati
        mysqli_stmt_bind_result($statementC, $dataC);
        while (mysqli_stmt_fetch($statementC)){
            $testSuperati = $dataC;
        }

        // ora recupero il quiz di numero piu' elevato nel test del regista
        $queryC1 = "select max(NumeroTest)
                    from TestRegisti
                    where Regista = ?";
        $statementC1 = mysqli_prepare($connessione, $queryC1);
        if (!$statementC1){
            die(mysqli_connect_error());
        }

        mysqli_stmt_bind_param($statementC1, "s", $_GET["regista"]);
        if (!mysqli_stmt_execute($statementC1)) {
            die(mysqli_connect_error());
        }

        mysqli_stmt_bind_result($statementC1, $mTest);
        while (mysqli_stmt_fetch($statementC1)){
            $maxTest = $mTest;
        }
        $_GET["numeroQuiz"] = intval($_GET["numeroQuiz"]);
        // il max sara' sicuramente un valore intero diverso da null perche' nel controllo precedente ho gia' escluso il caso di registi non validi
        if ($_GET["numeroQuiz"] < 0 or ($_GET["numeroQuiz"] > 0 and $_GET["numeroQuiz"] > $maxTest) or ($_GET["numeroQuiz"] > 0 and $_GET["numeroQuiz"] != ($testSuperati+1))
            or ($_GET["numeroQuiz"] === 0 and $testSuperati !== $maxTest)
            ){
                echo "<script>alert(Regista e/o numero di quiz errati.'); window.location.href = 'paginaQuizRegista.php?regista=" . $_GET["regista"] . "';</script>";
            exit();
        }
        // ho verificato entrambi i parametri
        // posso riprendere con le istruzioni corrette

        $_SESSION["registaCorrezione"] = $_GET["regista"];
        $_SESSION["numeroQuizCorrezione"] = $_GET["numeroQuiz"];
        // prendo tutti i dati sul test in considerazione
        $query = "select NumeroDomanda, Domanda, Tipo, RispCorretta
                from TestRegisti
                where Regista = ? and NumeroTest = ?";

        $statement = mysqli_prepare($connessione, $query);
        if (!$statement){
            die(mysqli_connect_error());
        }

        mysqli_stmt_bind_param($statement, "si", $_GET["regista"], $_GET["numeroQuiz"]);

        if (!mysqli_stmt_execute($statement)){
            die(mysqli_connect_error());
        }

        // salvo i risultati nel buffer cosi non contrasta con il successivo prepared statement nel caso di tipo 'opzioni'
        mysqli_stmt_store_result($statement);
        
        mysqli_stmt_bind_result($statement, $NumeroDomanda, $Domanda, $Tipo, $RispostaCorretta);

        while (mysqli_stmt_fetch($statement)){
            switch ($Tipo) {
                case "opzioni":
                    // domanda a scelta multipla
                    // prelevo le risposte valide
                    $query1 = "select Risposta
                            from RisposteTest
                            where Regista = ? and NumTest = ? and NumDomanda = ?";
                    $statement1 = mysqli_prepare($connessione, $query1);
                    if (!$statement1){
                        die(mysqli_connect_error());
                    }

                    mysqli_stmt_bind_param($statement1, "sii", $_GET["regista"], $_GET["numeroQuiz"], $NumeroDomanda);

                    if (!mysqli_stmt_execute($statement1)){
                        die(mysqli_connect_error());
                    }
                    mysqli_stmt_bind_result($statement1, $rispCandidata);
                    
                    echo '
                    <div class="domanda Opzioni">
                    <fieldset class="domandaOpzioni">
                        <legend><strong>' . $NumeroDomanda . '.</strong> ' . $Domanda . '</legend>';
                

                    $contatoreRisposte = 'A';
                    while (mysqli_stmt_fetch($statement1)){
                        echo '<input type="radio" id="risposta' . $contatoreRisposte . '_' . $NumeroDomanda . '" name="domanda_' . $NumeroDomanda . '" value="' . $rispCandidata . '" />';
                        echo '<label for="risposta' . $contatoreRisposte . '_' . $NumeroDomanda . '">' . $rispCandidata . '</label>';
                        
                        $contatoreRisposte++;
                    }
                    echo "    
                        </fieldset>
                        </div>";
                    break ;

                case "testo":
                    // domanda di input testuale
                    // non ci sono risposte candidate da prelevare
                    echo '<div class="domanda Testo">' .
                    '<label for="risposta' . $NumeroDomanda . '"><strong>' . $NumeroDomanda . '.</strong> ' . $Domanda . '</label><br>' .
                    '<input type="text" id="risposta' . $NumeroDomanda . '" name="domanda_' . $NumeroDomanda . '" />' .
                 '</div>';
            
                    break;
                
                case "collegamento":
                    // domanda di collegamento titoli-immagini 
                    // includo uno script javascrit per stampare il tutto
                    // i dati necessari verrano prelevati mediante richieste ajax con fetch al server

                    // passo il numero di domanda richiesto dal file prendiDatiCollegamento richiamato
                    // nel file javascript per la richiesta ajaxS 
                    $_SESSION["NumeroDomanda"] = $NumeroDomanda; 
                    
                    echo ' <div class="domanda collegamento">';
                    echo '<p><strong>' . $NumeroDomanda . '</strong>. Collega i titoli dei film ai fotogrammi tratti dal film stesso</p>';
                    echo "<canvas id='canvas' width='1000' height='700'></canvas></div>";
                    break;
            }   

        }
        
    ?>
    <button id="submitBtn">Consegna le risposte</button>
    </form>
    </div>
</body>

</html>


