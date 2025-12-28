document.addEventListener("DOMContentLoaded", () => {
    const subMenus = document.querySelectorAll(".has-sub");
    const TRANSITION_DURATION = 300;

    // Add CSS for smooth transitions
    const style = document.createElement('style');
    style.textContent = `
        .submenu {
            transition: max-height ${TRANSITION_DURATION}ms cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }
        .has-sub {
            transition: all 200ms ease;
        }
    `;
    document.head.appendChild(style);

    function closeOtherSubmenus(currentMenu) {
        subMenus.forEach(item => {
            if (item !== currentMenu && item.classList.contains("active")) {
                item.classList.remove("active");
                const sub = item.nextElementSibling;
                if (sub) sub.style.maxHeight = null;
            }
        });
    }

    function toggleSubmenu(menu) {
        const isOpening = !menu.classList.contains("active");
        const submenu = menu.nextElementSibling;
        if (!submenu) return;

        requestAnimationFrame(() => {
            if (isOpening) {
                submenu.style.maxHeight = '0px';
                menu.classList.add("active");

                requestAnimationFrame(() => {
                    submenu.style.maxHeight = submenu.scrollHeight + "px";
                });
            } else {
                submenu.style.maxHeight = null;
                menu.classList.remove("active");
            }
        });
    }

    subMenus.forEach(menu => {
        menu.addEventListener("click", e => {
            e.preventDefault();
            e.stopPropagation();

            closeOtherSubmenus(menu);
            toggleSubmenu(menu);
        });
    });

    // Highlight active submenu link and keep parent menu open
    const submenuLinks = document.querySelectorAll(".submenu a");
    submenuLinks.forEach(link => {
        link.addEventListener("click", e => {
            e.stopPropagation(); // Prevent document click
            submenuLinks.forEach(l => l.classList.remove("active-sub"));
            link.classList.add("active-sub");

            const parentMenu = link.closest(".submenu")?.previousElementSibling;
            if (parentMenu && !parentMenu.classList.contains("active")) {
                toggleSubmenu(parentMenu);
            }
        });
    });

    // Close submenus when clicking outside
    document.addEventListener("click", (e) => {
        if (!e.target.closest(".sidebar")) { // Only close if click is outside sidebar
            subMenus.forEach(menu => {
                if (menu.classList.contains("active")) {
                    menu.classList.remove("active");
                    const sub = menu.nextElementSibling;
                    if (sub) sub.style.maxHeight = null;
                }
            });
        }
    });

    // Close all submenus on Escape
    document.addEventListener("keydown", (e) => {
        if (e.key === 'Escape') {
            subMenus.forEach(menu => {
                if (menu.classList.contains("active")) {
                    menu.classList.remove("active");
                    const sub = menu.nextElementSibling;
                    if (sub) sub.style.maxHeight = null;
                }
            });
        }
    });
});
