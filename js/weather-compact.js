document.addEventListener('DOMContentLoaded', function() {
    // Toggle expanded state on click
    document.querySelectorAll('.weather-compact').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.stopPropagation();

            // Close other expanded widgets
            document.querySelectorAll('.weather-compact.expanded').forEach(function(other) {
                if (other !== el) {
                    other.classList.remove('expanded');
                }
            });

            // Toggle this widget
            el.classList.toggle('expanded');
        });
    });

    // Close on click outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.weather-compact')) {
            document.querySelectorAll('.weather-compact.expanded').forEach(function(el) {
                el.classList.remove('expanded');
            });
        }
    });

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.weather-compact.expanded').forEach(function(el) {
                el.classList.remove('expanded');
            });
        }
    });
});
