<div class="dropdown me-3">
    <button class="btn btn-outline-secondary position-relative" type="button" data-bs-toggle="dropdown" id="notificationDropdown">
        <i class="fas fa-bell"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationBadge" style="display: none;">
            0
        </span>
    </button>
    <div class="dropdown-menu dropdown-menu-end notification-dropdown" style="width: 350px; max-height: 400px; overflow-y: auto;">
        <div class="dropdown-header d-flex justify-content-between align-items-center">
            <span>Notifications</span>
            <button class="btn btn-sm btn-outline-primary" onclick="markAllAsRead()" id="markAllReadBtn" style="display: none;">
                Tout marquer
            </button>
        </div>
        <div class="dropdown-divider"></div>
        <div id="notificationsList">
            <div class="dropdown-item-text text-center text-muted">
                Chargement...
            </div>
        </div>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-center" href="{{ route('notifications.index') }}">
            <i class="fas fa-list me-1"></i>Voir toutes les notifications
        </a>
    </div>
</div>

<script>
let notificationDropdown;
let refreshInterval;

document.addEventListener('DOMContentLoaded', function() {
    notificationDropdown = document.getElementById('notificationDropdown');
    
    // Initial load
    loadNotifications();
    
    // Refresh every 30 seconds
    refreshInterval = setInterval(loadNotifications, 30000);
    
    // Load when dropdown is opened
    notificationDropdown.addEventListener('show.bs.dropdown', loadNotifications);
});

function loadNotifications() {
    fetch('{{ route("notifications.recent") }}')
        .then(response => response.json())
        .then(data => {
            updateNotificationBadge(data.unread_count);
            updateNotificationsList(data.notifications);
            updateMarkAllButton(data.unread_count);
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
        });
}

function updateNotificationBadge(count) {
    const badge = document.getElementById('notificationBadge');
    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.style.display = 'block';
    } else {
        badge.style.display = 'none';
    }
}

function updateNotificationsList(notifications) {
    const list = document.getElementById('notificationsList');
    
    if (notifications.length === 0) {
        list.innerHTML = '<div class="dropdown-item-text text-center text-muted">Aucune notification récente</div>';
        return;
    }
    
    list.innerHTML = notifications.map(notification => `
        <div class="dropdown-item ${!notification.is_read ? 'bg-light' : ''}" style="white-space: normal;">
            <div class="d-flex align-items-start">
                <i class="${notification.type_icon} me-2 text-${notification.priority === 'high' ? 'danger' : (notification.priority === 'medium' ? 'warning' : 'primary')}"></i>
                <div class="flex-grow-1">
                    <div class="fw-bold ${!notification.is_read ? 'text-primary' : ''}">${notification.title}</div>
                    <div class="small text-muted">${notification.message}</div>
                    <div class="small text-muted">
                        <i class="fas fa-clock me-1"></i>${notification.created_at}
                        ${!notification.is_read ? '<span class="badge bg-primary ms-1">Nouveau</span>' : ''}
                    </div>
                </div>
                ${!notification.is_read ? `
                    <button class="btn btn-sm btn-outline-secondary ms-2" onclick="markAsRead(${notification.id})" title="Marquer comme lu">
                        <i class="fas fa-check"></i>
                    </button>
                ` : ''}
            </div>
            ${notification.action_url ? `
                <div class="mt-2">
                    <a href="${notification.action_url}" class="btn btn-sm btn-outline-primary">Voir détails</a>
                </div>
            ` : ''}
        </div>
    `).join('');
}

function updateMarkAllButton(unreadCount) {
    const button = document.getElementById('markAllReadBtn');
    if (unreadCount > 0) {
        button.style.display = 'block';
    } else {
        button.style.display = 'none';
    }
}

function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/mark-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotifications(); // Refresh the list
        }
    })
    .catch(error => console.error('Error:', error));
}

function markAllAsRead() {
    fetch('{{ route("notifications.mark-all-read") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotifications(); // Refresh the list
        }
    })
    .catch(error => console.error('Error:', error));
}

// Clean up interval when page unloads
window.addEventListener('beforeunload', function() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>

<style>
.notification-dropdown {
    border: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.notification-dropdown .dropdown-item {
    border-bottom: 1px solid #f8f9fa;
    padding: 12px 16px;
}

.notification-dropdown .dropdown-item:hover {
    background-color: #f8f9fa;
}

.notification-dropdown .dropdown-item:last-child {
    border-bottom: none;
}
</style>