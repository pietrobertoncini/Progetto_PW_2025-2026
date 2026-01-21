// Gestisce la validazione dei dati di accesso e la comunicazione asincrona con il portale
document.addEventListener("DOMContentLoaded", function() {
    
    const form = document.getElementById('loginForm');

    if (!form) return;
    
    // Controlla l'integrità della risposta del server durante la fase di autenticazione
    function checkStatus(response) {
        if (!response.ok) {
            throw Error("Errore di rete: " + response.status);
        }
        return response;
    }

    form.addEventListener('submit', function(e) {
        // Impedisce il rinvio standard del modulo per gestire l'operazione tramite script
        e.preventDefault();

        const msgDiv = document.getElementById('messaggioAjax');
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();
        
        let errori = false;
        let msgErrore = "";

        // Verifica che tutti i campi necessari siano stati compilati correttamente
        if (email === "") {
            msgErrore += "Inserisci l'email.<br>";
            errori = true;
        }
        if (password === "") {
            msgErrore += "Inserisci la password.<br>";
            errori = true;
        }

        if (errori) {
            msgDiv.className = 'alert alert-danger';
            msgDiv.innerHTML = msgErrore;
            msgDiv.classList.remove('d-none');
            return;
        }

        // Se qua -> dati validi -> procediamo con AJAX
        msgDiv.classList.add('d-none');
        
        const apiUrl = '../backend/api_login.php'; 

        const formData = new FormData(form);

        // Invia i dati al sistema e gestisce il reindirizzamento in caso di successo
        fetch(apiUrl, {
            method: 'POST',
            body: formData
        })
        .then(checkStatus) // Controlla se il server risponde
        .then(response => response.json()) // Decodifica il JSON
        .then(data => {
            // Gestione della risposta logica
            if (data.status === 'ok') {
                // Successo: mostriamo verde e reindirizziamo
                msgDiv.className = 'alert alert-success';
                msgDiv.textContent = data.msg;
                msgDiv.classList.remove('d-none');

                // Effettua il passaggio alla pagina principale dopo un breve intervallo di conferma
                setTimeout(() => {
                    window.location.href = '../index.php';
                }, 1000);
            } else {
                // Errore: mostriamo rosso e restiamo qui
                msgDiv.className = 'alert alert-danger';
                msgDiv.textContent = data.msg;
                msgDiv.classList.remove('d-none');
            }
        })
        .catch(error => {
            console.error(error);
            msgDiv.className = 'alert alert-danger';
            msgDiv.textContent = "Errore di comunicazione col server.";
            msgDiv.classList.remove('d-none');
        });
    });
});