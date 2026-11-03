// Client UI: form checks, likes, comments, toasts, theme, and cover drag.
// Server still validates every request.
(function () {
    'use strict';

    var MAX_BYTES = 2 * 1024 * 1024;
    var ALLOWED = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    // Required fields and password confirmation before submit.
    document.querySelectorAll('form.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var confirmField = form.querySelector('[data-match]');
            if (confirmField) {
                var original = form.querySelector('#' + confirmField.dataset.match);
                confirmField.setCustomValidity(original && original.value !== confirmField.value ? 'Passwords do not match' : '');
            }
            // Treat spaces-only as empty.
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

    // Alert, confirm, and toast dialogs used instead of window.alert.
    window.showAppAlert = function(message, title, type) {
        title = title || 'Notice';
        type = type || 'warning';

        var modalEl = document.getElementById('appAlertModal');
        if (!modalEl) {
            alert(message);
            return;
        }

        var titleEl = document.getElementById('appAlertTitle');
        var msgEl = document.getElementById('appAlertMessage');
        var iconWrap = document.getElementById('appAlertIconWrap');
        var iconEl = document.getElementById('appAlertIcon');

        if (titleEl) titleEl.textContent = title;
        if (msgEl) msgEl.textContent = message;

        if (iconWrap && iconEl) {
            iconWrap.className = 'alert-modal-icon-wrap mx-auto mb-3 ' + type;
            var iconClass = 'bi-exclamation-triangle-fill';
            if (type === 'danger') iconClass = 'bi-x-circle-fill';
            else if (type === 'success') iconClass = 'bi-check-circle-fill';
            else if (type === 'info') iconClass = 'bi-info-circle-fill';
            iconEl.className = 'bi ' + iconClass + ' fs-2';
        }

        var modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInstance.show();
    };

    window.showAppConfirm = function(message, onConfirm, title) {
        title = title || 'Are you sure?';

        var modalEl = document.getElementById('appConfirmModal');
        if (!modalEl) {
            if (window.confirm(message)) { onConfirm(); }
            return;
        }

        var titleEl = document.getElementById('appConfirmTitle');
        var msgEl = document.getElementById('appConfirmMessage');
        var proceedBtn = document.getElementById('appConfirmProceedBtn');

        if (titleEl) titleEl.textContent = title;
        if (msgEl) msgEl.textContent = message;

        var modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);

        var newProceedBtn = proceedBtn.cloneNode(true);
        proceedBtn.parentNode.replaceChild(newProceedBtn, proceedBtn);
        newProceedBtn.addEventListener('click', function() {
            modalInstance.hide();
            onConfirm();
        });

        modalInstance.show();
    };

    window.showAppToast = function(message, type, duration) {
        type = type || 'success';
        duration = duration || 3500;

        var container = document.querySelector('.flash-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'flash-container';
            container.setAttribute('aria-live', 'polite');
            document.body.prepend(container);
        }

        var iconMap = {
            'success': 'bi-check-circle-fill',
            'danger': 'bi-exclamation-triangle-fill',
            'error': 'bi-exclamation-triangle-fill',
            'warning': 'bi-exclamation-circle-fill',
            'info': 'bi-info-circle-fill'
        };

        var toast = document.createElement('div');
        toast.className = 'flash-toast flash-toast-' + type;
        toast.setAttribute('role', 'alert');
        toast.dataset.autoDismiss = duration;

        var content = document.createElement('div');
        content.className = 'flash-toast-content';

        var icon = document.createElement('i');
        icon.className = 'bi ' + (iconMap[type] || 'bi-info-circle-fill') + ' flash-toast-icon';
        content.appendChild(icon);

        var msgDiv = document.createElement('div');
        msgDiv.className = 'flash-toast-message';
        msgDiv.textContent = message;
        content.appendChild(msgDiv);

        var closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'flash-toast-close';
        closeBtn.setAttribute('aria-label', 'Close');
        closeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
        content.appendChild(closeBtn);

        toast.appendChild(content);

        var progress = document.createElement('div');
        progress.className = 'flash-toast-progress';
        var progressBar = document.createElement('div');
        progressBar.className = 'flash-toast-progress-bar';
        progress.appendChild(progressBar);
        toast.appendChild(progress);

        container.appendChild(toast);

        function dismissToast() {
            if (toast.classList.contains('dismissing')) return;
            toast.classList.add('dismissing');
            setTimeout(function() {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 380);
        }

        var timer = setTimeout(dismissToast, duration);
        closeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            clearTimeout(timer);
            dismissToast();
        });
        toast.addEventListener('mouseenter', function() { clearTimeout(timer); });
        toast.addEventListener('mouseleave', function() { timer = setTimeout(dismissToast, 1500); });
    };

    // Confirm before submitting forms with data-confirm (deletes).
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var confirmMsg = form.dataset.confirm || 'Are you sure you want to proceed?';
            showAppConfirm(confirmMsg, function() {
                form.submit();
            });
        });
    });

    // Like/unlike without reloading the page.
    function setLikedState(button, liked) {
        var icon = button.querySelector('i');
        button.classList.toggle('liked', liked);
        button.setAttribute('aria-pressed', liked ? 'true' : 'false');
        icon.className = 'bi ' + (liked ? 'bi-heart-fill' : 'bi-heart') + ' me-1 fs-5';
        if (liked) {
            button.classList.remove('pop');
            void button.offsetWidth; // replay the heart pop animation
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
                    form.submit(); // if AJAX fails, submit the form normally
                })
                .finally(function () { button.disabled = false; });
        });
    });

    // Double-click a post photo to like it.
    document.querySelectorAll('[data-like-target]').forEach(function (media) {
        media.addEventListener('dblclick', function () {
            var card = media.closest('.post, .fb-post');
            var button = card.querySelector('.like-btn');
            var burst = media.querySelector('.heart-burst');

            burst.classList.remove('go');
            void burst.offsetWidth;
            burst.classList.add('go');

            if (!button.classList.contains('liked')) {
                button.click(); // uses the AJAX like handler above
            }
        });
    });

    // Copy the post URL to the clipboard.
    document.querySelectorAll('.share-post-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var url = btn.getAttribute('data-post-url');
            if (url) {
                navigator.clipboard.writeText(url).then(function() {
                    showAppToast('Link copied to clipboard!', 'success');
                }).catch(function() {
                    showAppToast('Failed to copy link.', 'danger');
                });
            }
        });
    });

    // Open the photo in a full-screen viewer with the post cloned beside it.
    document.querySelectorAll('.post-img-zoom').forEach(function(img) {
        img.addEventListener('click', function(e) {
            var modal = document.getElementById('imageModal');
            var modalImg = document.getElementById('modalImage');
            var sidebar = document.getElementById('modalSidebar');
            
            if (modal && modalImg && sidebar) {
                modalImg.src = img.src;

                var postArticle = img.closest('.fb-post');
                if (postArticle) {
                    var clone = postArticle.cloneNode(true);
                    // Drop the photo from the clone so it is not shown twice.
                    var cloneMedia = clone.querySelector('.fb-post-media');
                    if (cloneMedia) cloneMedia.remove();

                    clone.classList.remove('mb-3', 'shadow-sm', 'fb-post');
                    clone.style.border = 'none';
                    clone.style.boxShadow = 'none';
                    clone.style.borderRadius = '0';

                    var commentsCollapse = clone.querySelector('.collapse');
                    if (commentsCollapse) {
                        commentsCollapse.classList.add('show');
                    }

                    sidebar.innerHTML = '';
                    sidebar.appendChild(clone);

                    // Like buttons on the clone need their own AJAX handlers.
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

    // Show a local preview and reject files over 2 MB or the wrong type.
    document.querySelectorAll('input[type="file"]').forEach(function (input) {
        input.addEventListener('change', function () {
            var file = input.files[0];
            var preview = input.dataset.preview ? document.querySelector(input.dataset.preview) : null;

            if (!file) {
                return;
            }
            if (ALLOWED.indexOf(file.type) === -1) {
                input.value = '';
                showAppAlert('Only JPG, PNG, GIF, or WEBP image files are supported.', 'Unsupported File Format', 'warning');
                return;
            }
            if (file.size > MAX_BYTES) {
                input.value = '';
                var sizeMb = (file.size / (1024 * 1024)).toFixed(1);
                showAppAlert('The selected image is ' + sizeMb + ' MB. Photos must be 2 MB or smaller.', 'File Too Large', 'warning');
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

    // Focus the composer when the URL hash is #composer.
    if (window.location.hash === '#composer') {
        var box = document.querySelector('#composer textarea');
        if (box) { box.focus({ preventScroll: false }); }
    }

    // Auto-hide flash toasts; pause the timer while hovered.
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

    // Refresh "time ago" labels every 30 seconds.
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

    // Submit a comment with AJAX and append it without reloading.

    // Build a comment node with textContent so HTML in the comment is not executed.
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
        authorLink.textContent = c.full_name;

        var body = document.createElement('div');
        body.className = 'fb-comment-body';
        body.textContent = c.content;

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

                var commentNode = buildCommentNode(c, baseUrl);

                var postCard = form.closest('.fb-post');
                var list = postCard ? postCard.querySelector('.fb-comments-list') : null;
                if (!list) {
                    var section = postCard ? postCard.querySelector('.fb-comments-section') : null;
                    if (section) {
                        list = document.createElement('div');
                        list.className = 'fb-comments-list';
                        section.insertBefore(list, section.querySelector('.fb-comment-form'));
                    }
                }
                if (list) {
                    list.appendChild(commentNode);
                    commentNode.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }

                var countEl = postCard ? postCard.querySelector('.comment-count') : null;
                if (countEl) {
                    var n = (parseInt(countEl.textContent) || 0) + 1;
                    countEl.textContent = n + ' ' + (n === 1 ? 'comment' : 'comments');
                }

                // Server may rotate CSRF; copy the new token from the page.
                var csrfMeta = document.querySelector('meta[name="csrf-token"]');
                if (csrfMeta) {
                    var csrfInput = form.querySelector('input[name="csrf"]');
                    if (csrfInput) csrfInput.value = csrfMeta.getAttribute('content');
                }

                if (input) input.value = '';
            })
            .catch(function() {
                form.submit();
            })
            .finally(function() {
                if (submitBtn) submitBtn.disabled = false;
            });
        });
    });

    // Thin loading bar at the top of the page.
    var loader = document.createElement('div');
    loader.className = 'page-loader-bar';
    document.body.appendChild(loader);

    // Dark/light theme: save in localStorage and update toggle icons.
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
        document.documentElement.setAttribute('data-bs-theme', theme);
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

    // Drag or slide the profile cover to set its vertical crop.
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
