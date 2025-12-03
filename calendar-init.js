// calendar-init.js

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('event-calendar');
    
    // La variable 'serverEvents' est maintenant définie globalement dans calendar.php
    // (Elle contient vos événements Raclette Party, BDE Santa Secret, etc. au format JSON)
    
    // --- Événements Statiques (Ceux qui sont codés en dur dans ce fichier) ---
    let staticEvents = [
        // Example Event 1: Next CTF Tournament
        {
            title: 'CTF Hiver Final',
            start: '2025-12-15',
            end: '2025-12-17',
            color: '#00ff00'
        },
        // Example Event 2: Weekly BDE Meeting (Background Event)
        {
            title: 'Réunion BDE Hacking',
            daysOfWeek: [ 3 ], // Wednesday
            startTime: '18:00:00',
            endTime: '19:00:00',
            display: 'background',
            color: '#2c3e50'
        },
        {
            title: 'Séminaire CyberSec',
            start: '2025-12-05',
            allDay: true,
            color: '#00ff00',
            textColor: '#FFFFFF'
        },
    ];
    
    // Vérification de la variable globale 'serverEvents' (injectée par PHP)
    // On s'assure qu'elle existe et qu'elle est un tableau avant de concaténer.
    let phpEvents = Array.isArray(serverEvents) ? serverEvents : [];

    // Concaténer les événements statiques avec ceux récupérés du PHP
    let allEvents = staticEvents.concat(phpEvents);
    
    const calendar = new FullCalendar.Calendar(calendarEl, {
        // --- Customization ---
        locale: 'fr', 
        initialView: 'dayGridMonth',
        
        // --- Ajout du test initialDate pour voir les événements de 2025 immédiatement ---
        initialDate: '2025-12-01', 
        
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        
        // --- Event Data (Utilise la variable fusionnée) ---
        events: allEvents,
        
        eventDidMount: function(info) {
             info.el.style.borderColor = info.event.backgroundColor;
             info.el.style.backgroundColor = info.event.backgroundColor + '33'; // Semi-transparent
        }
    });

    calendar.render();
});