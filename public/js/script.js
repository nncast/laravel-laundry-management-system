const subMenus = document.querySelectorAll(".has-sub");

        subMenus.forEach(menu => {
            menu.addEventListener("click", (e) => {
                e.preventDefault();
                e.stopPropagation();

                const nextSubmenu = menu.nextElementSibling;

                // Collapse other submenus
                subMenus.forEach(item => {
                    if (item !== menu) {
                        item.classList.remove("active");
                        item.nextElementSibling.style.maxHeight = null;
                    }
                });

                // Toggle current submenu
                menu.classList.toggle("active");
                if (menu.classList.contains("active")) {
                    nextSubmenu.style.maxHeight = nextSubmenu.scrollHeight + "px";
                } else {
                    nextSubmenu.style.maxHeight = null;
                }
            });
        });

        // Highlight active submenu link
        const submenuLinks = document.querySelectorAll(".submenu a");
        submenuLinks.forEach(link => {
            link.addEventListener("click", e => {
                e.stopPropagation();
                submenuLinks.forEach(l => l.classList.remove("active-sub"));
                link.classList.add("active-sub");
                link.closest(".submenu").previousElementSibling.classList.add("active");
            });
        });