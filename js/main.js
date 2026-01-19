//======================= Mobile Menu & Search Toggle =======================//

// Mobile Search Toggle
(function () {
    function initMobileSearch() {
        const mobileSearchBtn = document.getElementById('mobileSearchBtn');
        const mobileSearch = document.getElementById('mobileSearch');

        if (!mobileSearchBtn || !mobileSearch) return;

        let isOpen = false;

        mobileSearchBtn.onclick = function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (isOpen) {
                mobileSearch.classList.add('hidden');
                isOpen = false;
            } else {
                mobileSearch.classList.remove('hidden');
                isOpen = true;
                // Focus on input
                setTimeout(function () {
                    const searchInput = mobileSearch.querySelector('input[name="q"]');
                    if (searchInput) searchInput.focus();
                }, 100);
            }
        };
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobileSearch);
    } else {
        initMobileSearch();
    }
})();

//======================= Dark Mode / Theme Switcher =======================//

// Theme management
(function () {
    const THEME_KEY = 'blog-theme';
    const THEMES = {
        LIGHT: 'light',
        DARK: 'dark',
        SYSTEM: 'system'
    };

    function getSystemTheme() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? THEMES.DARK : THEMES.LIGHT;
    }

    function getSavedTheme() {
        return localStorage.getItem(THEME_KEY) || THEMES.SYSTEM;
    }

    function saveTheme(theme) {
        localStorage.setItem(THEME_KEY, theme);
    }

    function applyTheme(theme) {
        const actualTheme = theme === THEMES.SYSTEM ? getSystemTheme() : theme;

        if (actualTheme === THEMES.DARK) {
            document.documentElement.classList.add('dark');
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            document.documentElement.removeAttribute('data-theme');
        }

        // Add theme-loaded class to enable transitions after theme is set
        document.body.classList.add('theme-loaded');

        updateThemeIcon(theme);
        updateThemeCheckmarks(theme);
    }

    function updateThemeIcon(theme) {
        const themeIcon = document.getElementById('themeIcon');
        if (!themeIcon) return;

        themeIcon.classList.remove('fa-sun', 'fa-moon', 'fa-desktop');

        switch (theme) {
            case THEMES.LIGHT:
                themeIcon.classList.add('fa-sun');
                break;
            case THEMES.DARK:
                themeIcon.classList.add('fa-moon');
                break;
            case THEMES.SYSTEM:
                themeIcon.classList.add('fa-desktop');
                break;
        }
    }

    function updateThemeCheckmarks(currentTheme) {
        const themeOptions = document.querySelectorAll('.theme-option');
        themeOptions.forEach(option => {
            const optionTheme = option.getAttribute('data-theme');
            const checkmark = option.querySelector('.theme-check');
            if (checkmark) {
                if (optionTheme === currentTheme) {
                    checkmark.classList.remove('hidden');
                } else {
                    checkmark.classList.add('hidden');
                }
            }
        });
    }

    // Setup theme switcher after DOM is ready
    function initThemeSwitcher() {
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeDropdown = document.getElementById('themeDropdown');
        const themeSwitcher = document.getElementById('themeSwitcher');
        const themeOptions = document.querySelectorAll('.theme-option');
        const savedTheme = getSavedTheme();

        if (!themeToggleBtn || !themeDropdown || !themeSwitcher) {
            return;
        }

        // Update UI on load
        updateThemeIcon(savedTheme);
        updateThemeCheckmarks(savedTheme);

        let isOpen = false;
        let justClicked = false;

        // Function to close dropdown
        function closeDropdown() {
            themeDropdown.classList.add('hidden');
            themeDropdown.style.display = 'none';
            isOpen = false;
        }

        // Function to open dropdown
        function openDropdown() {
            themeDropdown.classList.remove('hidden');
            themeDropdown.style.display = 'block';
            themeDropdown.style.visibility = 'visible';
            themeDropdown.style.opacity = '1';
            isOpen = true;
        }

        // Toggle dropdown on button click
        themeToggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            justClicked = true;

            if (isOpen) {
                closeDropdown();
            } else {
                openDropdown();
            }

            // Reset flag after this event cycle completes
            setTimeout(function () {
                justClicked = false;
            }, 50);
        });

        // Global click handler to close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            // Skip if we just clicked the button (event is still processing)
            if (justClicked) return;

            // Skip if dropdown is not open
            if (!isOpen) return;

            // Check if click is inside theme switcher
            if (!themeSwitcher.contains(e.target)) {
                closeDropdown();
            }
        });

        // Handle theme selection
        themeOptions.forEach(option => {
            option.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const selectedTheme = this.getAttribute('data-theme');
                saveTheme(selectedTheme);
                applyTheme(selectedTheme);
                closeDropdown();
            });
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initThemeSwitcher);
    } else {
        // DOM already loaded
        initThemeSwitcher();
    }

    // Listen for system theme changes when using system theme
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
        const currentTheme = getSavedTheme();
        if (currentTheme === THEMES.SYSTEM) {
            applyTheme(THEMES.SYSTEM);
        }
    });
})();

