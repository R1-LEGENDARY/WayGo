document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('vehicle-calendar');
    if (!calendarEl || !window.FullCalendar) {
        return;
    }

    let events = [];
    try {
        events = JSON.parse(calendarEl.dataset.events || '[]');
    } catch (error) {
        return;
    }

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: '100%',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },
        events: events
    });

    calendar.render();
});
