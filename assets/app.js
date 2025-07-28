import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

document.addEventListener('DOMContentLoaded', function() {
    const desktopToggle = document.querySelector(".toggle-btn");
    const mobileToggle = document.querySelector(".mobile-toggle-btn");
    const togglerIcon = document.querySelector("#icon");
    const sidebar = document.querySelector(".sidebar");

    function toggleSidebar() {
        sidebar.classList.toggle("expand");
        
        // Rotation de l'icône du bouton desktop
        if (sidebar.classList.contains("expand")) {
            togglerIcon.classList.remove("bxs-chevrons-right");
            togglerIcon.classList.add("bxs-chevrons-left");
        } else {
            togglerIcon.classList.remove("bxs-chevrons-left");
            togglerIcon.classList.add("bxs-chevrons-right");
        }
    }

    if (desktopToggle) {
        desktopToggle.addEventListener("click", toggleSidebar);
    }
    
    if (mobileToggle) {
        mobileToggle.addEventListener("click", toggleSidebar);
    }

    // Fermer le sidebar si on clique à l'extérieur (sur mobile)
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768 && !sidebar.contains(event.target) && 
            event.target !== mobileToggle && !mobileToggle.contains(event.target)) {
            sidebar.classList.remove("expand");
            togglerIcon.classList.remove("bxs-chevrons-left");
            togglerIcon.classList.add("bxs-chevrons-right");
        }
    });

    // Gestion du redimensionnement
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove("expand");
            togglerIcon.classList.remove("bxs-chevrons-left");
            togglerIcon.classList.add("bxs-chevrons-right");
        }
    });
});