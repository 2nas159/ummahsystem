/**
 * Enhanced Header JavaScript
 * Handles notifications, search, and other header functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize notifications
    loadNotifications();
    
    // Set up notification refresh interval
    setInterval(loadNotifications, 30000); // Refresh every 30 seconds
    
    // Set up global search
    setupGlobalSearch();
    
    // Set up sidebar functionality
    setupSidebar();
});

/**
 * Load notifications
 */
function loadNotifications() {
    fetch('api/notifications.php?action=get&limit=5')
        .then(response => response.json())
        .then(data => {
            if (data.notifications && data.notifications.length > 0) {
                document.getElementById('notificationsList').innerHTML = data.html;
                updateNotificationBadge(data.unread_count);
            } else {
                document.getElementById('notificationsList').innerHTML = 
                    '<div class="text-center p-3"><i class="fas fa-bell-slash text-muted"></i><p class="mb-0 text-muted">لا توجد إشعارات</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            document.getElementById('notificationsList').innerHTML = 
                '<div class="text-center p-3"><p class="mb-0 text-danger">خطأ في تحميل الإشعارات</p></div>';
        });
}

/**
 * Update notification badge
 */
function updateNotificationBadge(count) {
    const badge = document.querySelector('.notification-badge');
    if (count > 0) {
        if (!badge) {
            const button = document.getElementById('notificationDropdown');
            const span = document.createElement('span');
            span.className = 'notification-badge';
            span.textContent = count;
            button.appendChild(span);
        } else {
            badge.textContent = count;
        }
    } else if (badge) {
        badge.remove();
    }
}

/**
 * Mark notification as read
 */
function markAsRead(notificationId) {
    fetch('api/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=mark_read&notification_id=${notificationId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the unread styling
            const notificationItem = document.querySelector(`[data-id="${notificationId}"]`);
            if (notificationItem) {
                notificationItem.classList.remove('unread');
            }
            
            // Update badge count
            loadNotifications();
        }
    })
    .catch(error => console.error('Error marking notification as read:', error));
}

/**
 * Mark all notifications as read
 */
function markAllAsRead() {
    fetch('api/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=mark_all_read'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotifications();
        }
    })
    .catch(error => console.error('Error marking all notifications as read:', error));
}

/**
 * Setup global search
 */
function setupGlobalSearch() {
    const searchInput = document.getElementById('globalSearch');
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 2) {
                    performGlobalSearch(this.value);
                }
            }, 500);
        });
        
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (this.value.length >= 2) {
                    performGlobalSearch(this.value);
                }
            }
        });
    }
}

/**
 * Perform global search
 */
function performGlobalSearch(query) {
    // Show loading state
    const searchInput = document.getElementById('globalSearch');
    const originalValue = searchInput.value;
    
    // Create search results modal
    const modal = createSearchModal();
    document.body.appendChild(modal);
    
    // Show modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Perform search
    fetch(`api/global_search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            displaySearchResults(data, modal);
        })
        .catch(error => {
            console.error('Search error:', error);
            displaySearchError(modal);
        });
}

/**
 * Create search modal
 */
function createSearchModal() {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">نتائج البحث</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="searchResults">
                        <div class="text-center p-4">
                            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                            <p class="mt-2">جاري البحث...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    return modal;
}

/**
 * Display search results
 */
function displaySearchResults(data, modal) {
    const resultsContainer = modal.querySelector('#searchResults');
    
    if (data.results && data.results.length > 0) {
        let html = '<div class="list-group">';
        
        data.results.forEach(result => {
            html += `
                <a href="${result.url}" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">${result.title}</h6>
                        <small class="text-muted">${result.type}</small>
                    </div>
                    <p class="mb-1">${result.description}</p>
                </a>
            `;
        });
        
        html += '</div>';
        resultsContainer.innerHTML = html;
    } else {
        resultsContainer.innerHTML = `
            <div class="text-center p-4">
                <i class="fas fa-search fa-2x text-muted"></i>
                <p class="mt-2 text-muted">لم يتم العثور على نتائج</p>
            </div>
        `;
    }
}

/**
 * Display search error
 */
function displaySearchError(modal) {
    const resultsContainer = modal.querySelector('#searchResults');
    resultsContainer.innerHTML = `
        <div class="text-center p-4">
            <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
            <p class="mt-2 text-danger">حدث خطأ في البحث</p>
        </div>
    `;
}

/**
 * Setup sidebar functionality
 */
function setupSidebar() {
    // Handle submenu toggles
    document.querySelectorAll('.has-submenu > .nav-link').forEach(function(element) {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            
            const submenu = this.nextElementSibling;
            const isOpen = submenu.classList.contains('show');
            
            // Close all other submenus
            document.querySelectorAll('.submenu.show').forEach(function(openSubmenu) {
                if (openSubmenu !== submenu) {
                    const collapse = new bootstrap.Collapse(openSubmenu);
                    collapse.hide();
                }
            });
            
            // Toggle current submenu
            if (isOpen) {
                const collapse = new bootstrap.Collapse(submenu);
                collapse.hide();
            } else {
                const collapse = new bootstrap.Collapse(submenu);
                collapse.show();
            }
        });
    });
    
    // Add click handlers for mark as read buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('mark-read') || e.target.closest('.mark-read')) {
            const button = e.target.closest('.mark-read');
            const notificationId = button.dataset.id;
            markAsRead(notificationId);
        }
    });
}

/**
 * Show notification toast
 */
function showNotificationToast(title, message, type = 'info') {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <strong>${title}</strong><br>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove toast element after it's hidden
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

/**
 * Create toast container
 */
function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

/**
 * Initialize real-time updates
 */
function initializeRealTimeUpdates() {
    // Check for new notifications every 30 seconds
    setInterval(function() {
        fetch('api/notifications.php?action=unread_count')
            .then(response => response.json())
            .then(data => {
                updateNotificationBadge(data.count);
            })
            .catch(error => console.error('Error checking notification count:', error));
    }, 30000);
}

// Initialize real-time updates
initializeRealTimeUpdates();
