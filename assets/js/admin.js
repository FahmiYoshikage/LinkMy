document.addEventListener('DOMContentLoaded', function () {
    const linksList = document.getElementById('linksList');
    if (!linksList) return;
    const linkItems = linksList.querySelectorAll('.link-item');
    let draggedElement = null;
    let draggedIndex = null;
    let touchStartY = 0;
    let touchCurrentY = 0;
    let isDraggingTouch = false;
    let touchStartX = 0;
    let touchStartTime = 0;
    let touchHoldTimer = null;
    let isReadyToDrag = false;
    const HOLD_DURATION = 500; // 500ms hold before drag starts

    // Get overlay element
    const dragOverlay = document.getElementById('dragOverlay');

    // Prevent body scroll when dragging
    const preventBodyScroll = (e) => {
        if (isDraggingTouch) {
            e.preventDefault();
        }
    };
    document.body.addEventListener('touchmove', preventBodyScroll, {
        passive: false,
    });
    linkItems.forEach((item, index) => {
        item.setAttribute('draggable', 'true');
        item.addEventListener('dragstart', function (e) {
            draggedElement = this;
            draggedIndex = index;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.innerHTML);
        });
        item.addEventListener('dragend', function (e) {
            this.classList.remove('dragging');
            linkItems.forEach((item) => {
                item.classList.remove('drag-over');
            });
            saveNewOrder();
        });
        item.addEventListener('dragover', function (e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            if (this === draggedElement) return;

            this.classList.add('drag-over');
            return false;
        });
        item.addEventListener('dragenter', function (e) {
            if (this === draggedElement) return;
            this.classList.add('drag-over');
        });
        item.addEventListener('dragleave', function (e) {
            this.classList.remove('drag-over');
        });
        item.addEventListener('drop', function (e) {
            e.stopPropagation();
            e.preventDefault();

            if (draggedElement !== this) {
                const allItems = Array.from(linksList.children);
                const draggedPos = allItems.indexOf(draggedElement);
                const droppedPos = allItems.indexOf(this);

                if (draggedPos < droppedPos) {
                    this.parentNode.insertBefore(
                        draggedElement,
                        this.nextSibling
                    );
                } else {
                    this.parentNode.insertBefore(draggedElement, this);
                }
            }

            this.classList.remove('drag-over');
            return false;
        });

        // Touch Events for Mobile with hold delay
        item.addEventListener(
            'touchstart',
            function (e) {
                draggedElement = this;
                draggedIndex = index;
                touchStartY = e.touches[0].clientY;
                touchStartX = e.touches[0].clientX;
                touchStartTime = Date.now();
                isReadyToDrag = false;

                // Start hold timer
                touchHoldTimer = setTimeout(() => {
                    isReadyToDrag = true;
                    isDraggingTouch = true;
                    this.classList.add('dragging');

                    // Show blur overlay
                    if (dragOverlay) {
                        dragOverlay.classList.add('active');
                    }

                    // Highlight ALL links container
                    if (linksList) {
                        linksList.classList.add('dragging-active');
                    }

                    // Vibrate feedback (if supported)
                    if (navigator.vibrate) {
                        navigator.vibrate(50);
                    }
                }, HOLD_DURATION);

                // Store initial position
                this.style.zIndex = '1000';
                this.style.transition = 'none';

                // Don't prevent default here - allow scrolling initially
                e.stopPropagation();
            },
            { passive: false }
        );

        item.addEventListener(
            'touchmove',
            function (e) {
                // Check if moved too much before hold completed - cancel drag
                if (!isReadyToDrag) {
                    const deltaY = Math.abs(e.touches[0].clientY - touchStartY);
                    const deltaX = Math.abs(e.touches[0].clientX - touchStartX);
                    if (deltaY > 10 || deltaX > 10) {
                        clearTimeout(touchHoldTimer);
                    }
                    return; // Allow normal scrolling
                }

                if (!isDraggingTouch) return;

                touchCurrentY = e.touches[0].clientY;
                const deltaY = touchCurrentY - touchStartY;

                // Visual feedback - smooth transform
                this.style.transform = `translateY(${deltaY}px) scale(1.05)`;
                this.style.opacity = '0.9';
                this.style.boxShadow = '0 10px 30px rgba(0,0,0,0.4)';
                this.style.transition =
                    'transform 0.1s ease, box-shadow 0.1s ease';

                // Hide this element temporarily to get element below
                this.style.visibility = 'hidden';

                // Find element at touch position
                const elementBelow = document.elementFromPoint(
                    e.touches[0].clientX,
                    e.touches[0].clientY
                );

                // Show element again
                this.style.visibility = 'visible';

                if (elementBelow) {
                    const linkItem = elementBelow.closest('.link-item');
                    if (linkItem && linkItem !== this) {
                        // Clear all drag-over classes
                        linkItems.forEach((item) =>
                            item.classList.remove('drag-over')
                        );
                        linkItem.classList.add('drag-over');
                    }
                }

                e.preventDefault();
                e.stopPropagation();
            },
            { passive: false }
        );

        item.addEventListener('touchend', function (e) {
            // Clear hold timer if not ready
            clearTimeout(touchHoldTimer);

            if (!isDraggingTouch || !isReadyToDrag) {
                // Reset states
                isReadyToDrag = false;
                isDraggingTouch = false;
                return;
            }

            isDraggingTouch = false;
            isReadyToDrag = false;
            this.classList.remove('dragging');
            this.style.transform = '';
            this.style.opacity = '';
            this.style.zIndex = '';
            this.style.transition = '';
            this.style.boxShadow = '';
            this.style.visibility = '';

            // Hide blur overlay
            if (dragOverlay) {
                dragOverlay.classList.remove('active');
            }

            // Remove highlight from links container
            if (linksList) {
                linksList.classList.remove('dragging-active');
            }

            // Find the target element
            const elementBelow = document.elementFromPoint(
                e.changedTouches[0].clientX,
                e.changedTouches[0].clientY
            );

            if (elementBelow) {
                const targetItem = elementBelow.closest('.link-item');
                if (targetItem && targetItem !== this) {
                    const allItems = Array.from(linksList.children);
                    const draggedPos = allItems.indexOf(this);
                    const droppedPos = allItems.indexOf(targetItem);

                    if (draggedPos < droppedPos) {
                        targetItem.parentNode.insertBefore(
                            this,
                            targetItem.nextSibling
                        );
                    } else {
                        targetItem.parentNode.insertBefore(this, targetItem);
                    }

                    saveNewOrder();
                }
            }

            // Clear all drag-over classes
            linkItems.forEach((item) => item.classList.remove('drag-over'));

            e.preventDefault();
            e.stopPropagation();
        });

        // Handle touch cancel
        item.addEventListener('touchcancel', function (e) {
            clearTimeout(touchHoldTimer);
            isDraggingTouch = false;
            isReadyToDrag = false;
            this.classList.remove('dragging');
            this.style.transform = '';
            this.style.opacity = '';
            this.style.zIndex = '';
            this.style.transition = '';
            this.style.boxShadow = '';
            this.style.visibility = '';

            if (dragOverlay) {
                dragOverlay.classList.remove('active');
            }

            if (linksList) {
                linksList.classList.remove('dragging-active');
            }

            linkItems.forEach((item) => item.classList.remove('drag-over'));
        });
    });
    function saveNewOrder() {
        const items = linksList.querySelectorAll('.link-item');
        const orderData = [];

        items.forEach((item, index) => {
            const linkId = item.getAttribute('data-id');
            orderData.push({
                id: linkId,
                order: index + 1,
            });
        });
        fetch('dashboard.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body:
                'update_order=1&order_data=' +
                encodeURIComponent(JSON.stringify(orderData)),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    console.log('Order updated successfully');
                    // Optional: Show toast notification
                    showToast('Urutan berhasil diupdate!', 'success');
                }
            })
            .catch((error) => {
                console.error('Error updating order:', error);
                showToast('Gagal mengupdate urutan!', 'error');
            });
    }
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${
            type === 'success' ? 'success' : 'danger'
        } position-fixed top-0 end-0 m-3`;
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <i class="bi bi-${
                type === 'success' ? 'check-circle' : 'exclamation-circle'
            }-fill me-2"></i>
            ${message}
        `;

        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.5s';
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }
});
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Link berhasil disalin!');
        });
    } else {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('Link berhasil disalin!');
    }
}