//======================= Utility Functions =======================//

/**
 * Process image URLs - convert markdown syntax and plain URLs to HTML img tags
 * @param {string} content - Content string that may contain image URLs
 * @returns {string} - Content with image URLs converted to HTML img tags
 */
function processImageUrls(content) {
    if (!content || typeof content !== 'string') return content;

    // Pattern untuk markdown image syntax: ![alt](url)
    const markdownImagePattern = /!\[([^\]]*)\]\(([^)]+)\)/g;
    content = content.replace(markdownImagePattern, function (match, alt, url) {
        alt = alt || 'Image';
        url = url.trim();
        // Validasi URL
        if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('data:image/') || url.startsWith('/')) {
            return '<img src="' + url + '" alt="' + alt.replace(/"/g, '&quot;') + '" />';
        }
        return match; // Return original jika URL tidak valid
    });

    // Pattern untuk plain image URLs (http/https yang belum di-wrap dalam tag)
    // Hanya convert jika belum ada dalam tag img
    const plainUrlPattern = /(https?:\/\/[^\s<>"']+\.(jpg|jpeg|png|gif|webp|svg))/gi;
    content = content.replace(plainUrlPattern, function (match, url) {
        // Skip jika sudah dalam tag HTML img
        const matchIndex = content.indexOf(match);
        if (matchIndex === -1) return match;

        const beforeMatch = content.substring(0, matchIndex);
        const lastImgOpen = beforeMatch.lastIndexOf('<img');
        const lastImgClose = beforeMatch.lastIndexOf('>');

        // Jika ada tag <img sebelum match dan belum ditutup, skip
        if (lastImgOpen !== -1 && lastImgOpen > lastImgClose) {
            return match; // Skip, sudah dalam tag img
        }

        // Skip jika sudah dalam tag img (cek setelah match juga)
        const afterMatch = content.substring(matchIndex + match.length);
        if (afterMatch.indexOf('</img>') !== -1 || afterMatch.indexOf('/>') !== -1) {
            return match; // Skip, sudah dalam tag
        }

        return '<img src="' + url + '" alt="Image" />';
    });

    return content;
}

/**
 * Clean markdown code blocks from content
 * @param {string} content - Content string that may contain markdown code blocks
 * @returns {string} - Content with markdown code blocks removed
 */
function cleanMarkdownCodeBlocks(content) {
    if (!content || typeof content !== 'string') return content;

    // Remove markdown code blocks (```html ... ``` or ``` ... ```)
    let cleaned = content.replace(/```html\s*\n?/gi, '');
    cleaned = cleaned.replace(/```\s*\n?/g, '');
    cleaned = cleaned.replace(/```$/gm, '');

    // Remove any remaining markdown code block markers
    cleaned = cleaned.replace(/^```.*$/gm, '');

    // Remove image tags - user will add images manually
    cleaned = removeImageTags(cleaned);

    // Clean excessive spacing from HTML elements
    cleaned = cleanHtmlSpacing(cleaned);

    // Trim whitespace
    cleaned = cleaned.trim();

    return cleaned;
}

/**
 * Clean excessive spacing from HTML content
 * @param {string} html - HTML content string
 * @returns {string} - HTML content with cleaned spacing
 */
function cleanHtmlSpacing(html) {
    if (!html || typeof html !== 'string') return html;

    // Remove inline style attributes that add excessive spacing
    let cleaned = html.replace(/\s*style\s*=\s*["'][^"']*margin[^"']*["']/gi, '');
    cleaned = cleaned.replace(/\s*style\s*=\s*["'][^"']*padding[^"']*["']/gi, '');
    cleaned = cleaned.replace(/\s*style\s*=\s*["'][^"']*line-height[^"']*["']/gi, '');

    // Remove empty style attributes
    cleaned = cleaned.replace(/\s*style\s*=\s*["']\s*["']/gi, '');

    // Remove multiple consecutive line breaks in text content
    cleaned = cleaned.replace(/\n{3,}/g, '\n\n');

    // Remove excessive whitespace between tags
    cleaned = cleaned.replace(/>\s{2,}</g, '><');

    return cleaned;
}

/**
 * Remove image tags from HTML content
 * @param {string} html - HTML content string
 * @returns {string} - HTML content without image tags
 */
function removeImageTags(html) {
    if (!html || typeof html !== 'string') return html;

    // Remove all <img> tags (self-closing and with closing tag)
    let cleaned = html.replace(/<img[^>]*>/gi, '');
    cleaned = cleaned.replace(/<img[^>]*\/>/gi, '');

    // Remove any empty paragraphs or divs that might have been left after removing images
    cleaned = cleaned.replace(/<p[^>]*>\s*<\/p>/gi, '');
    cleaned = cleaned.replace(/<div[^>]*>\s*<\/div>/gi, '');

    return cleaned;
}

//======================= Tailwind Config =======================//
tailwind.config = {
    darkMode: ['class', '[data-theme="dark"]'],
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

    // Auto-inject "eye" toggle for password fields that don't have one yet
    // (e.g. dashboard/profile change password modal inputs)
    (function initAutoPasswordEyes() {
        /**
         * @param {HTMLInputElement} input
         */
        function hasExistingToggleForInput(input) {
            // Existing patterns in this project:
            // - login: button#togglePassword controls input#password
            // - register: button[data-toggle-password="<inputId>"]
            if (input.id === "password" && document.getElementById("togglePassword")) return true;
            if (input.id && document.querySelector(`[data-toggle-password="${CSS.escape(input.id)}"]`)) return true;
            // Generic: any nearby button explicitly marked as toggle
            const parent = input.parentElement;
            if (!parent) return false;
            return Boolean(parent.querySelector('[data-toggle-password], #togglePassword, [data-password-eye-btn="true"]'));
        }

        /**
         * @param {HTMLInputElement} input
         */
        function attachEyeToggle(input) {
            if (input.dataset.passwordEyeAttached === "true") return;
            if (hasExistingToggleForInput(input)) return;
            if (input.closest('[data-no-password-eye="true"]')) return;

            const parent = input.parentElement;
            if (!parent) return;

            // We want the button positioned relative to a container that matches the input box,
            // not the whole label (which may include a title/span above the input).
            let fieldWrapper = parent;
            const parentTag = (parent.tagName || "").toLowerCase();

            // If input is inside a <label> (common in this project), create a wrapper div
            // around the input so absolute positioning aligns with the input height.
            if (parentTag === "label") {
                const wrap = document.createElement("div");
                wrap.setAttribute("data-password-eye-field", "true");
                wrap.style.position = "relative";
                wrap.style.display = "block";
                // Insert wrapper right before the input and move input into it
                parent.insertBefore(wrap, input);
                wrap.appendChild(input);
                fieldWrapper = wrap;
            } else {
                // Ensure the existing parent can position the button
                const currentPos = window.getComputedStyle(fieldWrapper).position;
                if (currentPos === "static" || !currentPos) {
                    fieldWrapper.style.position = "relative";
                }
            }

            // Add padding so text doesn't overlap button
            const computedPaddingRight = parseFloat(window.getComputedStyle(input).paddingRight || "0");
            if (computedPaddingRight < 44) {
                input.style.paddingRight = "44px";
            }

            const btn = document.createElement("button");
            btn.type = "button";
            btn.setAttribute("data-password-eye-btn", "true");
            btn.setAttribute("aria-label", "Tampilkan password");

            // Inline styles so it works without Tailwind scanning
            btn.style.position = "absolute";
            btn.style.right = "12px";
            btn.style.top = "0";
            btn.style.bottom = "0";
            btn.style.display = "flex";
            btn.style.alignItems = "center";
            btn.style.background = "transparent";
            btn.style.border = "0";
            btn.style.padding = "6px";
            btn.style.margin = "0";
            btn.style.cursor = "pointer";
            btn.style.color = "#64748b"; // slate-500
            btn.style.lineHeight = "1";

            const icon = document.createElement("i");
            icon.className = "fas fa-eye";
            btn.appendChild(icon);

            btn.addEventListener("click", function () {
                const isPassword = input.type === "password";
                input.type = isPassword ? "text" : "password";
                icon.classList.toggle("fa-eye", !isPassword);
                icon.classList.toggle("fa-eye-slash", isPassword);
                btn.setAttribute("aria-label", isPassword ? "Sembunyikan password" : "Tampilkan password");
            });

            fieldWrapper.appendChild(btn);
            input.dataset.passwordEyeAttached = "true";
        }

        // Attach on initial load
        const passwordInputs = document.querySelectorAll('input[type="password"]');
        passwordInputs.forEach((input) => {
            attachEyeToggle(/** @type {HTMLInputElement} */(input));
        });

        // Also attach when modals open / DOM changes (cheap observer)
        const observer = new MutationObserver(function (mutations) {
            for (const m of mutations) {
                if (m.type !== "childList") continue;
                m.addedNodes.forEach((node) => {
                    if (!(node instanceof Element)) return;
                    const inputs = node.matches('input[type="password"]')
                        ? [node]
                        : Array.from(node.querySelectorAll('input[type="password"]'));
                    inputs.forEach((inp) => attachEyeToggle(/** @type {HTMLInputElement} */(inp)));
                });
            }
        });

        observer.observe(document.body, { childList: true, subtree: true });
    })();
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

//======================= Create / Edit Post Page =======================//
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formCreatePost') || document.getElementById('formEditPost');
    if (!form || typeof Quill === 'undefined') return;

    var AI_API_ENDPOINT = window.AI_API_ENDPOINT;

    function titleToSlug(title) {
        let slug = title.toLowerCase();
        slug = slug.replace(/\s+/g, '-');
        slug = slug.replace(/[^a-z0-9\-]/g, '');
        slug = slug.replace(/-+/g, '-');
        return slug.replace(/^-+|-+$/g, '');
    }

    // Auto-generate slug from title
    const titleInput = document.getElementById('title');
    if (titleInput) {
        titleInput.addEventListener('input', function (e) {
            const slug = titleToSlug(e.target.value);
            const slugInput = document.getElementById('slug');
            if (slugInput) slugInput.value = slug;
        });
    }

    // Initialize Quill editor
    const quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ color: [] }, { background: [] }],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ align: [] }],
                ['link', 'image', 'video'],
                ['blockquote', 'code-block'],
                ['clean']
            ]
        }
    });

    // Custom handler untuk image - memastikan gambar ditampilkan dengan benar
    var toolbar = quill.getModule('toolbar');
    if (toolbar) {
        toolbar.addHandler('image', function () {
            var range = quill.getSelection(true);
            if (!range) range = { index: quill.getLength(), length: 0 };

            var url = prompt('Masukkan URL gambar:');
            if (!url || !url.trim()) return;

            url = url.trim();

            // Validasi URL dasar
            if (!url.startsWith('http://') && !url.startsWith('https://') && !url.startsWith('data:image/') && !url.startsWith('/')) {
                alert('URL tidak valid. Pastikan URL dimulai dengan http://, https://, data:image/, atau /');
                return;
            }

            // Langsung insert image - Quill akan menampilkan gambar
            quill.insertEmbed(range.index, 'image', url, 'user');
            quill.setSelection(range.index + 1);

            // Validasi gambar setelah insert (optional - untuk error handling)
            setTimeout(function () {
                var insertedImg = quill.root.querySelector('img[src="' + url + '"]');
                if (insertedImg) {
                    insertedImg.onerror = function () {
                        alert('Gagal memuat gambar. Pastikan URL gambar valid dan dapat diakses.');
                        this.style.border = '2px solid red';
                        this.alt = 'Gambar tidak dapat dimuat';
                    };
                }
            }, 100);
        });
    }


    // Set existing content to Quill (edit page only; create has empty #content)
    const contentInput = document.getElementById('content');
    if (contentInput && contentInput.value) {
        quill.root.innerHTML = contentInput.value;
    }

    // Sync Quill content to hidden input before form submit
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const content = quill.root.innerHTML;
        const stripHtml = function (html) {
            const tmp = document.createElement('div');
            tmp.innerHTML = html;
            return (tmp.textContent || tmp.innerText || '').trim();
        };
        if (!stripHtml(content)) {
            alert('Konten post wajib diisi.');
            return;
        }
        contentInput.value = content;
        e.target.submit();
    });

    // Image preview
    const imageInput = document.getElementById('image');
    if (imageInput) {
        imageInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            if (file) {
                const reader = new FileReader();
                reader.onload = function (ev) {
                    if (previewImg) previewImg.src = ev.target.result;
                    if (preview) preview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else if (preview) {
                preview.classList.add('hidden');
            }
        });
    }

    // AI Helper Functions
    function showLoading(buttonId) {
        const btn = document.getElementById(buttonId);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Loading...</span>';
        }
    }
    function resetButton(buttonId, originalText) {
        const btn = document.getElementById(buttonId);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
    function showError(message) {
        alert('Error: ' + message);
    }

    // AI Generate Title
    const aiGenerateTitleBtn = document.getElementById('aiGenerateTitle');
    if (aiGenerateTitleBtn) {
        aiGenerateTitleBtn.addEventListener('click', async function () {
            const topicInput = prompt('Masukkan topik atau ide untuk judul:');
            if (!topicInput || !topicInput.trim()) return;
            const originalText = this.innerHTML;
            showLoading('aiGenerateTitle');
            try {
                let suggestions = '';
                await generateTitle(AI_API_ENDPOINT, topicInput, function (c) { suggestions = c; }, function (err) {
                    showError(err);
                    resetButton('aiGenerateTitle', originalText);
                });
                if (suggestions) {
                    const ti = document.getElementById('title');
                    const first = suggestions.split('\n')[0].trim().replace(/^[0-9]+\.\s*/, '');
                    if (ti && first) {
                        ti.value = first;
                        ti.dispatchEvent(new Event('input'));
                    }
                }
            } catch (err) {
                showError(err.message);
            } finally {
                resetButton('aiGenerateTitle', originalText);
            }
        });
    }

    // AI Generate Description
    const aiGenerateDescriptionBtn = document.getElementById('aiGenerateDescription');
    if (aiGenerateDescriptionBtn) {
        aiGenerateDescriptionBtn.addEventListener('click', async function () {
            const title = document.getElementById('title').value;
            if (!title || !title.trim()) {
                alert('Silakan isi judul terlebih dahulu.');
                return;
            }
            const originalText = this.innerHTML;
            showLoading('aiGenerateDescription');
            try {
                const content = quill.root.textContent || '';
                await generateDescription(AI_API_ENDPOINT, title, content, function (desc) {
                    const d = document.getElementById('description');
                    if (d) d.value = (desc || '').trim();
                }, function (err) {
                    showError(err);
                    resetButton('aiGenerateDescription', originalText);
                });
            } catch (err) {
                showError(err.message);
            } finally {
                resetButton('aiGenerateDescription', originalText);
            }
        });

        // AI Generate Content (only on edit page, #aiGenerateContent exists)
        const aiGenerateContentBtn = document.getElementById('aiGenerateContent');
        if (aiGenerateContentBtn) {
            aiGenerateContentBtn.addEventListener('click', async function () {
                const title = document.getElementById('title').value;
                if (!title || !title.trim()) {
                    alert('Silakan isi judul terlebih dahulu.');
                    return;
                }
                const originalText = this.innerHTML;
                showLoading('aiGenerateContent');
                try {
                    const desc = (document.getElementById('description') || {}).value || '';
                    await generateBlogContent(AI_API_ENDPOINT, title, desc, function (c) {
                        // Clean markdown code blocks dan process image URLs
                        const cleanedContent = cleanMarkdownCodeBlocks(c);
                        quill.root.innerHTML = cleanedContent;
                    }, function (err) {
                        showError(err);
                        resetButton('aiGenerateContent', originalText);
                    });
                } catch (err) {
                    showError(err.message);
                } finally {
                    resetButton('aiGenerateContent', originalText);
                }
            });
        }
    }

    // AI Improve Content
    const aiImproveContentBtn = document.getElementById('aiImproveContent');
    if (aiImproveContentBtn) {
        aiImproveContentBtn.addEventListener('click', async function () {
            const currentContent = quill.root.innerHTML;
            if (!currentContent || !currentContent.trim()) {
                alert('Silakan isi konten terlebih dahulu.');
                return;
            }
            const originalText = this.innerHTML;
            showLoading('aiImproveContent');
            try {
                await improveContent(AI_API_ENDPOINT, currentContent, function (c) {
                    // Clean markdown code blocks dan process image URLs
                    const cleanedContent = cleanMarkdownCodeBlocks(c);
                    quill.root.innerHTML = cleanedContent;
                }, function (err) {
                    showError(err);
                    resetButton('aiImproveContent', originalText);
                });
            } catch (err) {
                showError(err.message);
            } finally {
                resetButton('aiImproveContent', originalText);
            }
        });
    }

    // Modal Generate All with AI
    setTimeout(function () {
        const aiModal = document.getElementById('aiGenerateModal');
        const aiPromptInput = document.getElementById('aiPrompt');
        const aiProgressDiv = document.getElementById('aiGenerateProgress');
        const aiProgressText = document.getElementById('aiProgressText');
        const aiProgressBar = document.getElementById('aiProgressBar');
        const startAIGenerateBtn = document.getElementById('startAIGenerate');
        const closeAIModalBtn = document.getElementById('closeAIModal');
        const cancelAIGenerateBtn = document.getElementById('cancelAIGenerate');
        const generateAllBtn = document.getElementById('generateAllWithAI');

        if (!aiModal || !generateAllBtn) return;

        generateAllBtn.addEventListener('click', function () {
            aiModal.classList.remove('hidden');
            aiModal.classList.add('flex');
            if (aiPromptInput) aiPromptInput.value = '';
            if (aiProgressDiv) aiProgressDiv.classList.add('hidden');
            if (aiProgressBar) aiProgressBar.style.width = '0%';
        });

        function closeAIModal() {
            aiModal.classList.add('hidden');
            aiModal.classList.remove('flex');
            if (aiPromptInput) aiPromptInput.value = '';
            if (aiProgressDiv) aiProgressDiv.classList.add('hidden');
            if (aiProgressBar) aiProgressBar.style.width = '0%';
        }
        if (closeAIModalBtn) closeAIModalBtn.addEventListener('click', closeAIModal);
        if (cancelAIGenerateBtn) cancelAIGenerateBtn.addEventListener('click', closeAIModal);
        aiModal.addEventListener('click', function (e) {
            if (e.target === aiModal) closeAIModal();
        });

        if (startAIGenerateBtn) {
            startAIGenerateBtn.addEventListener('click', async function () {
                const topic = (aiPromptInput && aiPromptInput.value) ? aiPromptInput.value.trim() : '';
                if (!topic) {
                    alert('Silakan masukkan topik atau ide untuk blog post.');
                    return;
                }
                if (aiProgressDiv) aiProgressDiv.classList.remove('hidden');
                startAIGenerateBtn.disabled = true;
                startAIGenerateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Generating...';
                try {
                    await generateAllContent(
                        AI_API_ENDPOINT,
                        topic,
                        function (title) {
                            const ti = document.getElementById('title');
                            if (ti) { ti.value = title; ti.dispatchEvent(new Event('input')); }
                        },
                        function (description) {
                            const d = document.getElementById('description');
                            if (d) d.value = description;
                        },
                        function (content) {
                            if (quill) {
                                // Clean markdown code blocks dan process image URLs
                                const cleanedContent = cleanMarkdownCodeBlocks(content);
                                quill.root.innerHTML = cleanedContent;
                            }
                        },
                        function (text, pct) {
                            if (aiProgressText) aiProgressText.textContent = text;
                            if (aiProgressBar) aiProgressBar.style.width = pct + '%';
                        },
                        function (err) {
                            showError(err);
                            if (aiProgressDiv) aiProgressDiv.classList.add('hidden');
                            startAIGenerateBtn.disabled = false;
                            startAIGenerateBtn.innerHTML = '<i class="fas fa-magic mr-2"></i> Generate';
                        }
                    );
                    setTimeout(function () {
                        closeAIModal();
                        if (typeof showToast === 'function') showToast('Konten berhasil di-generate dengan AI!', 'success');
                        else alert('Konten berhasil di-generate dengan AI!');
                    }, 500);
                } catch (err) {
                    showError(err.message);
                    if (aiProgressDiv) aiProgressDiv.classList.add('hidden');
                } finally {
                    startAIGenerateBtn.disabled = false;
                    startAIGenerateBtn.innerHTML = '<i class="fas fa-magic mr-2"></i> Generate';
                }
            });
        }
    }, 100);

    // Tag Input Field Handler
    const tagInput = document.getElementById('tagInput');
    const tagsContainer = document.getElementById('tagsContainer');
    const tagsArrayInput = document.getElementById('tagsArray');

    // Tag management variables and functions (accessible to both handlers)
    let tags = [];
    let updateTagsDisplay, addTag, removeTag;

    if (tagInput && tagsContainer && tagsArrayInput) {
        // Load existing tags from hidden input (for edit page)
        try {
            const existingTags = JSON.parse(tagsArrayInput.value || '[]');
            if (Array.isArray(existingTags) && existingTags.length > 0) {
                tags = existingTags;
            }
        } catch (e) {
            // Ignore parse errors
        }

        updateTagsDisplay = function () {
            tagsContainer.innerHTML = '';
            tags.forEach((tag, index) => {
                const tagChip = document.createElement('span');
                tagChip.className = 'tag-chip inline-flex items-center gap-1.5 px-3 py-1 bg-sky-100 text-sky-700 rounded-full text-sm';
                tagChip.innerHTML = `
                    ${tag}
                    <button type="button" class="tag-remove text-sky-600 hover:text-sky-800" data-index="${index}">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                `;
                tagsContainer.appendChild(tagChip);
            });
            tagsArrayInput.value = JSON.stringify(tags);
        };

        addTag = function (tagName) {
            tagName = tagName.trim();
            if (tagName && !tags.includes(tagName)) {
                tags.push(tagName);
                updateTagsDisplay();
            }
        };

        removeTag = function (index) {
            tags.splice(index, 1);
            updateTagsDisplay();
        };

        // Initial display
        updateTagsDisplay();

        // Handle tag input
        tagInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                const value = tagInput.value.trim();
                if (value) {
                    // If comma, split by comma
                    if (e.key === ',') {
                        const parts = value.split(',').map(t => t.trim()).filter(t => t);
                        parts.forEach(part => addTag(part));
                    } else {
                        addTag(value);
                    }
                    tagInput.value = '';
                }
            }
        });

        // Handle tag removal
        tagsContainer.addEventListener('click', function (e) {
            if (e.target.closest('.tag-remove')) {
                const index = parseInt(e.target.closest('.tag-remove').getAttribute('data-index'));
                removeTag(index);
            }
        });
    }

    // AI Generate Tags
    const aiGenerateTagsBtn = document.getElementById('aiGenerateTags');
    if (aiGenerateTagsBtn && tagInput && tagsContainer && tagsArrayInput) {
        aiGenerateTagsBtn.addEventListener('click', async function () {
            const title = document.getElementById('title')?.value || '';
            if (!title || !title.trim()) {
                alert('Silakan isi judul terlebih dahulu.');
                return;
            }
            const description = document.getElementById('description')?.value || '';
            const originalText = this.innerHTML;
            showLoading('aiGenerateTags');
            try {
                let generatedTags = '';
                await generateTags(AI_API_ENDPOINT, title, description, function (tags) {
                    generatedTags = tags.trim();
                }, function (err) {
                    showError(err);
                    resetButton('aiGenerateTags', originalText);
                });

                if (generatedTags) {
                    // Parse tags from comma-separated string
                    const tagList = generatedTags.split(',').map(t => t.trim()).filter(t => t);
                    // Remove common prefixes/suffixes that AI might add
                    const cleanedTags = tagList.map(tag => {
                        return tag.replace(/^(tags?|tag:|\-|\*|\d+\.)\s*/i, '').trim();
                    }).filter(t => t);

                    // Add tags using the shared addTag function if available
                    if (addTag) {
                        cleanedTags.forEach(tag => {
                            if (tag) {
                                addTag(tag);
                            }
                        });
                    } else {
                        // Fallback: manually update if functions not available
                        const tagInput = document.getElementById('tagInput');
                        const tagsContainer = document.getElementById('tagsContainer');
                        const tagsArrayInput = document.getElementById('tagsArray');
                        if (tagInput && tagsContainer && tagsArrayInput) {
                            let currentTags = [];
                            try {
                                currentTags = JSON.parse(tagsArrayInput.value || '[]');
                            } catch (e) { }

                            cleanedTags.forEach(tag => {
                                if (tag && !currentTags.includes(tag)) {
                                    currentTags.push(tag);
                                }
                            });

                            // Update display
                            tagsContainer.innerHTML = '';
                            currentTags.forEach((tag, index) => {
                                const tagChip = document.createElement('span');
                                tagChip.className = 'tag-chip inline-flex items-center gap-1.5 px-3 py-1 bg-sky-100 text-sky-700 rounded-full text-sm';
                                tagChip.innerHTML = `
                                    ${tag}
                                    <button type="button" class="tag-remove text-sky-600 hover:text-sky-800" data-index="${index}">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                `;
                                tagsContainer.appendChild(tagChip);
                            });

                            tagsArrayInput.value = JSON.stringify(currentTags);
                        }
                    }
                }
            } catch (err) {
                showError(err.message);
            } finally {
                resetButton('aiGenerateTags', originalText);
            }
        });
    }
});

