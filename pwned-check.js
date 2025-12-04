document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const feedbackElement = document.getElementById('passwordFeedback');
    const submitButton = document.getElementById('submitButton');

    // Vérification : s'assurer que les éléments sont trouvés
    if (!passwordInput || !feedbackElement || !submitButton) {
        console.error("Élément(s) HTML manquant(s) pour la vérification de mot de passe.");
        // Le bouton est désactivé par défaut (bonne pratique)
        if (submitButton) submitButton.disabled = true; 
        return;
    }

    // Écoute l'événement de saisie (à chaque fois que l'utilisateur tape)
    passwordInput.addEventListener('input', async () => {
        const password = passwordInput.value;
        
        // La validation de force doit correspondre au PHP (qui demande >= 10, pas 8)
        if (password.length < 10) {
            feedbackElement.textContent = "Le mot de passe doit contenir au moins 10 caractères et être complexe.";
            feedbackElement.style.color = "orange";
            submitButton.disabled = true;
            return;
        }

        feedbackElement.textContent = "Vérification de sécurité en cours...";
        feedbackElement.style.color = "gray";
        
        const isPwned = await checkPasswordPwned(password);

        if (isPwned > 0) {
            // Mot de passe compromis
            feedbackElement.textContent = `⚠️ ATTENTION : Ce mot de passe a été exposé ${isPwned} fois dans des fuites de données. Veuillez en choisir un autre.`;
            feedbackElement.style.color = "red";
            submitButton.disabled = true; // Empêche la soumission du formulaire
        } else {
            // Mot de passe sécurisé (au niveau de l'exposition)
            feedbackElement.textContent = "✅ Mot de passe sécurisé (non exposé).";
            feedbackElement.style.color = "green";
            submitButton.disabled = false; // Permet la soumission
        }
    });

    /**
     * Hache le mot de passe en SHA-1 et vérifie auprès de l'API Pwned Passwords.
     */
    async function checkPasswordPwned(password) {
        // Hachage SHA-1
        const encoder = new TextEncoder();
        const data = encoder.encode(password);

        // Cette partie nécessite HTTPS pour fonctionner (sinon erreur de crypto.subtle)
        try {
             const hashBuffer = await crypto.subtle.digest('SHA-1', data);
             const hashArray = Array.from(new Uint8Array(hashBuffer));
             const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('').toUpperCase();
        
             const prefix = hashHex.substring(0, 5);
             const suffix = hashHex.substring(5);

             // Requête à l'API Pwned Passwords
             const response = await fetch(`https://api.pwnedpasswords.com/range/${prefix}`);
             
             // ... reste de la logique de vérification ...

             if (!response.ok) {
                 console.error("Erreur API Pwned Passwords", response.status);
                 return 0;
             }

             const text = await response.text();
             const lines = text.split('\r\n');

             for (const line of lines) {
                 const [apiSuffix, count] = line.split(':');
                 if (apiSuffix === suffix) {
                     return parseInt(count, 10);
                 }
             }
             return 0;

        } catch (error) {
             console.error("Erreur de hachage ou de connexion (vérifiez si vous êtes en HTTPS) :", error);
             // On considère non exposé pour ne pas bloquer si c'est une erreur technique
             return 0;
        }
    }
});