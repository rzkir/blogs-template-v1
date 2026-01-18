//======================= Tailwind Config =======================//
tailwind.config = {
    theme: {
        extend: {
            colors: {
                brand: {
                    50: "#eef2ff",
                    500: "#6366f1",
                    600: "#4f46e5",
                    700: "#4338ca",
                },
            },
        },
    },
};

//======================= Toggle Password Visibility =======================//
document.addEventListener("DOMContentLoaded", function () {
    // Support for old format (login.php with id="togglePassword")
    const togglePasswordBtn = document.getElementById("togglePassword");
    if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener("click", function () {
            const passwordInput = document.getElementById("password");
            const eyeIcon = document.getElementById("eyeIcon");
            const eyeSlashIcon = document.getElementById("eyeSlashIcon");

            if (!passwordInput || !eyeIcon || !eyeSlashIcon) return;

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.add("hidden");
                eyeSlashIcon.classList.remove("hidden");
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.remove("hidden");
                eyeSlashIcon.classList.add("hidden");
            }
        });
    }

    // Support for new format with data-toggle-password attribute (register.php)
    const toggleButtons = document.querySelectorAll("[data-toggle-password]");
    toggleButtons.forEach((btn) => {
        btn.addEventListener("click", function () {
            const passwordId = btn.getAttribute("data-toggle-password");
            const passwordInput = document.getElementById(passwordId);
            const eyeIcon = btn.querySelector(`[data-eye-icon="${passwordId}"]`);
            const eyeSlashIcon = btn.querySelector(
                `[data-eye-slash-icon="${passwordId}"]`
            );

            if (!passwordInput || !eyeIcon || !eyeSlashIcon) return;

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.add("hidden");
                eyeSlashIcon.classList.remove("hidden");
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.remove("hidden");
                eyeSlashIcon.classList.add("hidden");
            }
        });
    });
});

//======================= Profile Dropdown Toggle =======================//
document.addEventListener('DOMContentLoaded', function () {
    const profileBtn = document.getElementById('profile-dropdown-btn');
    const profileMenu = document.getElementById('profile-dropdown-menu');
    const profileChevron = document.getElementById('profile-chevron');
    const profileChevronMobile = document.getElementById('profile-chevron-mobile');

    if (profileBtn && profileMenu) {
        profileBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            profileMenu.classList.toggle('hidden');
            if (profileChevron) {
                profileChevron.classList.toggle('rotate-180');
            }
            if (profileChevronMobile) {
                profileChevronMobile.classList.toggle('rotate-180');
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            const profileDropdown = document.getElementById('profile-dropdown');
            if (profileDropdown && !profileDropdown.contains(e.target)) {
                profileMenu.classList.add('hidden');
                if (profileChevron) {
                    profileChevron.classList.remove('rotate-180');
                }
                if (profileChevronMobile) {
                    profileChevronMobile.classList.remove('rotate-180');
                }
            }
        });
    }
});