//======================= Sticky Header Scroll Detection (Responsive) =======================//
(function () {
    function initStickyHeader() {
        const header = document.getElementById('mainHeader');
        if (!header) return;

        let lastScrollTop = 0;
        let isScrolled = false;

        // Only apply enhanced sticky behavior on mobile/tablet (max-width: 768px)
        function handleScroll() {
            // Check if we're on mobile/tablet
            if (window.innerWidth > 768) return;

            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            // Add scrolled class for shadow effect when scrolled down
            if (scrollTop > 10 && !isScrolled) {
                header.classList.add('scrolled');
                isScrolled = true;
            } else if (scrollTop <= 10 && isScrolled) {
                header.classList.remove('scrolled');
                isScrolled = false;
            }

            lastScrollTop = scrollTop;
        }

        // Throttle scroll event for better performance
        let ticking = false;
        window.addEventListener('scroll', function () {
            if (!ticking) {
                window.requestAnimationFrame(function () {
                    handleScroll();
                    ticking = false;
                });
                ticking = true;
            }
        });

        // Handle window resize
        window.addEventListener('resize', function () {
            if (window.innerWidth > 768) {
                header.classList.remove('scrolled');
                isScrolled = false;
            }
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initStickyHeader);
    } else {
        initStickyHeader();
    }
})();

// ======================== Breaking News Ticker ========================
(function () {
    function initBreakingNewsTicker() {
        const ticker = document.getElementById('breakingNewsTicker');
        if (!ticker) return;

        // Clone the content for seamless infinite scroll
        const originalContent = ticker.innerHTML;
        ticker.innerHTML = originalContent + originalContent;

        // Calculate animation duration based on content width for smooth scrolling
        const updateAnimationDuration = () => {
            const tickerWidth = ticker.scrollWidth / 2;
            // Speed: 60 pixels per second for smooth movement
            const duration = Math.max(tickerWidth / 60, 20);
            ticker.style.animationDuration = `${duration}s`;
        };

        // Wait for content to render before calculating
        setTimeout(() => {
            updateAnimationDuration();
        }, 100);

        // Update on window resize with debounce
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(updateAnimationDuration, 250);
        });

        // Pause on hover for better UX
        ticker.addEventListener('mouseenter', () => {
            ticker.style.animationPlayState = 'paused';
        });

        ticker.addEventListener('mouseleave', () => {
            ticker.style.animationPlayState = 'running';
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBreakingNewsTicker);
    } else {
        initBreakingNewsTicker();
    }
})();

