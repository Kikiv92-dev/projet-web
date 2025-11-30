// calendar-init.js

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('event-calendar');
    
    const calendar = new FullCalendar.Calendar(calendarEl, {
        // --- Customization ---
        locale: 'fr', // Use French language settings
        initialView: 'dayGridMonth', // Display the monthly grid view initially
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        
        // --- Theming (Optional: helps match your dark mode) ---
        // Note: FullCalendar's dark mode requires an extra theme file, 
        // but simple styles can be overridden in your admin-styles.css
        
        // --- Event Data ---
        events: [
            // Example Event 1: Next CTF Tournament
            {
                title: 'CTF Hiver Final',
                start: '2025-12-15',
                end: '2025-12-17',
                color: '#00ff00' // Neon Green background
            },
            // Example Event 2: Weekly BDE Meeting
            {
                title: 'Réunion BDE Hacking',
                daysOfWeek: [ 3 ], // Wednesday
                startTime: '18:00:00',
                endTime: '19:00:00',
                display: 'background', // Display as a background block
                color: '#2c3e50' // Dark background color
            },
            {
                title: 'Séminaire CyberSec',
                start: '2025-12-05', // Friday, December 5th
                allDay: true,
                color: '#00ff00', // A medium green
                textColor: '#FFFFFF' // White text for visibility
            },

             // --- New Event 2: Your BDE SANTA SECRET (If it's in Dec) ---
            { 
                title: 'BDE SANTA SECRET',
                start: '2025-12-19T20:00:00', // Specific date and time
                duration: '03:00', // 3 hours long
                color: '#00FF00', 
                textColor: '#000000' // Black text for maximum visibility
            },
            // You will load real event data here from your server later
            
        ]
        
    });

    calendar.render();
});