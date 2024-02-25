<?php

    if ( empty($_POST["username"]) || empty($_POST["password"]) ){
        echo ("<script> alert('Errore: username o password non possono essere vuoti!');
        window.history.back(); 
                </script>");
        exit();
    }
    // sono stati inseriti correttamente username e passoword

    require "accessodb.php";
    
    $connessione = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);
    if (mysqli_connect_errno())
        // la connessione al database ha fallito
        die(mysqli_connect_error());

    
    if (isset($_POST["registrati"])){
        // l'utente si vuole registrare

        $regexCheck = '/^\S{4,20}$/';
        // controllo che username e password siano lunghi almeno 4 e meno di 20, con soli caratteri non spazi bianchi
        if (!preg_match($regexCheck, $_POST["password"]) || !preg_match($regexCheck, $_POST["username"])){
            echo ("<script>alert('Errore: i campi devono essere composti tra 4 e 20 caratteri, senza spazi bianchi!'); window.history.back();
            </script>");
            exit();
        }

        $password =  password_hash($_POST["password"], PASSWORD_BCRYPT);
        $query = "insert into Utenti (Username, Password) values (?,?)";

        $statement = mysqli_prepare($connessione, $query);
        if (!$statement){
            die(mysqli_connect_error());
        }
        mysqli_stmt_bind_param($statement, "ss", $_POST["username"], $password);

        if (!mysqli_stmt_execute($statement)){
            // errore su inserimento: username gia' presente
            echo ("<script>alert('Errore: l\'utente è già registrato'); window.history.back();
            </script>");
            exit();
        } 
        
        // inizializzo TestUtente

        // trovo tutti i registi non bloccati da nessuno
        $queryC = "select Nome
                   from Registi
                   where BloccatoDa is null";

        $statementC = mysqli_prepare($connessione, $queryC);
        if (!$statementC){
            die(mysqli_connect_error());
        }
        
        if (!mysqli_stmt_execute($statementC)){
            die(mysqli_connect_error());
        }   

        mysqli_stmt_store_result($statementC);
        $queryC1 = "insert into TestUtente(Utente, Regista, DataSuperamento, Errori, TestSuperati)
                    values (?, ?, null, null, 0)";
        $statementC1 = mysqli_prepare($connessione, $queryC1);
        if (!$statementC1){
            die(mysqli_connect_error());
        }
        
        // li inserisco come nuovi record
        mysqli_stmt_bind_result($statementC, $registaDisponibile);
        while (mysqli_stmt_fetch($statementC)){
            mysqli_stmt_bind_param($statementC1, "ss", $_POST["username"], $registaDisponibile);
            mysqli_stmt_execute($statementC1);
        }
        echo ("<script>alert('Utente registrato con successo'); window.history.back(); </script>");
    } else{
    
        // l'utente vuole fare un login
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $query = "select Password from utenti where Username = ?";
        $statement = mysqli_prepare($connessione, $query);
        if (!$statement){
            die(mysqli_connect_error());
        }
        mysqli_stmt_bind_param($statement, "s", $_POST["username"]);
        if (!mysqli_stmt_execute($statement)){
            die(mysqli_connect_error());
        }
        
        mysqli_stmt_bind_result($statement, $Password);
        
        while (mysqli_stmt_fetch($statement)){
            
            if (password_verify($_POST["password"], $Password)) {
                // login effettuato con successo
                $_SESSION["utente"] = $_POST["username"];
                header("location: index.php");
            }else {
                echo ("<script>alert('Password errata'); 
                            window.history.back();
            </script>");
            exit();
            }
            
        }
        // se sono qui vuol dire che non esiste un utente con lo username inserito
        echo ("<script>alert('Utente non registrato'); 
                            window.history.back();
            </script>");
        exit();
    }

?>
