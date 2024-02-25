<!DOCTYPE html>
<html lang="it">
    <head>
        <title>Accesso a DirectorQuiz</title>
        <meta name="author" content="Luca Di Gasparro"> 
        <meta charset="UTF-8">
        <link rel="stylesheet" href="../css/style.css">
    </head>

    <body>
        <p id="accedi">Esegui l'accesso per sbloccare tutte le funzionalità di DirectorQuiz, oppure se ancora non hai fatto un account Registrati!</p>
        <form action="checkCredenziali.php" method="POST">
            <fieldset>
                <label for="username">Username: </label> <input type="text" name="username" id="username">
                <label for="password">Password: </label> <input type="password" name="password" id="password">          
            </fieldset>
        <div class="insiemeBottoni">
            <input type="submit" name="accedi" value="Accedi">
            <input type="submit" name="registrati" value="Registrati">
        </div>
        </form>
    </body>

</html>
