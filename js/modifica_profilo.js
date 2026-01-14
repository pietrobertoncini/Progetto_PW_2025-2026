function previewFoto(input) {
    // Se l'utente ha selezionato un file
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            // Prendo i riferimenti ai due elementi HTML
            var imgElement = document.getElementById('previewImg');
            var iconElement = document.getElementById('defaultIcon');

            // Imposto la nuova immagine
            imgElement.src = e.target.result;

            // Mostro l'immagine e nascondo l'icona
            imgElement.classList.remove('d-none');
            iconElement.classList.add('d-none');
        }

        // Leggo il file caricato
        reader.readAsDataURL(input.files[0]);
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById('updateProfileForm');
    if (!form) return;

    function checkStatus(response) {
        if (!response.ok) throw Error(response.statusText);
        return response;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const msgDiv = document.getElementById('messaggioAjax');
        const phpAlert = document.getElementById('phpAlert');
        if (phpAlert) phpAlert.style.display = 'none';

        msgDiv.classList.add('d-none');
        document.querySelectorAll('.text-danger-custom').forEach(el => el.textContent = '');

        // Recupero Valori
        const nome = document.getElementById('nome').value.trim();
        const cognome = document.getElementById('cognome').value.trim();
        const email = document.getElementById('email').value.trim();
        const dataNascita = document.getElementById('data_nascita').value;

        let errori = false;

        // Validazione
        if (nome.length < 2) {
            document.getElementById('errNome').textContent = "Nome troppo corto";
            errori = true;
        }
        if (cognome.length < 2) {
            document.getElementById('errCognome').textContent = "Cognome troppo corto";
            errori = true;
        }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            document.getElementById('errEmail').textContent = "Email non valida";
            errori = true;
        }
        if (dataNascita === "") {
            document.getElementById('errData').textContent = "Data richiesta";
            errori = true;
        }

        if (errori) return;

        // AJAX
        const formData = new FormData(form);
        const apiUrl = '../backend/api_update_profile.php';

        fetch(apiUrl, {
            method: 'POST',
            body: formData
        })
            .then(checkStatus)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'ok') {
                    msgDiv.className = 'alert alert-success';
                    msgDiv.textContent = data.msg;
                    msgDiv.classList.remove('d-none');

                    // Aggiorna la foto e torna al profilo dopo 1.5s
                    setTimeout(() => {
                        window.location.href = '../frontend/profilo.php';
                    }, 1500);
                } else {
                    msgDiv.className = 'alert alert-danger';
                    msgDiv.textContent = data.msg;
                    msgDiv.classList.remove('d-none');
                }
            })
            .catch(error => {
                console.error(error);
                msgDiv.className = 'alert alert-danger';
                msgDiv.textContent = "Errore tecnico aggiornamento.";
                msgDiv.classList.remove('d-none');
            });
    });
});