//======================= Mobile Menu Toggle & Back to Top =======================//
(function () {
    function initFooterScripts() {
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', function () {
                mobileMenu.classList.toggle('hidden');
            });
        }

        // Back to top - tampil hanya saat di-scroll
        const backToTop = document.getElementById('footerBackToTop');
        const scrollThreshold = 300;

        function toggleBackToTop() {
            if (backToTop) {
                if (window.scrollY > scrollThreshold) {
                    backToTop.classList.add('is-visible');
                } else {
                    backToTop.classList.remove('is-visible');
                }
            }
        }

        if (backToTop) {
            backToTop.addEventListener('click', function (e) {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
            toggleBackToTop();
            window.addEventListener('scroll', toggleBackToTop, { passive: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFooterScripts);
    } else {
        initFooterScripts();
    }
})();

//======================= Dashboard: Mobile Sidebar Toggle =======================//
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const menuBtn = document.getElementById('mobile-menu-btn');

    if (!sidebar || !overlay || !menuBtn) return;

    function toggleSidebar() {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }

    menuBtn.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);

    // Close sidebar when clicking a link on mobile
    const sidebarLinks = sidebar.querySelectorAll('a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function () {
            if (window.innerWidth < 1024) {
                toggleSidebar();
            }
        });
    });
});

