// Bottone toggle
const toggleBtn = document.getElementById('theme-toggle');

// Funzione per aggiornare icona e testo
function updateToggle(isDark) {
    const icon = toggleBtn.querySelector('i');
    if (isDark) {
        icon.classList.remove('bi-moon-stars');
        icon.classList.add('bi-sun');
        toggleBtn.innerText = ' Light Mode';
    } else {
        icon.classList.remove('bi-sun');
        icon.classList.add('bi-moon-stars');
        toggleBtn.innerText = ' Dark Mode';
    }
    toggleBtn.prepend(icon);
}

// Funzione per impostare il tema
function setTheme(theme) {
    if (theme === 'dark') {
        document.body.classList.add('dark-mode');
        updateToggle(true);
    } else {
        document.body.classList.remove('dark-mode');
        updateToggle(false);
    }
    localStorage.setItem('theme', theme);
}

// Imposta tema iniziale dal localStorage
document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme') || 'light';
    setTheme(savedTheme);
});

// Toggle al click
toggleBtn.addEventListener('click', () => {
    const isDark = document.body.classList.contains('dark-mode');
    setTheme(isDark ? 'light' : 'dark');
});

// Flash message timeout
const flashAlert = document.getElementById('flash-message');
if (flashAlert) {
    setTimeout(() => {
        flashAlert.style.display = 'none';
    }, 2000);
}