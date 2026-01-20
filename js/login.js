document.addEventListener("DOMContentLoaded", function() {
    
    const form = document.getElementById('loginForm');

    if (!form) return;
    
    // Funzione per gestire lo stato della risposta
    function checkStatus(response) {
        if (!response.ok) {
            throw Error("Errore di rete: " + response.status);
        }
        return response;
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Blocca sempre il submit standard

        const msgDiv = document.getElementById('messaggioAjax');
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();
        
        let errori = false;
        let msgErrore = "";

        // Validazione
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

        // Chiamata fetch 
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

                // Aspettiamo un secondo e andiamo alla home
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