//======================= Dashboard: Profile Modals =======================//
document.addEventListener('DOMContentLoaded', function () {
    // Change Password Modal
    (function () {
        const openBtn = document.getElementById('openChangePasswordModal');
        const modal = document.getElementById('changePasswordModal');
        const backdrop = document.getElementById('changePasswordBackdrop');
        const closeBtn = document.getElementById('closeChangePasswordModal');
        const cancelBtn = document.getElementById('cancelChangePassword');
        const form = document.getElementById('changePasswordForm');

        if (!openBtn || !modal) return;

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            setTimeout(function () {
                const firstInput = modal.querySelector('input[name="current_password"]');
                if (firstInput) firstInput.focus();
            }, 50);
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
            if (form) form.reset();
        }

        openBtn.addEventListener('click', openModal);
        if (backdrop) backdrop.addEventListener('click', closeModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    })();

    // Change Name Modal
    (function () {
        const openBtn = document.getElementById('openChangeNameModal');
        const modal = document.getElementById('changeNameModal');
        const backdrop = document.getElementById('changeNameBackdrop');
        const closeBtn = document.getElementById('closeChangeNameModal');
        const cancelBtn = document.getElementById('cancelChangeName');
        const form = document.getElementById('changeNameForm');

        if (!openBtn || !modal) return;

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            setTimeout(function () {
                const input = modal.querySelector('input[name="fullname"]');
                if (input) input.focus();
            }, 50);
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
            if (form) form.reset();
        }

        openBtn.addEventListener('click', openModal);
        if (backdrop) backdrop.addEventListener('click', closeModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    })();
});

