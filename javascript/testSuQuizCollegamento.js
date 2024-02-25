document.addEventListener("DOMContentLoaded", function() {
    let canvas = document.getElementById("canvas");
    

    if (canvas != null){
        // array per conservare film, immagini, immagini con linee(vedi dopo) e linee attualmente disegnate
        let ctx = canvas.getContext("2d");
        let films = [];
        let images = [];
        let imagesWithLine = [];
        let lines = [];

        
        // Funzione per fare una richiesta AJAX al file PHP e ottenere titoli ed source delle immagini da disegnare
        function fetchData() {
            fetch('../php/prendiDatiCollegamento.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Errore di connessione');
                    }
                    return response.json();
                })
                .then(data => {
                    const titles = data.titles;
                    const imageSources = data.images;

                    // Assegna i titoli dei film ai rispettivi array
                    titles.forEach((title, index) => {
                        films.push({
                            title: title,
                            x: 180 + index * 300,
                            y: 100,
                            width: 100,
                            height: 100
                        });
                    });

                    // Assegna le sorgenti delle immagini ai rispettivi array
                    imageSources.forEach((src, index) => {
                        images.push({
                            src: src,
                            x: 115 + index * 300,
                            y: 300,
                            width: 250,
                            height: 100
                        });
                    });

                    // disegno immagini e film
                    loadImagesAndDraw();
                    drawFilms();

                    // se sto facendo il test, abilito i listener sugli eventi di spostamento del mouse
                    if (!window.faiCanvasCorretto){
                        // anche se quando lo chiamo la prima volta essa non e' definita, undefined vale come falso, quindi l'espressione restituisce true
                        startLineTracking();
                    }else{
                        // altrimenti disegno le linee per corretto e sbagliato con i vari colori 
                        window.faiCanvasCorretto = false; // ristabilisco la condizione per il prossimo quiz (sara' di tracciamento)
                        let sequenzaImmaginiCorrette = window.RispostaCollegamento;
                        let risposteUtente = window.RisposteDate;

                        function drawLinesBetweenFilmsAndImages() {                    
                            let immaginiCorrette = sequenzaImmaginiCorrette.split("-");
                            let risposteDate = risposteUtente.split("-");
                        
                            // Itera su ogni film e disegna una linea che lo connette alla relativa immagine
                            // le soluzioni sono nell'ordine di film associati
                            films.forEach((film, index) => {
                                const src = immaginiCorrette[index];
                                const startX = film.x + ctx.measureText(film.title).width / 2;
                                const startY = film.y; // Inizio dalla parte inferiore del film
                        
                                // Trova l'immagine corrispondente nell'array images
                                const image = images.find(img => img.src === src);
                        
                                if (image) {
                                    // Trova le coordinate x e y dell'immagine
                                    const endX = image.x + image.width / 2;
                                    const endY = image.y; 
                                    
                                    if (immaginiCorrette[index] === risposteDate[index]){
                                        // linea corretta tracciata
                                        ctx.strokeStyle = "green";
                                        ctx.beginPath();
                                        ctx.moveTo(startX, startY);
                                        ctx.lineTo(endX, endY);
                                        ctx.stroke();
                                    }else{
                                        // linea sbagliata: traccio la prima blu per indicare quale sarebbe dovuta essere quella corretta
                                        ctx.strokeStyle = "blue";
                                        ctx.beginPath();
                                        ctx.moveTo(startX, startY);
                                        ctx.lineTo(endX, endY);
                                        ctx.stroke();

                                        // ora traccio in rosso quella data
                                        ctx.strokeStyle = "red";
                                        ctx.beginPath();
                                        ctx.moveTo(startX, startY);
                                        const imageErrata = images.find(img => img.src === risposteDate[index]);
                                        const endXErrata = imageErrata.x + imageErrata.width / 2;
                                        const endYErrata = imageErrata.y;
                                        ctx.beginPath();
                                        ctx.moveTo(startX, startY);
                                        ctx.lineTo(endXErrata, endYErrata);
                                        ctx.stroke();
                                    }
                                    
                                } else {
                                    console.error("Errore: immagine non trovata per il film:", film.title);
                                }
                            });
                        }

                        drawLinesBetweenFilmsAndImages();
                    }
                })
                .catch(error => {
                    console.error('Errore di fetch:', error);
                });
        }


        function startLineTracking() {
            // Avvia il tracciamento delle linee solo dopo che tutte le immagini sono state caricate
            // stabilisce un eventlistener
            // in una funzione in quanto lo posso avere soltanto se non sto stampando i risultati corretti
            canvas.addEventListener("mousedown", function(e) {
                let startPoint = getCursorPosition(canvas, e);
                let nearestFilm = findNearestFilm(startPoint);
                if (nearestFilm !== null && Math.abs(startPoint.x - nearestFilm.x) < ctx.measureText(nearestFilm.title).width && Math.abs(startPoint.y - nearestFilm.y) < 12) {
                    // faccio partire una linea dal centro dell'immagine per uniformita'
                    startPoint = { x: nearestFilm.x + ctx.measureText(nearestFilm.title).width / 2, y: nearestFilm.y}; 
                    let existingLineIndex = getLineIndexByStartPoint(startPoint);
                    if (existingLineIndex !== -1) {
                        // Rimuovi la linea precedente se esiste
                        let removedImage = removeLineAndReturnImage(existingLineIndex);
                        if (removedImage) {
                            // Rimuovi l'immagine associata dalla lista imagesWithLine
                            removeImageFromList(removedImage);
                        }
                        drawLines(); // Ridisegna il canvas per rimuovere la linea precedente
                    }
                    tempLine = { startPoint: startPoint, endPoint: startPoint };
                    canvas.addEventListener("mousemove", onMouseMove);
                } else{
                    tempLine = null;
                }
            });
        }

        // Funzione per disegnare i titoli dei film sul canvas
        function drawFilms() {
            films.forEach(film => {
                ctx.font = "12px Arial";
                let textWidth = ctx.measureText(film.title).width;
                let textHeight = 17;

                // Disegna il rettangolo di sfondo
                ctx.fillStyle = "lightblue"; // Imposta il colore di sfondo
                ctx.fillRect(film.x - 5, film.y - 12, textWidth + 10, textHeight);

                // Disegna il testo del titolo sopra il rettangolo
                ctx.fillStyle = "black"; // Imposta il colore del testo
                ctx.fillText(film.title, film.x, film.y);

                // Traccia il bordo del rettangolo
                ctx.strokeStyle = "blue"; // Imposta il colore del bordo
                ctx.strokeRect(film.x - 5, film.y - 12, textWidth + 10, textHeight);
            });
        }

        // Funzione per caricare e disegnare le immagini sul canvas
        function loadImagesAndDraw() {
            let imagesLoaded = 0;
            images.forEach(image => {
                let img = new Image();
                img.onload = function() {
                    // imposto una larghezza di 250 e un'altezza che rispetti l'aspect ratio originale
                    let aspectRatio = img.naturalWidth / img.naturalHeight;
                    let height = 250 / aspectRatio;
                    image.height = height;
                    imagesLoaded++;
                    if (imagesLoaded === images.length) {
                        // Tutte le immagini sono state caricate, disegnale sul canvas
                        drawImages();
                    }
                };
                img.src = image.src;
                image.img = img;
            });
        }

        // Funzione per disegnare le immagini sul canvas
        function drawImages() {
            images.forEach(image => {
                ctx.drawImage(image.img, image.x, image.y, 250, image.height);
            });
        }

        fetchData(); // eseguo il fetch
        
        // Variabile per memorizzare le linee temporanee
        let tempLine = null;
        if (!window.faiCanvasCorretto){
            // se devo abilitare le funzioni e non invece disegnare il canvas con le risposte corrette in fase di correzione

            // Funzione per gestire il movimento del mouse durante il tracciamento della linea temporanea
            function onMouseMove(e) {
                if (tempLine !== null) {
                    let endPoint = getCursorPosition(canvas, e);
                    tempLine.endPoint = endPoint;
                    drawLines();
                    drawTempLine();
                }
            }

            // Aggiungi event listener per il rilascio del mouse per completare il tracciamento della linea temporanea
            canvas.addEventListener("mouseup", function(e) {
                canvas.removeEventListener("mousemove", onMouseMove);
                if (tempLine !== null) {
                    if (!isLineValid(tempLine.startPoint, tempLine.endPoint)) {
                        // se non e' valida la ignoro e ridisegno
                        tempLine = null;
                        drawLines();
                    } else {
                        // se valida faccio in modo che termini al centro di una immagine per uniformita'
                        let startPoint = tempLine.startPoint;
                        let endPoint = tempLine.endPoint;
                        if (isPointOnFilm(startPoint) && isPointOnImage(endPoint)) {
                            let closestImage = findClosestImage(endPoint);
                            endPoint = {
                                x: closestImage.x + closestImage.width / 2,
                                y: closestImage.y + closestImage.height / 2
                            };
                            tempLine.endPoint = endPoint; 
                        }
                        let line = { startPoint: startPoint, endPoint: endPoint };
                        let index = getLineIndexByStartPoint(startPoint);
                        if (index !== -1) {
                            // se esiste gia' una linea che parte da quel film, cancello quella precedente
                            lines.splice(index, 1);
                        }
                        lines.push(line);
                        // Aggiungi l'immagine corrente alla lista delle immagini con una linea associata
                        imagesWithLine.push(findClosestImage(endPoint));
                        drawLines();
                    }
                }
            });

            // Funzione per disegnare tutte le linee, film ed immagini
            function drawLines() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                drawFilms();
                
                lines.forEach(line => {
                    ctx.beginPath();
                    ctx.moveTo(line.startPoint.x, line.startPoint.y);
                    ctx.lineTo(line.endPoint.x, line.endPoint.y);
                    ctx.strokeStyle = "black";
                    ctx.lineWidth = 2;
                    ctx.stroke();
                });
                drawImages();
            }

            // Funzione per disegnare una linea temporanea
            function drawTempLine() {
                if (tempLine !== null) {
                    ctx.beginPath();
                    ctx.moveTo(tempLine.startPoint.x, tempLine.startPoint.y);
                    ctx.lineTo(tempLine.endPoint.x, tempLine.endPoint.y);
                    ctx.strokeStyle = "black";
                    ctx.lineWidth = 2;
                    ctx.stroke();
                }
            }

            // Funzione per verificare se la linea è valida
            function isLineValid(startPoint, endPoint) {
                // Verifica se il punto di inizio è sul testo di un film e il punto di fine è su un'immagine
                if (isPointOnFilm(startPoint) && isPointOnImage(endPoint)) {
                    // Trova l'immagine più vicina al punto finale
                    let closestImage = findClosestImage(endPoint);
                    // Controlla se l'immagine di destinazione ha già una linea associata
                    return !imagesWithLine.includes(closestImage);
                }

                return false;
            }

            // Funzione per trovare l'immagine più vicina al punto specificato
            function findClosestImage(point) {
                let closestDistance = Infinity;
                let closestImage = null;
                images.forEach(image => {
                    let centerX = image.x + image.width / 2;
                    let centerY = image.y + image.height / 2;
                    let distance = Math.sqrt((point.x - centerX) ** 2 + (point.y - centerY) ** 2);
                    if (distance < closestDistance) {
                        closestDistance = distance;
                        closestImage = image;
                    }
                });
                return closestImage;
            }

            // Funzione per trovare il film più vicino al punto specificato
            function findNearestFilm(point) {
                let nearestFilm = null;
                let minDistance = Infinity;
                films.forEach(film => {
                    let filmCenterX = film.x + 20;
                    let distance = Math.abs(point.x - filmCenterX);
                    if (distance < minDistance) {
                        minDistance = distance;
                        nearestFilm = film;
                    }
                });
                return nearestFilm;
            }

            // Funzione per verificare se un punto è su un'immagine
            function isPointOnImage(point) {
                return images.some(image =>
                    point.x >= image.x && point.x <= image.x + image.width &&
                    point.y >= image.y && point.y <= image.y + image.height
                );
            }

            // Funzione per verificare se un punto è su un titolo di film
            function isPointOnFilm(point) {
                return films.some(film =>
                    point.x >= film.x && point.x <= film.x + ctx.measureText(film.title).width &&
                    point.y >= film.y - 12 && point.y <= film.y
                );
            }

            // Funzione per ottenere le coordinate del mouse sul canvas
            function getCursorPosition(canvas, e) {
                let rect = canvas.getBoundingClientRect();
                return {
                    x: e.clientX - rect.left,
                    y: e.clientY - rect.top
                };
            }

            // Funzione per ottenere l'indice di una linea dalla sua startPoint
            function getLineIndexByStartPoint(startPoint) {
                return lines.findIndex(line =>
                    line.startPoint.x === startPoint.x && line.startPoint.y === startPoint.y
                );
            }

            // Funzione per rimuovere un'immagine dalla lista imagesWithLine
            function removeImageFromList(image) {
                const index = imagesWithLine.indexOf(image);
                if (index !== -1) {
                    imagesWithLine.splice(index, 1);
                }
            }

            // Funzione per rimuovere una linea dall'array lines e restituire l'immagine associata
            function removeLineAndReturnImage(index) {
                let removedImage = null;
                if (index >= 0 && index < lines.length) {
                    let removedLine = lines.splice(index, 1)[0]; // [0] per accedere al primo ed unico elemento: splice restituisce comunque un array
                    removedImage = findClosestImage(removedLine.endPoint);
                    drawLines(); // Ridisegna tutto il canvas dopo aver rimosso la linea
                }
                return removedImage;
            }

            // Funzione per controllare il numero di linee tracciate
            function controllaNumeroLinee() {
                return (lines.length === films.length);
            }

            // logica necessaria per codificare i dati da inviare al server
            function prepareDataForServer() {
                // Creiamo un array per contenere i nomi delle immagini associate ai film
                let imageNames = [];
                // Iteriamo sui film
                films.forEach(film => {
                    // Troviamo la linea che parte dal film
                    let line = lines.find(line => {
                        return line.startPoint.x >= film.x && line.startPoint.x <= film.x + film.width;
                    });

                    // Se troviamo una linea associata al film, troviamo l'immagine collegata
                    if (line) {
                        let image = images.find(image => {
                            return line.endPoint.x >= image.x && line.endPoint.x <= image.x + image.width;
                        });

                        // Se troviamo l'immagine collegata, aggiungiamo solo il suo nome all'array
                        if (image) {
                            imageNames.push(image.src);
                        }
                    }
                });

                // Uniamo i nomi delle immagini in una stringa separata da " - "
                let stringToSend = imageNames.join('-');
                return stringToSend;
            }
        }
    } 



    // gestisco l'evento in cui premo il bottone per l'invio dei dati
    let bottone = document.getElementById("submitBtn");
    if (bottone != null){
        bottone.addEventListener("click", function(event) {
            if (canvas!=null) event.preventDefault(); // in questo caso devo implementare logica aggiuntiva per restituire le risposte
            let domandeOpzioni = document.querySelectorAll('.domandaOpzioni');
            let domandeTesto = document.querySelectorAll('.Testo');
            // controllo che l'utente abbia risposto a tutte le domanda ad opzioni
            for (let i = 0; i < domandeOpzioni.length; i++) {
                let domanda = domandeOpzioni[i];
                let risposteSelezionate = domanda.querySelectorAll('input[type="radio"]:checked');
                if (risposteSelezionate.length === 0) {
                    event.preventDefault(); // Impedisce l'invio del modulo
                    alert('Si prega di rispondere a tutte le domande a scelta multipla prima di consegnare il quiz.');
                    return;
                }
            }

            // controllo che l'utente abbia risposto a tutte le domande di input testuale
            for (let j = 0; j < domandeTesto.length; j++) {
                let domanda = domandeTesto[j];
                let risposta = domanda.querySelector('input[type="text"]');
                if (risposta.value.trim() === '') {
                    event.preventDefault(); // Impedisce l'invio del modulo
                    alert('Si prega di rispondere a tutte le domande a risposta aperta prima di consegnare il quiz.');
                    return;
                }
            }

            if (canvas != null){
                const isValid = controllaNumeroLinee(); 
                if (isValid) {
                    // Prepara i dati da inviare
                    let dataToSend = prepareDataForServer();
                    console.log("dati da mandare: ", dataToSend);
                    fetch('../php/gestisciCollegamento.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(dataToSend)
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Errore di rete');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Dati inviati con successo:', data);
                        console.log('Dati inviati in formato JSON:', JSON.stringify(dataToSend, null, 2));
                        document.getElementById("quizForm").submit();
                    })
                    .catch(error => {
                        console.error('Errore durante l\'invio dei dati:', error);
                    });

                }else {
                    event.preventDefault();
                    alert('Devi tracciare tutte le righe nella domanda di collegamento.');
                    return;
                }
            }
        });
    }

});