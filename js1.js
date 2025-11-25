const canvas = document.getElementById("matrixCanvas");
const ctx = canvas.getContext("2d");

// Ajuster la taille du canvas à la taille de la fenêtre
canvas.width = window.innerWidth;
canvas.height = window.innerHeight;

// Le contenu de la pluie (0 et 1)
const letters = "01".split("");
const fontSize = 16;
const columns = canvas.width / fontSize;
// Un tableau pour suivre la position y (ligne) de chaque colonne
const drops = Array(Math.floor(columns)).fill(0);

function draw() {
    // Ombrage de l'arrière-plan pour un effet de traînée
    ctx.fillStyle = "rgba(0, 0, 0, 0.05)"; // Taux de traînée plus faible pour la lisibilité
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Style du texte
    ctx.fillStyle = "#0f0"; // Vert Matrix
    ctx.font = `${fontSize}px monospace`;

    // Dessiner chaque goutte
    for (let i = 0; i < drops.length; i++) {
        // Sélectionner une lettre aléatoire
        const text = letters[Math.floor(Math.random() * letters.length)];
        
        // Dessiner le texte à la position (x, y)
        ctx.fillText(text, i * fontSize, drops[i] * fontSize);

        // Si la goutte atteint le bas de l'écran OU qu'une condition aléatoire est remplie,
        // la remettre en haut.
        if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
            drops[i] = 0;
        }
        drops[i]++;
    }
}

// Rendre l'animation responsive
window.addEventListener('resize', () => {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    // Recalculer les colonnes pour que l'effet remplisse bien la nouvelle taille
    const newColumns = canvas.width / fontSize;
    drops.length = Math.floor(newColumns);
    drops.fill(0);
});

// Lancer l'animation
setInterval(draw, 50);