//======================= Dashboard: Category Modals =======================//
document.addEventListener('DOMContentLoaded', function () {
    // Create Category Modal
    (function () {
        const openBtn = document.getElementById('openCreateCategoryModal');
        const openBtnEmpty = document.getElementById('openCreateCategoryModalEmpty');
        const modal = document.getElementById('createCategoryModal');
        const backdrop = document.getElementById('createCategoryBackdrop');
        const closeBtn = document.getElementById('closeCreateCategoryModal');
        const cancelBtn = document.getElementById('cancelCreateCategory');
        const form = document.getElementById('createCategoryForm');

        if (!modal) return;

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            setTimeout(function () {
                const firstInput = modal.querySelector('input[name="name"]');
                if (firstInput) firstInput.focus();
            }, 50);
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
            if (form) form.reset();
        }

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (openBtnEmpty) openBtnEmpty.addEventListener('click', openModal);
        if (backdrop) backdrop.addEventListener('click', closeModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    })();

    // Edit Category Modal
    (function () {
        const modal = document.getElementById('editCategoryModal');
        const backdrop = document.getElementById('editCategoryBackdrop');
        const closeBtn = document.getElementById('closeEditCategoryModal');
        const cancelBtn = document.getElementById('cancelEditCategory');
        const form = document.getElementById('editCategoryForm');
        const editButtons = document.querySelectorAll('.openEditCategoryModal');

        if (!modal) return;

        function openModal(categoryId, categoryName) {
            const idInput = document.getElementById('editCategoryId');
            const nameInput = document.getElementById('editCategoryName');
            if (idInput) idInput.value = categoryId;
            if (nameInput) nameInput.value = categoryName;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            setTimeout(function () {
                if (nameInput) nameInput.focus();
            }, 50);
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
            if (form) form.reset();
        }

        editButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const categoryId = this.getAttribute('data-category-id');
                const categoryName = this.getAttribute('data-category-name');
                openModal(categoryId, categoryName);
            });
        });

        if (backdrop) backdrop.addEventListener('click', closeModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    })();
});

