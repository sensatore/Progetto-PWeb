-- Progettazione Web 
DROP DATABASE if exists pweb_progetto; 
CREATE DATABASE pweb_progetto; 
USE pweb_progetto; 
-- MySQL dump 10.13  Distrib 5.7.28, for Win64 (x86_64)
--
-- Host: localhost    Database: pweb_progetto
-- ------------------------------------------------------
-- Server version	5.7.28

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `registi`
--

DROP TABLE IF EXISTS `registi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registi` (
  `Nome` varchar(100) NOT NULL,
  `BloccatoDa` varchar(100) DEFAULT NULL,
  `Sblocca` varchar(100) DEFAULT NULL,
  `BreveIntroduzione` text NOT NULL,
  `Immagine` varchar(100) NOT NULL,
  PRIMARY KEY (`Nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registi`
--

LOCK TABLES `registi` WRITE;
/*!40000 ALTER TABLE `registi` DISABLE KEYS */;
INSERT INTO `registi` VALUES ('Kubrick','Nolan',NULL,'Kubrick è stato senza dubbio uno dei grandi maestri della storia del cinema, firmando capolavori in molteplici generi. Christopher Nolan stesso lo considera uno dei suoi maestri, e il film di Kubrick <em>2001: Odissea nello Spazio</em> è non a caso il suo preferito.\r\n<br>\r\nNasce a New York nel 1926 e fin da giovane si appassione alla dapprima alla fotografia, poi alle immagini in movimento e comincia a lavorare dietro la macchina da presa. Da lì in poi tutti riconosceranno il suo grande talento.\r\n<br>\r\nE\' un regista che dà piena importanza ad ogni aspetto che concorre nella realizzazione di una scena, e soprattutto alla musica, prevalentemente classica, come in 2001 ed Arancia Meccanica.\r\n<br>\r\nI suoi film vanno dalla guerra alla fantascienza, dall\'horror al film in costume: sono tutti attraversati da un profondo studio della natura umana, della lacerazione proveniente dalla nevrosi esistenziale e del tempo, e della dolorosa scelta tra il bene. Mostra soprattutto le paure più profonde dell\'essere umano, riguardo a sè e al mondo, e non si risparmia di scendere a patti con violenza e sessualità per dare una rappresentazione a 360 gradi dell\'uomo.\r\n<br>\r\nAlcuni dei suoi più celebri film sono 2001: Odissea nello spazio, Arancia meccanica e The Shining ','kubrick.jpg'),('Nolan',NULL,'Kubrick','Christopher Nolan nasce nel 1970 a Londra ed ha raggiunto nel corso del tempo un grande successo di critica e pubblico. I suoi film hanno incassato cumulativamente piu\' di 6 miliardi di dollari in tutto il mondo, ed e\' diventato una figura autoriale di spicco nel panorama del cinema contemporaneo.\n<br>\nNei suoi film, affronta temi epistemologici e metafisici ed esplora diversi aspetti della moralità umana, la costruzione del tempo e la natura malleabile della memoria e dell\'identità personale. \n<br>\nPredilige una fotografia di grandi formati e l\'utilizzo massiccio di effetti pratici piuttosto che speciali.\nI suoi film principali sono Memento, la trilogia del Cavaliere Oscuro, Inception e Oppenheimer.','nolan.jpg');
/*!40000 ALTER TABLE `registi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rispostetest`
--

DROP TABLE IF EXISTS `rispostetest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rispostetest` (
  `Regista` varchar(100) NOT NULL,
  `NumTest` int(11) NOT NULL,
  `NumDomanda` int(11) NOT NULL,
  `Risposta` varchar(100) NOT NULL,
  PRIMARY KEY (`Regista`,`NumTest`,`NumDomanda`,`Risposta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rispostetest`
--

LOCK TABLES `rispostetest` WRITE;
/*!40000 ALTER TABLE `rispostetest` DISABLE KEYS */;
INSERT INTO `rispostetest` VALUES ('Kubrick',0,2,'Londra'),('Kubrick',0,2,'New York'),('Kubrick',0,3,'barrylindon.jpg-eyeswideshut.jpg-spaceodyssey.jpg'),('Kubrick',1,1,'Eyes Wide Shut'),('Kubrick',1,1,'The Shining'),('Kubrick',1,3,'Barry Lindon'),('Kubrick',1,3,'Killer\'s Kiss'),('Kubrick',1,3,'Orizzonti di Gloria'),('Kubrick',2,1,'2001: Odissea nello Spazio'),('Kubrick',2,1,'Barry Lyndon'),('Kubrick',2,2,'2001: Odissea nello Spazio'),('Kubrick',2,2,'Barry Lindon'),('Nolan',0,1,'Il tempo'),('Nolan',0,1,'L\'amore'),('Nolan',0,3,'inception.jpg-interstellar.jpg-memento.jpg'),('Nolan',1,1,'Batman Begins'),('Nolan',1,1,'Following'),('Nolan',1,3,'Hans Zimmer'),('Nolan',1,3,'John Williams'),('Nolan',1,3,'Ludwig Göransson'),('Nolan',2,1,'Memento'),('Nolan',2,1,'Oppenheimer'),('Nolan',2,1,'Tenet'),('Nolan',2,3,'Christian Bale'),('Nolan',2,3,'Matthew McConaughey');
/*!40000 ALTER TABLE `rispostetest` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testregisti`
--

DROP TABLE IF EXISTS `testregisti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testregisti` (
  `Regista` varchar(100) NOT NULL,
  `NumeroTest` int(11) NOT NULL,
  `NumeroDomanda` int(11) NOT NULL,
  `Domanda` varchar(1000) NOT NULL,
  `Tipo` varchar(100) NOT NULL,
  `RispCorretta` varchar(100) NOT NULL,
  PRIMARY KEY (`Regista`,`NumeroTest`,`NumeroDomanda`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testregisti`
--

LOCK TABLES `testregisti` WRITE;
/*!40000 ALTER TABLE `testregisti` DISABLE KEYS */;
INSERT INTO `testregisti` VALUES ('Kubrick',0,1,'In che anno è uscito Arancia Meccanica?','testo','1971'),('Kubrick',0,2,'Dove è nato Kubrick?','opzioni','New York'),('Kubrick',0,3,'Barry Lindon-Eyes Wide Shut-2001:Odissea nello Spazio','collegamento','barrylindon.jpg-eyeswideshut.jpg-spaceodyssey.jpg'),('Kubrick',1,1,'Qual è stato l\'ultimo film diretto da Kubrick?','opzioni','Eyes Wide Shut'),('Kubrick',1,2,'Quale film di Kubrick è basato su un famoso libro di Stephen King ambientato in un hotel?','testo','the shining'),('Kubrick',1,3,'Quale film di guerra degli anni \'50 da lui diretto gli ha garantito un grande successo di critica?','opzioni','Orizzonti di Gloria'),('Kubrick',2,1,'Quale di questi è un film storico in costume?','opzioni','Barry Lyndon'),('Kubrick',2,2,'Per quale film Kubrick ha vinto il suo unico premio Oscar?','opzioni','2001: Odissea nello Spazio'),('Kubrick',2,3,'In che anno è morto Kubrick?','testo','1999'),('Nolan',0,1,'Quale di questi è un tema ricorrente nella filmografia di Nolan?','opzioni','Il tempo'),('Nolan',0,2,'Dove è nato Nolan?','testo','londra'),('Nolan',0,3,'Memento-Inception-Interstellar','collegamento','memento.jpg-inception.jpg-interstellar.jpg'),('Nolan',1,1,'Qual e\' stato il primo film diretto da Christopher Nolan?','opzioni','Following'),('Nolan',1,2,'In che anno e\' uscito Dunkirk?','testo','2017'),('Nolan',1,3,'Chi ha composto la colonna sonora di Interstellar?','opzioni','Hans Zimmer'),('Nolan',2,1,'Qual è stato l\'ultimo film diretto da Nolan?','opzioni','Oppenheimer'),('Nolan',2,2,'Per quale film Nolan ha ricevuto la prima candidatura agli Oscar?','testo','memento'),('Nolan',2,3,'Chi ha interpretato Batman nella trilogia del Cavaliere Oscuro?','opzioni','Christian Bale');
/*!40000 ALTER TABLE `testregisti` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testutente`
--

DROP TABLE IF EXISTS `testutente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testutente` (
  `Utente` varchar(20) NOT NULL,
  `Regista` varchar(100) NOT NULL,
  `DataSuperamento` date DEFAULT NULL,
  `Errori` int(11) DEFAULT NULL,
  `TestSuperati` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Utente`,`Regista`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testutente`
--

LOCK TABLES `testutente` WRITE;
/*!40000 ALTER TABLE `testutente` DISABLE KEYS */;
INSERT INTO `testutente` VALUES ('utente1','Nolan',NULL,NULL,0);
/*!40000 ALTER TABLE `testutente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `utenti`
--

DROP TABLE IF EXISTS `utenti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `utenti` (
  `Username` varchar(20) NOT NULL,
  `Password` varchar(255) NOT NULL,
  PRIMARY KEY (`Username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utenti`
--

LOCK TABLES `utenti` WRITE;
/*!40000 ALTER TABLE `utenti` DISABLE KEYS */;
INSERT INTO `utenti` VALUES ('utente1','$2y$10$bLI9Xozx8.d34uYb9RR50ejDOvzsyvbH1rcf7H/FrtbDyGyEE.uQi');
/*!40000 ALTER TABLE `utenti` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-02-13 16:11:25
