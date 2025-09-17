<template>
  <div class="notification-container" v-if="notifications.length > 0">
    <TransitionGroup name="notification" tag="div">
      <div
        v-for="notification in notifications"
        :key="notification.id"
        :class="['notification', `notification--${notification.type}`]"
      >
        <div class="notification-icon">
          <i :class="getIconClass(notification.type)"></i>
        </div>
        <div class="notification-content">
          <p class="notification-message">{{ notification.message }}</p>
        </div>
        <button 
          class="notification-close"
          @click="removeNotification(notification.id)"
        >
          <i class="fas fa-times"></i>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script>
import { ref, reactive } from 'vue'

// Estado global de notificaciones
const notifications = ref([])
let notificationId = 0

export const useNotifications = () => {
  const addNotification = (message, type = 'info', duration = 5000) => {
    const id = ++notificationId
    const notification = {
      id,
      message,
      type,
      duration
    }
    
    notifications.value.push(notification)
    
    if (duration > 0) {
      setTimeout(() => {
        removeNotification(id)
      }, duration)
    }
    
    return id
  }
  
  const removeNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }
  
  const clearNotifications = () => {
    notifications.value = []
  }
  
  return {
    notifications,
    addNotification,
    removeNotification,
    clearNotifications
  }
}

export default {
  name: 'NotificationSystem',
  setup() {
    const { notifications, removeNotification } = useNotifications()
    
    const getIconClass = (type) => {
      const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
      }
      return icons[type] || icons.info
    }
    
    return {
      notifications,
      removeNotification,
      getIconClass
    }
  }
}
</script>

<style scoped>
.notification-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  max-width: 400px;
  pointer-events: none;
}

.notification {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 16px;
  margin-bottom: 12px;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  border-left: 4px solid #3b82f6;
  pointer-events: auto;
  min-width: 320px;
  backdrop-filter: blur(10px);
}

.notification--success {
  border-left-color: #10b981;
}

.notification--success .notification-icon {
  color: #10b981;
}

.notification--error {
  border-left-color: #ef4444;
}

.notification--error .notification-icon {
  color: #ef4444;
}

.notification--warning {
  border-left-color: #f59e0b;
}

.notification--warning .notification-icon {
  color: #f59e0b;
}

.notification--info {
  border-left-color: #3b82f6;
}

.notification--info .notification-icon {
  color: #3b82f6;
}

.notification-icon {
  font-size: 20px;
  margin-top: 2px;
}

.notification-content {
  flex: 1;
}

.notification-message {
  margin: 0;
  font-size: 14px;
  line-height: 1.5;
  color: #374151;
  font-weight: 500;
}

.notification-close {
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: color 0.2s ease;
}

.notification-close:hover {
  color: #374151;
}

/* Animaciones */
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.notification-move {
  transition: transform 0.3s ease;
}

/* Responsive */
@media (max-width: 768px) {
  .notification-container {
    top: 10px;
    right: 10px;
    left: 10px;
    max-width: none;
  }
  
  .notification {
    min-width: auto;
  }
}
</style>