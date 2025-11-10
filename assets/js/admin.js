document.addEventListener('DOMContentLoaded', function () {
    const linksList = document.getElementById('linksList');

    if (!linksList) return;

    const linkItems = linksList.querySelectorAll('.link-item');

    let draggedElement = null;
    let draggedIndex = null;

    // Add drag event listeners to all link items
    linkItems.forEach((item, index) => {
        // Make items draggable
        item.setAttribute('draggable', 'true');

        // Drag start
        item.addEventListener('dragstart', function (e) {
            draggedElement = this;
            draggedIndex = index;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.innerHTML);
        });

        // Drag end
        item.addEventListener('dragend', function (e) {
            this.classList.remove('dragging');

            // Remove all dragover classes
            linkItems.forEach((item) => {
                item.classList.remove('drag-over');
            });

            // Save new order to database
            saveNewOrder();
        });

        // Drag over
        item.addEventListener('dragover', function (e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            if (this === draggedElement) return;

            this.classList.add('drag-over');
            return false;
        });

        // Drag enter
        item.addEventListener('dragenter', function (e) {
            if (this === draggedElement) return;
            this.classList.add('drag-over');
        });

        // Drag leave
        item.addEventListener('dragleave', function (e) {
            this.classList.remove('drag-over');
        });

        // Drop
        item.addEventListener('drop', function (e) {
            e.stopPropagation();
            e.preventDefault();

            if (draggedElement !== this) {
                // Swap elements
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
    });

    // Function to save new order to database
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

        // Send AJAX request to update order
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

    // Toast notification function
    function showToast(message, type = 'success') {
        // Create toast element
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

        // Auto remove after 3 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.5s';
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }
});

// Copy to clipboard function (for dashboard)
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Link berhasil disalin!');
        });
    } else {
        // Fallback for older browsers
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('Link berhasil disalin!');
    }
}