//======================= Dashboard: Tag Modals =======================//
document.addEventListener('DOMContentLoaded', function () {
    // Create Tag Modal
    (function () {
        const openBtn = document.getElementById('openCreateTagModal');
        const openBtnEmpty = document.getElementById('openCreateTagModalEmpty');
        const modal = document.getElementById('createTagModal');
        const backdrop = document.getElementById('createTagBackdrop');
        const closeBtn = document.getElementById('closeCreateTagModal');
        const cancelBtn = document.getElementById('cancelCreateTag');
        const form = document.getElementById('createTagForm');

        if (!modal) return;

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            setTimeout(function () {
                const firstInput = modal.querySelector('input[name="name"]');
                if (firstInput) firstInput.focus();
            }, 50);
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
            if (form) form.reset();
        }

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (openBtnEmpty) openBtnEmpty.addEventListener('click', openModal);
        if (backdrop) backdrop.addEventListener('click', closeModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    })();

    // Edit Tag Modal
    (function () {
        const modal = document.getElementById('editTagModal');
        const backdrop = document.getElementById('editTagBackdrop');
        const closeBtn = document.getElementById('closeEditTagModal');
        const cancelBtn = document.getElementById('cancelEditTag');
        const form = document.getElementById('editTagForm');
        const editButtons = document.querySelectorAll('.openEditTagModal');

        if (!modal) return;

        function openModal(tagId, tagName) {
            const idInput = document.getElementById('editTagId');
            const nameInput = document.getElementById('editTagName');
            if (idInput) idInput.value = tagId;
            if (nameInput) nameInput.value = tagName;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            setTimeout(function () {
                if (nameInput) nameInput.focus();
            }, 50);
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
            if (form) form.reset();
        }

        editButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const tagId = this.getAttribute('data-tag-id');
                const tagName = this.getAttribute('data-tag-name');
                openModal(tagId, tagName);
            });
        });

        if (backdrop) backdrop.addEventListener('click', closeModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    })();
});
