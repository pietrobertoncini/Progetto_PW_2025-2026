document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('registerForm');

    function checkStatus(response) {
        if (!response.ok) { throw Error(response.statusText); }
        return response;
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const msgDiv = document.getElementById('messaggioAjax');
        
        // Reset errori visuali precedenti
        document.querySelectorAll('.text-danger-custom').forEach(el => el.textContent = '');
        msgDiv.classList.add('d-none');

        // Recupero valori
        const nome = document.getElementById('nome').value.trim();
        const cognome = document.getElementById('cognome').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        
        let errori = false;

        // Validazione
        if (nome.length < 2) {
            document.getElementById('errNome').textContent = "Il nome deve avere almeno 2 caratteri.";
            errori = true;
        }

        if (cognome.length < 2) {
            document.getElementById('errCognome').textContent = "Il cognome deve avere almeno 2 caratteri.";
            errori = true;
        }

        // Regex semplice per email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            document.getElementById('errEmail').textContent = "Inserisci un'email valida.";
            errori = true;
        }

        if (password.length < 4) {
            document.getElementById('errPassword').textContent = "La password deve essere di almeno 4 caratteri.";
            errori = true;
        }

        // Se ci sono errori, fermiamo tutto
        if (errori) {
            return; 
        }

        const formData = new FormData(form);
        const apiUrl = '../backend/api_register.php';

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

                // Aspettiamo un secondo e mezzo e andiamo alla home
                setTimeout(() => {
                    window.location.href = '../index.php';
                }, 1500);
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
            msgDiv.textContent = "Errore tecnico nella registrazione.";
            msgDiv.classList.remove('d-none');
        });
    });
});