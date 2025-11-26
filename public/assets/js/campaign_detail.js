/**
 * Campaign Detail Page JavaScript
 * Handles: Donation modal, Gallery modal, Comments system
 */

(function() {
    'use strict';

    // Get config from window
    const cfg = window.CAMPAIGN_DETAIL_CONFIG || {};
    const campaignId = cfg.campaignId || 0;
    const csrfName = cfg.csrfTokenName || '';
    const csrfValue = cfg.csrfTokenValue || '';
    const endpoints = cfg.endpoints || {};
    const galleryImages = cfg.galleryImages || [];
    const origin = window.location.origin || '';

    function resolveEndpoint(url, fallbackPath) {
        const fallback = fallbackPath || '/';

        if (url) {
            try {
                const parsed = new URL(url, origin);
                const path = parsed.pathname + (parsed.search || '');
                return origin + path;
            } catch (err) {
                if (url.startsWith('/')) {
                    return origin + url;
                }
                return origin + '/' + url.replace(/^\/+/, '');
            }
        }

        return origin + fallback;
    }

    const donationEndpoint = resolveEndpoint(endpoints.donationCreate, '/donation/create');
    const commentsListEndpoint = resolveEndpoint(endpoints.commentsList, `/discussion/campaign/${campaignId}`);
    const commentCreateEndpoint = resolveEndpoint(endpoints.commentCreate, '/discussion/comment');
    const likeCommentBaseEndpoint = resolveEndpoint(endpoints.likeCommentBase, '/discussion/comment');
    const aminCommentBaseEndpoint = resolveEndpoint(endpoints.aminCommentBase, '/discussion/comment');

    // ============================================
    // DONATION MODAL
    // ============================================
    let donateModalInitialized = false;

    function initDonateModal() {
        if (donateModalInitialized) return;
        donateModalInitialized = true;

        const modal = document.getElementById('donateModal');
        const btnDonate = document.querySelectorAll('[onclick="openDonateModal()"]');
        const btnClose = document.getElementById('btnCloseModal');
        const btnCancel = document.getElementById('btnCancelModal');

        if (!modal) {
            console.error('Modal donasi tidak ditemukan di DOM');
            return;
        }


        // Fungsi buka modal
        window.openDonateModal = function() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.style.opacity = 0;
            modal.style.transition = 'opacity 0.3s ease';
            
            const modalContent = document.getElementById('donateModalContent');
            if (modalContent) {
                modalContent.style.transform = 'scale(0.95) translateY(-10px)';
                modalContent.style.opacity = '0';
            }
            
            requestAnimationFrame(() => {
                modal.style.opacity = 1;
                if (modalContent) {
                    modalContent.style.transform = 'scale(1) translateY(0)';
                    modalContent.style.opacity = '1';
                    modalContent.style.transition = 'transform 0.3s ease-out, opacity 0.3s ease-out';
                }
            });
            
            document.body.style.overflow = 'hidden';
            const hiddenId = document.getElementById('donate_campaign_id');
            if (hiddenId) hiddenId.value = campaignId;
            
            // Reset form and show form container, hide success message
            const formContainer = document.getElementById('donateFormContainer');
            const successModal = document.getElementById('donateSuccessModal');
            const form = document.getElementById('donateForm');
            
            if (formContainer) formContainer.classList.remove('hidden');
            if (successModal) successModal.classList.add('hidden');
            if (form) form.reset();
            
        };

        // Fungsi tutup modal
        window.closeDonateModal = function() {
            modal.style.opacity = 0;
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
                
                // Reset form and show form container, hide success message
                const formContainer = document.getElementById('donateFormContainer');
                const successModal = document.getElementById('donateSuccessModal');
                const form = document.getElementById('donateForm');
                
                if (formContainer) formContainer.classList.remove('hidden');
                if (successModal) successModal.classList.add('hidden');
                if (form) form.reset();
            }, 200);
        };

        // Event listeners
        btnClose?.addEventListener('click', window.closeDonateModal);
        btnCancel?.addEventListener('click', window.closeDonateModal);

        // Klik di luar modal (backdrop)
        modal?.addEventListener('click', e => {
            if (e.target === modal) {
                window.closeDonateModal();
            }
        });

        // Fallback untuk tombol donasi
        btnDonate.forEach(b => {
            b.addEventListener('click', (e) => {
                e.preventDefault();
                window.openDonateModal();
            });
        });

        // Toggle bank account selection based on payment method
        const paymentMethodRadios = document.querySelectorAll('.payment-method-radio');
        const bankAccountContainer = document.getElementById('bankAccountContainer');

        paymentMethodRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (bankAccountContainer) {
                    const selectedType = this.getAttribute('data-type');
                    if (selectedType === 'bank-transfer') {
                        bankAccountContainer.classList.remove('hidden');
                    } else {
                        bankAccountContainer.classList.add('hidden');
                        const bankSelect = bankAccountContainer.querySelector('select[name="bank_account_id"]');
                        if (bankSelect) bankSelect.value = '';
                    }
                }
            });
        });

    }

    // ============================================
    // SUBMIT DONATION
    // ============================================
    async function submitDonation(e) {
        e.preventDefault();
        const form = document.getElementById('donateForm');
        if (!form) return;

        const formData = new FormData(form);
        formData.append(csrfName, csrfValue);

        const submitBtn = document.getElementById('donateSubmit');
        if (!submitBtn) return;

        const original = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Memproses...';

        try {
            const res = await fetch(donationEndpoint, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                // Hide form and show success message in modal
                const formContainer = document.getElementById('donateFormContainer');
                const successModal = document.getElementById('donateSuccessModal');
                const successMsg = document.getElementById('donateSuccessModalMsg');
                const closeSuccessBtn = document.getElementById('btnCloseSuccessModal');
                
                if (formContainer && successModal && successMsg) {
                    formContainer.classList.add('hidden');
                    successModal.classList.remove('hidden');
                    successMsg.textContent = 'Donasi berhasil dibuat. silakan melakukan transfer sesuai instruksi pada pesan Whatsapp yang terkirim';
                    
                    // Close modal when close button is clicked
                    if (closeSuccessBtn) {
                        closeSuccessBtn.onclick = function() {
                            if (typeof window.closeDonateModal === 'function') {
                                window.closeDonateModal();
                            }
                            // Reset form for next donation
                            formContainer.classList.remove('hidden');
                            successModal.classList.add('hidden');
                            form.reset();
                        };
                    }
                }
            } else {
                const errBox = document.getElementById('donateError');
                const msg = document.getElementById('donateErrorMsg');
                if (errBox && msg) {
                    errBox.classList.remove('hidden');
                    msg.textContent = data.message || 'Gagal membuat donasi';
                }
            }
        } catch (err) {
            console.error(err);
            const errBox = document.getElementById('donateError');
            const msg = document.getElementById('donateErrorMsg');
            if (errBox && msg) {
                errBox.classList.remove('hidden');
                msg.textContent = 'Terjadi kesalahan saat membuat donasi';
            }
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = original;
        }
    }

    window.submitDonation = submitDonation;

    // ============================================
    // GALLERY MODAL
    // ============================================
    let currentGalleryIndex = 0;
    let touchStartX = 0;
    let touchEndX = 0;

    function initGalleryModal() {
        if (galleryImages.length === 0) return;

        const galleryModal = document.getElementById('galleryModal');
        const galleryImage = document.getElementById('galleryImage');
        const galleryCounter = document.getElementById('galleryCounter');
        const galleryCloseBtn = document.getElementById('galleryCloseBtn');
        const galleryPrevBtn = document.getElementById('galleryPrevBtn');
        const galleryNextBtn = document.getElementById('galleryNextBtn');

        if (!galleryModal || !galleryImage) return;

        function updateGalleryImage() {
            if (galleryImages[currentGalleryIndex]) {
                galleryImage.src = galleryImages[currentGalleryIndex];
                if (galleryCounter) {
                    galleryCounter.textContent = (currentGalleryIndex + 1) + ' / ' + galleryImages.length;
                }
            }
        }

        function showNextImage() {
            if (currentGalleryIndex < galleryImages.length - 1) {
                currentGalleryIndex++;
            } else {
                currentGalleryIndex = 0;
            }
            updateGalleryImage();
        }

        function showPrevImage() {
            if (currentGalleryIndex > 0) {
                currentGalleryIndex--;
            } else {
                currentGalleryIndex = galleryImages.length - 1;
            }
            updateGalleryImage();
        }

        function openGalleryModal(index) {
            currentGalleryIndex = index;
            updateGalleryImage();
            if (galleryModal) {
                galleryModal.classList.remove('hidden');
                galleryModal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeGalleryModal() {
            if (galleryModal) {
                galleryModal.classList.add('hidden');
                galleryModal.classList.remove('flex');
                document.body.style.overflow = '';
            }
        }

        // Event listeners
        if (galleryCloseBtn) {
            galleryCloseBtn.addEventListener('click', closeGalleryModal);
        }

        if (galleryPrevBtn) {
            galleryPrevBtn.addEventListener('click', showPrevImage);
        }

        if (galleryNextBtn) {
            galleryNextBtn.addEventListener('click', showNextImage);
        }

        // Close on backdrop click
        if (galleryModal) {
            galleryModal.addEventListener('click', function(e) {
                if (e.target === galleryModal) {
                    closeGalleryModal();
                }
            });
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (!galleryModal || galleryModal.classList.contains('hidden')) return;

            if (e.key === 'Escape') {
                closeGalleryModal();
            } else if (e.key === 'ArrowLeft') {
                showPrevImage();
            } else if (e.key === 'ArrowRight') {
                showNextImage();
            }
        });

        // Touch swipe support
        if (galleryModal) {
            galleryModal.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, {
                passive: true
            });

            galleryModal.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            }, {
                passive: true
            });
        }

        function handleSwipe() {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;

            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    // Swipe left - next image
                    showNextImage();
                } else {
                    // Swipe right - prev image
                    showPrevImage();
                }
            }
        }

        window.openGalleryModal = openGalleryModal;
    }

    // ============================================
    // COMMENTS SYSTEM
    // ============================================
    let currentReplyTo = null;

    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Helper untuk cek pinned
    function isCommentPinned(comment) {
        if (comment.is_pinned === undefined || comment.is_pinned === null) return false;
        const pinned = parseInt(comment.is_pinned);
        return pinned === 1 || comment.is_pinned === true || comment.is_pinned === "1";
    }

    // Helper untuk render reply
    function renderReply(reply) {
        const rDate = new Date(reply.created_at);
        const rDateStr = rDate.toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        const name = escapeHtml(reply.commenter_name || 'User');
        const content = escapeHtml(reply.content || '');
        const likeClass = reply.is_liked ? 'text-[#055b16]' : 'text-gray-600';
        const aminClass = reply.is_amined ? 'text-[#055b16]' : 'text-gray-600';
        const likesCount = reply.likes_count || 0;
        const aminsCount = reply.amins_count || 0;
        const replyInitial = (reply.commenter_name || 'U').charAt(0).toUpperCase();
        const replyAvatar = reply.user_avatar || null;

        let html = '<div class="mb-2 sm:mb-3 bg-gray-50 p-2 sm:p-3 rounded-lg">';
        html += '<div class="flex items-start gap-2">';
        if (replyAvatar) {
            html += '<img src="' + escapeHtml(replyAvatar) + '" alt="' + name + '" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full object-cover flex-shrink-0" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">';
            html += '<div class="flex-shrink-0 w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#055b16] flex items-center justify-center text-white text-xs font-semibold" style="display:none;">' + replyInitial + '</div>';
        } else {
            html += '<div class="flex-shrink-0 w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#055b16] flex items-center justify-center text-white text-xs font-semibold">' + replyInitial + '</div>';
        }
        html += '<div class="flex-1 min-w-0">';
        html += '<div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 mb-1">';
        html += '<span class="font-semibold text-xs sm:text-sm text-gray-900">' + name + '</span>';
        html += '<span class="text-xs text-gray-500">' + rDateStr + '</span>';
        html += '</div>';
        html += '<div class="text-xs sm:text-sm text-gray-700 whitespace-pre-wrap break-words overflow-wrap-anywhere">' + content + '</div>';
        html += '<div class="flex flex-wrap items-center gap-2 sm:gap-3 mt-2">';
        html += '<button onclick="likeComment(' + reply.id + ', this)" class="flex items-center gap-1 text-xs ' + likeClass + ' hover:text-[#055b16] transition-colors">';
        html += '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>';
        html += '<span>Suka</span>';
        html += '<span class="like-count">' + likesCount + '</span>';
        html += '</button>';
        html += '<button onclick="aminComment(' + reply.id + ', this)" class="flex items-center gap-1 text-xs ' + aminClass + ' hover:text-[#055b16] transition-colors">';
        html += '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        html += '<span class="amin-count">Aamiin (' + aminsCount + ')</span>';
        html += '</button>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        return html;
    }

    // Load komentar
    function loadComments() {
        const commentsList = document.getElementById('commentsList');
        if (!commentsList) return;

        console.log('Loading comments for campaign ID:', campaignId);
        fetch(commentsListEndpoint)
            .then(res => {
                console.log('Response status:', res.status);
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                console.log('Comments loaded:', data);
                if (data.success && data.data) {
                    console.log('Comments data:', data.data);
                    console.log('Comments count:', data.data.length);
                    renderComments(data.data);
                    updateCommentsCount(data.data);
                } else {
                    console.log('No comments or invalid response');
                    commentsList.innerHTML = '<div class="text-center py-8 text-gray-500">Belum ada komentar. Jadilah yang pertama berkomentar!</div>';
                }
            })
            .catch(err => {
                console.error('Error loading comments:', err);
                commentsList.innerHTML = '<div class="text-center py-8 text-red-500">Gagal memuat komentar. Silakan refresh halaman.</div>';
            });
    }

    // Render komentar
    function renderComments(comments) {
        const commentsList = document.getElementById('commentsList');
        if (!commentsList) return;

        if (!Array.isArray(comments)) {
            commentsList.innerHTML = '<div class="text-center py-8 text-red-500">Format komentar tidak valid.</div>';
            return;
        }

        if (comments.length === 0) {
            commentsList.innerHTML = '<div class="text-center py-8 text-gray-500">Belum ada komentar. Jadilah yang pertama!</div>';
            return;
        }

        // Sort pinned dulu
        const sortedComments = [...comments].sort((a, b) => {
            const aPinned = isCommentPinned(a);
            const bPinned = isCommentPinned(b);
            if (aPinned && !bPinned) return -1;
            if (!aPinned && bPinned) return 1;
            return new Date(b.created_at) - new Date(a.created_at);
        });

        let html = '';

        sortedComments.forEach(comment => {
            const date = new Date(comment.created_at);
            const dateStr = date.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            const pinned = isCommentPinned(comment);
            const pinnedBadge = pinned ? '<span class="text-xs bg-gray-200 text-gray-700 px-2 py-0.5 rounded-full">📌</span>' : '';

            // === KOMENTAR UTAMA ===
            const commentName = escapeHtml(comment.commenter_name || 'User');
            const commentContent = escapeHtml(comment.content || '');
            const commentInitial = (comment.commenter_name || 'U').charAt(0).toUpperCase();
            const likeClass = comment.is_liked ? 'text-[#055b16]' : 'text-gray-600';
            const aminClass = comment.is_amined ? 'text-[#055b16]' : 'text-gray-600';
            const commentAvatar = comment.user_avatar || null;

            html += '<div class="border border-gray-200 rounded-xl p-4 hover:border-gray-300 transition-colors" data-comment-id="' + comment.id + '">';
            html += '<div class="flex items-start gap-3">';
            if (commentAvatar) {
                html += '<img src="' + escapeHtml(commentAvatar) + '" alt="' + commentName + '" class="w-10 h-10 rounded-full object-cover flex-shrink-0" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">';
                html += '<div class="flex-shrink-0 w-10 h-10 rounded-full bg-[#055b16] flex items-center justify-center text-white font-semibold" style="display:none;">' + commentInitial + '</div>';
            } else {
                html += '<div class="flex-shrink-0 w-10 h-10 rounded-full bg-[#055b16] flex items-center justify-center text-white font-semibold">' + commentInitial + '</div>';
            }
            html += '<div class="flex-1">';
            html += '<div class="flex items-center gap-2 mb-1">';
            html += '<span class="font-semibold text-gray-900">' + commentName + '</span>';
            html += pinnedBadge;
            html += '<span class="text-xs text-gray-500">' + dateStr + '</span>';
            html += '</div>';
            html += '<div class="text-gray-700 mb-3 whitespace-pre-wrap">' + commentContent + '</div>';
            html += '<div class="flex items-center gap-4">';
            html += '<button onclick="likeComment(' + comment.id + ', this)" class="flex items-center gap-1 text-sm ' + likeClass + ' hover:text-[#055b16] transition-colors">';
            html += '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>';
            html += '<span>Suka</span>';
            html += '<span class="like-count">' + (comment.likes_count || 0) + '</span>';
            html += '</button>';
            html += '<button onclick="aminComment(' + comment.id + ', this)" class="flex items-center gap-1 text-sm ' + aminClass + ' hover:text-[#055b16] transition-colors">';
            html += '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
            html += '<span class="amin-count">Aamiin (' + (comment.amins_count || 0) + ')</span>';
            html += '</button>';
            html += '<button onclick="replyToComment(' + comment.id + ', \'' + commentName.replace(/'/g, "\\'") + '\')" class="text-sm text-gray-600 hover:text-[#055b16]">Balas</button>';
            html += '</div>';
            
            // Render replies SETELAH tombol action
            if (comment.replies && Array.isArray(comment.replies) && comment.replies.length > 0) {
                html += '<div class="mt-4 ml-4 pl-4 border-l-2 border-gray-200 space-y-3">';
                comment.replies.forEach(reply => {
                    const replyDate = new Date(reply.created_at);
                    const replyDateStr = replyDate.toLocaleDateString('id-ID', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    const replyName = escapeHtml(reply.commenter_name || 'User');
                    const replyContent = escapeHtml(reply.content || '');
                    const replyLikeClass = reply.is_liked ? 'text-[#055b16]' : 'text-gray-600';
                    const replyAminClass = reply.is_amined ? 'text-[#055b16]' : 'text-gray-600';
                    
                    html += '<div class="border border-gray-100 rounded-lg p-3 bg-gray-50">';
                    html += '<div class="flex items-center gap-2 mb-1">';
                    html += '<span class="font-semibold text-sm text-gray-900">' + replyName + '</span>';
                    html += '<span class="text-xs text-gray-500">' + replyDateStr + '</span>';
                    html += '</div>';
                    html += '<div class="text-sm text-gray-700 whitespace-pre-wrap">' + replyContent + '</div>';
                    html += '<div class="flex items-center gap-3 mt-2">';
                    html += '<button onclick="likeComment(' + reply.id + ', this)" class="flex items-center gap-1 text-xs ' + replyLikeClass + ' hover:text-[#055b16] transition-colors">';
                    html += '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>';
                    html += '<span>Suka</span>';
                    html += '<span class="like-count">' + (reply.likes_count || 0) + '</span>';
                    html += '</button>';
                    html += '<button onclick="aminComment(' + reply.id + ', this)" class="flex items-center gap-1 text-xs ' + replyAminClass + ' hover:text-[#055b16] transition-colors">';
                    html += '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                    html += '<span class="amin-count">Aamiin (' + (reply.amins_count || 0) + ')</span>';
                    html += '</button>';
                    html += '</div>';
                    html += '</div>';
                });
                html += '</div>';
            }
            
            html += '</div>';
            html += '</div>';
            html += '</div>';
        });

        // Set HTML - SEDERHANA seperti tenant_campaign_detail
        commentsList.innerHTML = html;

        // Tidak perlu force style lagi - biarkan Tailwind handle dengan space-y-4
    }

    // Update jumlah komentar
    function updateCommentsCount(comments) {
        const countEl = document.getElementById('commentsCount');
        if (countEl) {
            const total = comments.reduce((sum, c) => sum + 1 + (c.replies?.length || 0), 0);
            countEl.textContent = `${total} komentar`;
        }
    }

    // Reply ke komentar
    function replyToComment(commentId, commenterName) {
        currentReplyTo = commentId;
        const replyToIdEl = document.getElementById('reply_to_id');
        const replyToNameEl = document.getElementById('replyToName');
        const replyIndicatorEl = document.getElementById('replyIndicator');
        const commentContentEl = document.getElementById('comment_content');
        const commentFormEl = document.getElementById('commentForm');

        if (replyToIdEl) replyToIdEl.value = commentId;
        if (replyToNameEl) replyToNameEl.textContent = `Membalas ${commenterName}`;
        if (replyIndicatorEl) replyIndicatorEl.classList.remove('hidden');
        if (commentContentEl) commentContentEl.focus();

        // Scroll ke form
        if (commentFormEl) {
            commentFormEl.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }
    }

    window.replyToComment = replyToComment;

    // Cancel reply
    const cancelReplyBtn = document.getElementById('cancelReply');
    if (cancelReplyBtn) {
        cancelReplyBtn.addEventListener('click', function() {
            currentReplyTo = null;
            const replyToIdEl = document.getElementById('reply_to_id');
            const replyIndicatorEl = document.getElementById('replyIndicator');
            if (replyToIdEl) replyToIdEl.value = '';
            if (replyIndicatorEl) replyIndicatorEl.classList.add('hidden');
        });
    }

    // Submit komentar
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);
            const submitBtn = document.getElementById('submitCommentBtn');
            const submitText = document.getElementById('submitCommentText');
            const submitLoading = document.getElementById('submitCommentLoading');

            // Pastikan parent_id hanya dikirim jika ada reply aktif
            const replyToIdEl = document.getElementById('reply_to_id');
            const replyToId = replyToIdEl ? replyToIdEl.value : '';
            if (!replyToId || replyToId.trim() === '' || !currentReplyTo) {
                formData.delete('parent_id');
            } else {
                formData.set('parent_id', currentReplyTo);
            }

            // Disable button
            if (submitBtn) submitBtn.disabled = true;
            if (submitText) submitText.classList.add('hidden');
            if (submitLoading) submitLoading.classList.remove('hidden');

        fetch(commentCreateEndpoint, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => {
                    console.log('Response status:', res.status);
                    if (!res.ok) {
                        return res.json().then(data => {
                            console.error('Error response:', data);
                            throw new Error(data.message || 'Gagal mengirim komentar');
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    console.log('Comment submitted:', data);
                    if (data.success) {
                        // Reset form dan clear reply state
                        form.reset();
                        const replyToIdEl = document.getElementById('reply_to_id');
                        const replyIndicatorEl = document.getElementById('replyIndicator');
                        if (replyToIdEl) replyToIdEl.value = '';
                        if (replyIndicatorEl) replyIndicatorEl.classList.add('hidden');
                        currentReplyTo = null;

                        // Show success message
                        const successMsg = document.createElement('div');
                        successMsg.className = 'fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                        successMsg.textContent = 'Komentar berhasil dikirim! Komentar Anda sedang menunggu moderasi.';
                        document.body.appendChild(successMsg);
                        setTimeout(() => successMsg.remove(), 5000);
                    } else {
                        throw new Error(data.message || 'Gagal mengirim komentar');
                    }
                })
                .catch(err => {
                    console.error('Error submitting comment:', err);
                    alert(err.message || 'Terjadi kesalahan. Silakan coba lagi.');
                })
                .finally(() => {
                    // Enable button
                    if (submitBtn) submitBtn.disabled = false;
                    if (submitText) submitText.classList.remove('hidden');
                    if (submitLoading) submitLoading.classList.add('hidden');
                });
        });
    }

    // Like komentar
    function likeComment(commentId, button) {
        console.log('Like clicked - Comment ID:', commentId);

        fetch(`${likeCommentBaseEndpoint}/${commentId}/like`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => {
                        throw new Error(err.message || 'Network response was not ok');
                    });
                }
                return res.json();
            })
            .then(data => {
                console.log('Like response:', data);
                if (data.success && data.data) {
                    const countEl = button.querySelector('.like-count');
                    const likesCount = data.data.likes_count ?? data.likes_count ?? 0;
                    const isLiked = data.data.liked ?? data.liked ?? false;

                    console.log('Count element:', countEl, 'Current count:', countEl?.textContent, 'New count:', likesCount);

                    if (countEl && likesCount !== undefined) {
                        countEl.textContent = likesCount;
                        console.log('Count updated to:', countEl.textContent);
                    }

                    // Update button state based on server response
                    if (isLiked) {
                        button.classList.add('text-[#055b16]');
                        button.classList.remove('text-gray-600');
                        console.log('Button state: LIKED');
                    } else {
                        button.classList.remove('text-[#055b16]');
                        button.classList.add('text-gray-600');
                        console.log('Button state: NOT LIKED');
                    }
                } else {
                    console.error('Like error:', data.message);
                }
            })
            .catch(err => {
                console.error('Error liking comment:', err);
            });
    }

    window.likeComment = likeComment;

    // Aamiin komentar
    function aminComment(commentId, button) {
        console.log('Aamiin clicked - Comment ID:', commentId);

        fetch(`${aminCommentBaseEndpoint}/${commentId}/amin`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => {
                        throw new Error(err.message || 'Network response was not ok');
                    });
                }
                return res.json();
            })
            .then(data => {
                console.log('Aamiin response:', data);
                if (data.success && data.data) {
                    const countEl = button.querySelector('.amin-count');
                    const aminsCount = data.data.amins_count ?? data.amins_count ?? 0;
                    const isAmined = data.data.amined ?? data.amined ?? false;

                    console.log('Count element:', countEl, 'Current count:', countEl?.textContent, 'New count:', aminsCount);

                    if (countEl && aminsCount !== undefined) {
                        countEl.textContent = `Aamiin (${aminsCount})`;
                        console.log('Count updated to:', countEl.textContent);
                    }

                    // Update button state based on server response
                    if (isAmined) {
                        button.classList.add('text-[#055b16]');
                        button.classList.remove('text-gray-600');
                        console.log('Button state: AMINED');
                    } else {
                        button.classList.remove('text-[#055b16]');
                        button.classList.add('text-gray-600');
                        console.log('Button state: NOT AMINED');
                    }
                } else {
                    console.error('Aamiin error:', data.message);
                }
            })
            .catch(err => {
                console.error('Error amining comment:', err);
            });
    }

    window.aminComment = aminComment;

    // ============================================
    // INITIALIZATION
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        initDonateModal();
        initGalleryModal();

        // Load comments on page load
        if (campaignId > 0) {
            loadComments();
        }
    });

})();

