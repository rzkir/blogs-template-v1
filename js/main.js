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

    // Custom handler: gambar di konten dari Pexels (prompt = kata kunci cari)
    var toolbar = quill.getModule('toolbar');
    if (toolbar) {
        toolbar.addHandler('image', function () {
            var range = quill.getSelection(true);
            if (!range) range = { index: quill.getLength(), length: 0 };
            var keyword = (prompt('Kata kunci untuk cari gambar di Pexels (contoh: sunset, nature, coffee):') || '').trim();
            if (!keyword) return;

            fetch('/api/pexels-image.php?q=' + encodeURIComponent(keyword))
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.url) {
                        quill.insertEmbed(range.index, 'image', data.url, 'user');
                        quill.setSelection(range.index + 1);
                    } else {
                        alert(data.error || 'Gagal mengambil gambar dari Pexels.');
                    }
                })
                .catch(function () {
                    alert('Gagal menghubungi server. Periksa koneksi atau API key Pexels di config/pexels.php.');
                });
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
                        quill.root.innerHTML = c;
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
                    quill.root.innerHTML = c;
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
                            if (quill) quill.root.innerHTML = content;
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
});
