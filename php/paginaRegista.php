<!DOCTYPE html>
<html lang="it">
    <head>
        <title>Introduzione al Regista - DirectorQuiz</title>
        <meta name="author" content="Luca Di Gasparro"> 
        <meta charset="UTF-8">
        <link rel="stylesheet" href="../css/style.css">
    </head>

<body>
    <div class="containerIntroduzione">
    <nav>
        <ul>
            <li> <a class="pagPrin" href="index.php">Pagina Principale</a> </li>
            <li> <a class="ridireziona" href="testSbloccati.php">Test Disponibili</a></li>
    
    <?php
        require "controlloLoginEffettuato.php";
        if (!isset($_GET["regista"]) || empty($_GET["regista"])) {
            echo "<script>alert('Nome del regista non specificato.'); window.history.back();</script>";
            exit();
        }
        
        require "accessodb.php";
        $connessione = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);
        if (mysqli_connect_errno())
            die(mysqli_connect_error());
        
        $query = "select BreveIntroduzione, Immagine
                from Registi
                where Nome = ?";

        $statement = mysqli_prepare($connessione, $query);
        if (!$statement) {
            die(mysqli_connect_error());
        }

        mysqli_stmt_bind_param($statement, "s", $_GET["regista"]);
        if (!mysqli_stmt_execute($statement)) {
            die(mysqli_connect_error());
        }

        // controllo se il resultset e' vuoto
        // vorrebbe dire che non esiste un regista con quel nome, quindi il nome e' invalido
        mysqli_stmt_store_result($statement);
        if (mysqli_stmt_num_rows($statement) === 0){
            echo "<script>alert('Nome del regista non valido. Ritorno al menù principale.'); window.location.href = 'index.php';</script>";
            exit();
        }
        
        echo '<li><a class="ridireziona" href="paginaQuizRegista.php?regista=' . $_GET['regista'] . '">Vai ai quiz del regista</a></li>';
        echo '<li> <a class="logout" href="logout.php">Logout</a></li> </ul>
                </nav>';

        mysqli_stmt_bind_result($statement, $introduzione, $nomeImmagine);
        while (mysqli_stmt_fetch($statement)){      
            echo "<img class='immagineRegista' src='../img/registi/" . $nomeImmagine . "' alt='" . $_GET['regista'] . "'>";
            echo "<div>";
            echo "<p>" . $introduzione . "</p>";
            echo "<br> Puoi vedere il test di questo regista facendo click su <em>Vai ai quiz del regista</em> di lato.";
            echo "</div>";
        }
        
        ?>
</div>
    


    
</body>
</html>