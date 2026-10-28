/**
 * LyfeFeed front-end behaviour (vanilla JS):
 *  1. Client-side form validation (Bootstrap styles)
 *  2. Confirm dialogs for destructive actions
 *  3. AJAX like / unlike (heart button) + double-tap a photo to like
 *  4. Image preview + size/type check before upload
 *  5. Focus the composer when arriving via #composer
 *
 * Client-side checks are a convenience only; the server validates everything again.
 */
(function () {
    'use strict';

    var MAX_BYTES = 2 * 1024 * 1024;
    var ALLOWED = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    /* 1. Form validation ------------------------------------------------ */
    document.querySelectorAll('form.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var confirmField = form.querySelector('[data-match]');
            if (confirmField) {
                var original = form.querySelector('#' + confirmField.dataset.match);
                confirmField.setCustomValidity(original && original.value !== confirmField.value ? 'Passwords do not match' : '');
            }
            // Whitespace-only text is not allowed in required text fields
            form.querySelectorAll('input[type="text"][required], textarea[required]').forEach(function (field) {
                field.setCustomValidity(field.value.trim() === '' ? 'Required' : '');
            });

            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    /* 2. Confirm before delete ------------------------------------------ */
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    /* 3. AJAX like toggle ----------------------------------------------- */
    function setLikedState(button, liked) {
        var icon = button.querySelector('i');
        button.classList.toggle('liked', liked);
        button.setAttribute('aria-pressed', liked ? 'true' : 'false');
        icon.className = 'bi ' + (liked ? 'bi-heart-fill' : 'bi-heart') + ' me-1 fs-5';
        if (liked) {
            button.classList.remove('pop');
            void button.offsetWidth;          // restart the CSS animation
            button.classList.add('pop');
        }
    }

    document.querySelectorAll('form.like-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var button = form.querySelector('.like-btn');
            var card = form.closest('.post, .fb-post');
            button.disabled = true;

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (!data.ok) { throw new Error(data.error || 'Request failed'); }
                    setLikedState(button, data.liked);
                    var countEl = card.querySelector('.like-count');
                    if (countEl) { countEl.textContent = data.count; }
                    var wordEl = card.querySelector('.like-word');
                    if (wordEl) { wordEl.textContent = data.count === 1 ? 'like' : 'likes'; }
                })
                .catch(function () {
                    form.submit();   // graceful fallback: normal form post
                })
                .finally(function () { button.disabled = false; });
        });
    });

    // Double-click / double-tap a photo to like it
    document.querySelectorAll('[data-like-target]').forEach(function (media) {
        media.addEventListener('dblclick', function () {
            var card = media.closest('.post, .fb-post');
            var button = card.querySelector('.like-btn');
            var burst = media.querySelector('.heart-burst');

            burst.classList.remove('go');
            void burst.offsetWidth;
            burst.classList.add('go');

            if (!button.classList.contains('liked')) {
                button.click();      // submits the like form via the AJAX handler above
            }
        });
    });

    /* Share post button */
    document.querySelectorAll('.share-post-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var url = btn.getAttribute('data-post-url');
            if (url) {
                navigator.clipboard.writeText(url).then(function() {
                    alert('Link copied to clipboard!');
                });
            }
        });
    });

    /* Image Lightbox / Zoom Facebook Mode */
    document.querySelectorAll('.post-img-zoom').forEach(function(img) {
        img.addEventListener('click', function(e) {
            var modal = document.getElementById('imageModal');
            var modalImg = document.getElementById('modalImage');
            var sidebar = document.getElementById('modalSidebar');
            
            if (modal && modalImg && sidebar) {
                // Set image
                modalImg.src = img.src;
                
                // Clone the parent post
                var postArticle = img.closest('.fb-post');
                if (postArticle) {
                    var clone = postArticle.cloneNode(true);
                    // Remove the image from the cloned post so it doesn't show twice
                    var cloneMedia = clone.querySelector('.fb-post-media');
                    if (cloneMedia) cloneMedia.remove();
                    
                    // Style the cloned card to fit seamlessly in the sidebar
                    clone.classList.remove('mb-3', 'shadow-sm', 'fb-post');
                    clone.style.border = 'none';
                    clone.style.boxShadow = 'none';
                    clone.style.borderRadius = '0';
                    
                    // Expand comments to show them
                    var commentsCollapse = clone.querySelector('.collapse');
                    if (commentsCollapse) {
                        commentsCollapse.classList.add('show');
                    }
                    
                    // Inject clone
                    sidebar.innerHTML = '';
                    sidebar.appendChild(clone);
                    
                    // Re-bind like form in the clone
                    clone.querySelectorAll('form.like-form').forEach(function (form) {
                        form.addEventListener('submit', function (event) {
                            event.preventDefault();
                            var btn = form.querySelector('.like-btn');
                            btn.disabled = true;
                            fetch(form.action, {
                                method: 'POST', body: new FormData(form),
                                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                                credentials: 'same-origin'
                            }).then(res => res.json()).then(data => {
                                if (!data.ok) throw new Error();
                                setLikedState(btn, data.liked);
                                var countEl = clone.querySelector('.like-count');
                                if (countEl) countEl.textContent = data.count;
                            }).catch(() => form.submit()).finally(() => btn.disabled = false);
                        });
                    });
                }
                
                var bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            }
        });
    });

    /* 4. Image preview & validation ------------------------------------- */
    document.querySelectorAll('input[type="file"]').forEach(function (input) {
        input.addEventListener('change', function () {
            var file = input.files[0];
            var preview = input.dataset.preview ? document.querySelector(input.dataset.preview) : null;

            if (!file) {
                return;
            }
            if (ALLOWED.indexOf(file.type) === -1) {
                alert('Only JPG, PNG, GIF, or WEBP images are allowed.');
                input.value = '';
                return;
            }
            if (file.size > MAX_BYTES) {
                alert('Image must be 2 MB or smaller.');
                input.value = '';
                return;
            }
            if (preview) {
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('d-none');
                var container = document.getElementById('previewContainer');
                if (container) {
                    container.classList.remove('d-none');
                }
            }
        });
    });

    var clearBtn = document.getElementById('clearPreviewBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            var fileInput = document.getElementById('composerFileInput');
            var preview = document.getElementById('postPreview');
            var container = document.getElementById('previewContainer');
            if (fileInput) { fileInput.value = ''; }
            if (preview) {
                preview.src = '';
                preview.classList.add('d-none');
            }
            if (container) { container.classList.add('d-none'); }
        });
    }

    /* 5. Jump to composer ----------------------------------------------- */
    if (window.location.hash === '#composer') {
        var box = document.querySelector('#composer textarea');
        if (box) { box.focus({ preventScroll: false }); }
    }

    /* 6. Flash Toasts: Animated auto-dismiss ---------------------------- */
    document.querySelectorAll('.flash-toast[data-auto-dismiss]').forEach(function (toast) {
        var duration = parseInt(toast.dataset.autoDismiss, 10) || 3500;
        var remaining = duration;
        var startTime = Date.now();
        var timerId = null;
        var isPaused = false;

        function dismiss() {
            if (toast.classList.contains('dismissing')) return;
            toast.classList.add('dismissing');
            setTimeout(function () {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 380);
        }

        function startTimer() {
            startTime = Date.now();
            timerId = setTimeout(dismiss, remaining);
        }

        function pauseTimer() {
            clearTimeout(timerId);
            remaining -= (Date.now() - startTime);
            if (remaining < 0) remaining = 0;
            isPaused = true;
        }

        function resumeTimer() {
            if (isPaused) {
                isPaused = false;
                startTimer();
            }
        }

        startTimer();

        toast.addEventListener('mouseenter', pauseTimer);
        toast.addEventListener('mouseleave', resumeTimer);

        var closeBtn = toast.querySelector('.flash-toast-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                dismiss();
            });
        }
    });

    /* 7. Real-time post relative timestamp updates ---------------------- */
    function formatTimeAgo(dateStr) {
        var timestamp = new Date(dateStr).getTime();
        if (isNaN(timestamp)) return null;

        var diff = Math.max(0, Math.floor((Date.now() - timestamp) / 1000));
        if (diff < 45) return 'Just now';
        if (diff < 90) return '1 min ago';
        if (diff < 3600) return Math.floor(diff / 60) + ' mins ago';
        if (diff < 7200) return '1 hr ago';
        if (diff < 86400) return Math.floor(diff / 3600) + ' hrs ago';
        if (diff < 172800) return 'Yesterday';
        if (diff < 604800) return Math.floor(diff / 86400) + ' days ago';
        return null;
    }

    function updateRelativeTimes() {
        document.querySelectorAll('.js-time-ago[datetime]').forEach(function (el) {
            var dt = el.getAttribute('datetime');
            var formatted = formatTimeAgo(dt);
            if (formatted) {
                el.textContent = formatted;
            }
        });
    }

    updateRelativeTimes();
    setInterval(updateRelativeTimes, 30000);

    /* 8. AJAX Comment Submit (no page jump) ----------------------------- */

    /** Build a comment DOM node safely (no innerHTML, no XSS risk). */
    function buildCommentNode(c, baseUrl) {
        var avatarSrc = c.profile_image
            ? baseUrl + '/assets/uploads/' + encodeURIComponent(c.profile_image)
            : baseUrl + '/assets/img/default-avatar.svg';

        var wrapper = document.createElement('div');
        wrapper.className = 'fb-comment-item d-flex gap-2 mb-2';

        var avatarImg = document.createElement('img');
        avatarImg.src = avatarSrc;
        avatarImg.alt = '';
        avatarImg.className = 'avatar avatar-xs flex-shrink-0';

        var innerDiv = document.createElement('div');
        innerDiv.className = 'min-w-0 flex-grow-1';

        var bubble = document.createElement('div');
        bubble.className = 'fb-comment-bubble';

        var authorLink = document.createElement('a');
        authorLink.className = 'fb-comment-author';
        authorLink.href = '#';
        authorLink.textContent = c.full_name;  // textContent = safe, no XSS

        var body = document.createElement('div');
        body.className = 'fb-comment-body';
        body.textContent = c.content;           // textContent = safe, no XSS

        bubble.appendChild(authorLink);
        bubble.appendChild(body);

        var meta = document.createElement('div');
        meta.className = 'fb-comment-meta ps-2 pt-1';
        var timeSpan = document.createElement('span');
        timeSpan.className = 'js-time-ago';
        timeSpan.textContent = 'Just now';
        meta.appendChild(timeSpan);

        innerDiv.appendChild(bubble);
        innerDiv.appendChild(meta);

        wrapper.appendChild(avatarImg);
        wrapper.appendChild(innerDiv);

        return wrapper;
    }

    document.querySelectorAll('.fb-comment-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var input = form.querySelector('input[name="content"]');
            var content = input ? input.value.trim() : '';
            if (!content) return;

            var submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (!data.ok || !data.comment) throw new Error('Failed');
                var c = data.comment;
                var baseUrl = document.body.getAttribute('data-base-url') || '';

                // Build comment DOM safely (no innerHTML)
                var commentNode = buildCommentNode(c, baseUrl);

                // Insert into comment list
                var postCard = form.closest('.fb-post');
                var list = postCard ? postCard.querySelector('.fb-comments-list') : null;
                if (!list) {
                    // Create the list if no comments yet
                    var section = postCard ? postCard.querySelector('.fb-comments-section') : null;
                    if (section) {
                        list = document.createElement('div');
                        list.className = 'fb-comments-list';
                        section.insertBefore(list, section.querySelector('.fb-comment-form'));
                    }
                }
                if (list) {
                    list.appendChild(commentNode);
                    // Scroll the new comment into view
                    commentNode.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }

                // Update comment count
                var countEl = postCard ? postCard.querySelector('.comment-count') : null;
                if (countEl) {
                    var n = (parseInt(countEl.textContent) || 0) + 1;
                    countEl.textContent = n + ' ' + (n === 1 ? 'comment' : 'comments');
                }

                // Update CSRF token for next submission (token rotated server-side)
                var csrfMeta = document.querySelector('meta[name="csrf-token"]');
                if (csrfMeta) {
                    var csrfInput = form.querySelector('input[name="csrf"]');
                    if (csrfInput) csrfInput.value = csrfMeta.getAttribute('content');
                }

                // Clear input
                if (input) input.value = '';
            })
            .catch(function() {
                // Fallback: normal submit
                form.submit();
            })
            .finally(function() {
                if (submitBtn) submitBtn.disabled = false;
            });
        });
    });

    /* 9. Page Transition Loader ----------------------------------------- */
    var loader = document.createElement('div');
    loader.className = 'page-loader-bar';
    document.body.appendChild(loader);

    /* 10. Dark Mode Theme Toggle --------------------------------------- */
    function getTheme() {
        return document.documentElement.getAttribute('data-theme') || localStorage.getItem('theme') || 'light';
    }

    function updateThemeUI(theme) {
        var isDark = theme === 'dark';
        document.querySelectorAll('.theme-toggle-btn i').forEach(function(icon) {
            icon.className = 'bi ' + (isDark ? 'bi-sun-fill text-warning' : 'bi-moon-stars');
        });
        var badge = document.getElementById('themeStatusBadge');
        if (badge) {
            badge.textContent = isDark ? 'On' : 'Off';
            badge.className = 'badge ' + (isDark ? 'bg-primary' : 'bg-secondary-subtle text-secondary');
        }
    }

    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        updateThemeUI(theme);
    }

    function toggleTheme() {
        var current = getTheme();
        setTheme(current === 'dark' ? 'light' : 'dark');
    }

    document.querySelectorAll('.theme-toggle-btn, #themeToggleDropdownBtn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleTheme();
        });
    });

    updateThemeUI(getTheme());

    /* 11. Banner Drag to Reposition ------------------------------------ */
    var coverBox = document.getElementById('coverRepositionContainer');
    var coverImg = document.getElementById('coverPreview');
    var coverSlider = document.getElementById('coverPositionSlider');
    var coverInput = document.getElementById('cover_position');
    var coverLabel = document.getElementById('coverPosLabel');
    var coverFileInput = document.getElementById('cover_image');
    var coverSliderWrap = document.getElementById('coverSliderWrap');
    var dragBadge = document.getElementById('dragBadge');
    var noCoverPlaceholder = document.getElementById('noCoverPlaceholder');

    if (coverBox && coverImg && coverInput) {
        var isDragging = false;
        var startY = 0;
        var startPos = parseInt(coverInput.value, 10) || 50;

        function updatePosition(pct) {
            pct = Math.max(0, Math.min(100, Math.round(pct)));
            coverInput.value = pct;
            if (coverSlider) coverSlider.value = pct;
            if (coverLabel) coverLabel.textContent = pct + '%';
            coverImg.style.objectPosition = 'center ' + pct + '%';
        }

        if (coverSlider) {
            coverSlider.addEventListener('input', function() {
                updatePosition(parseInt(this.value, 10));
            });
        }

        coverBox.addEventListener('mousedown', function(e) {
            if (coverImg.classList.contains('d-none')) return;
            isDragging = true;
            startY = e.clientY;
            startPos = parseInt(coverInput.value, 10) || 50;
            coverBox.style.cursor = 'grabbing';
            e.preventDefault();
        });

        window.addEventListener('mousemove', function(e) {
            if (!isDragging) return;
            var deltaY = e.clientY - startY;
            var boxHeight = coverBox.offsetHeight || 180;
            var newPos = startPos - (deltaY / boxHeight) * 100;
            updatePosition(newPos);
        });

        window.addEventListener('mouseup', function() {
            if (isDragging) {
                isDragging = false;
                coverBox.style.cursor = 'grab';
            }
        });

        coverBox.addEventListener('touchstart', function(e) {
            if (coverImg.classList.contains('d-none')) return;
            if (e.touches.length === 1) {
                isDragging = true;
                startY = e.touches[0].clientY;
                startPos = parseInt(coverInput.value, 10) || 50;
            }
        }, { passive: true });

        window.addEventListener('touchmove', function(e) {
            if (!isDragging || e.touches.length !== 1) return;
            var deltaY = e.touches[0].clientY - startY;
            var boxHeight = coverBox.offsetHeight || 180;
            var newPos = startPos - (deltaY / boxHeight) * 100;
            updatePosition(newPos);
        }, { passive: true });

        window.addEventListener('touchend', function() {
            isDragging = false;
        });

        if (coverFileInput) {
            coverFileInput.addEventListener('change', function() {
                var file = this.files && this.files[0];
                if (file && file.type.startsWith('image/')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        coverImg.src = e.target.result;
                        coverImg.classList.remove('d-none');
                        if (noCoverPlaceholder) noCoverPlaceholder.classList.add('d-none');
                        if (dragBadge) dragBadge.classList.remove('d-none');
                        if (coverSliderWrap) coverSliderWrap.classList.remove('d-none');
                        coverBox.style.cursor = 'grab';
                        updatePosition(50);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    }
})();
