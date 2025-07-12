// TaskFlow - Modern Task Management App
// Main JavaScript functionality

class TaskManager {
    constructor() {
        this.tasks = [];
        this.currentFilter = 'all';
        this.taskCounter = 0;
        
        // DOM elements
        this.taskForm = document.getElementById('taskForm');
        this.taskInput = document.getElementById('taskInput');
        this.prioritySelect = document.getElementById('prioritySelect');
        this.tasksContainer = document.getElementById('tasksContainer');
        this.emptyState = document.getElementById('emptyState');
        this.totalTasksEl = document.getElementById('totalTasks');
        this.completedTasksEl = document.getElementById('completedTasks');
        this.clearCompletedBtn = document.getElementById('clearCompleted');
        this.exportTasksBtn = document.getElementById('exportTasks');
        this.filterBtns = document.querySelectorAll('.filter-btn');
        
        this.init();
    }
    
    init() {
        this.loadTasks();
        this.bindEvents();
        this.updateUI();
        this.updateStats();
        
        // Add some demo tasks on first visit
        if (this.tasks.length === 0) {
            this.addDemoTasks();
        }
    }
    
    bindEvents() {
        // Form submission
        this.taskForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.addTask();
        });
        
        // Filter buttons
        this.filterBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.setFilter(e.target.dataset.filter);
            });
        });
        
        // Clear completed tasks
        this.clearCompletedBtn.addEventListener('click', () => {
            this.clearCompletedTasks();
        });
        
        // Export tasks
        this.exportTasksBtn.addEventListener('click', () => {
            this.exportTasks();
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.taskInput.blur();
            }
        });
    }
    
    addTask() {
        const text = this.taskInput.value.trim();
        const priority = this.prioritySelect.value;
        
        if (!text) {
            this.showNotification('Please enter a task description', 'error');
            return;
        }
        
        const task = {
            id: ++this.taskCounter,
            text,
            priority,
            completed: false,
            createdAt: new Date().toISOString(),
            completedAt: null
        };
        
        this.tasks.unshift(task);
        this.saveTasks();
        this.updateUI();
        this.updateStats();
        
        // Reset form
        this.taskInput.value = '';
        this.prioritySelect.value = 'medium';
        this.taskInput.focus();
        
        this.showNotification('Task added successfully!', 'success');
    }
    
    toggleTask(id) {
        const task = this.tasks.find(t => t.id === id);
        if (task) {
            task.completed = !task.completed;
            task.completedAt = task.completed ? new Date().toISOString() : null;
            this.saveTasks();
            this.updateUI();
            this.updateStats();
            
            const status = task.completed ? 'completed' : 'reopened';
            this.showNotification(`Task ${status}!`, 'success');
        }
    }
    
    deleteTask(id) {
        const taskIndex = this.tasks.findIndex(t => t.id === id);
        if (taskIndex > -1) {
            const taskItem = document.querySelector(`[data-task-id="${id}"]`);
            if (taskItem) {
                taskItem.classList.add('removing');
                setTimeout(() => {
                    this.tasks.splice(taskIndex, 1);
                    this.saveTasks();
                    this.updateUI();
                    this.updateStats();
                    this.showNotification('Task deleted!', 'success');
                }, 300);
            }
        }
    }
    
    setFilter(filter) {
        this.currentFilter = filter;
        
        // Update filter buttons
        this.filterBtns.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.filter === filter);
        });
        
        this.updateUI();
    }
    
    getFilteredTasks() {
        switch (this.currentFilter) {
            case 'pending':
                return this.tasks.filter(task => !task.completed);
            case 'completed':
                return this.tasks.filter(task => task.completed);
            case 'high':
                return this.tasks.filter(task => task.priority === 'high');
            default:
                return this.tasks;
        }
    }
    
    updateUI() {
        const filteredTasks = this.getFilteredTasks();
        
        if (filteredTasks.length === 0) {
            this.showEmptyState();
        } else {
            this.hideEmptyState();
            this.renderTasks(filteredTasks);
        }
    }
    
    renderTasks(tasks) {
        this.tasksContainer.innerHTML = tasks.map(task => `
            <div class="task-item ${task.completed ? 'completed' : ''}" data-task-id="${task.id}">
                <div class="task-content">
                    <div class="task-checkbox ${task.completed ? 'checked' : ''}" 
                         onclick="taskManager.toggleTask(${task.id})" 
                         tabindex="0" 
                         role="checkbox" 
                         aria-checked="${task.completed}"
                         onkeydown="if(event.key === 'Enter' || event.key === ' ') { event.preventDefault(); taskManager.toggleTask(${task.id}); }">
                    </div>
                    <span class="task-text">${this.escapeHtml(task.text)}</span>
                    <span class="task-priority ${task.priority}">${task.priority}</span>
                    <div class="task-actions">
                        <button class="task-btn delete-btn" 
                                onclick="taskManager.deleteTask(${task.id})" 
                                aria-label="Delete task">
                            🗑️
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    showEmptyState() {
        this.emptyState.style.display = 'block';
        this.tasksContainer.innerHTML = '';
        
        // Update empty state message based on filter
        const emptyStateMessages = {
            all: { icon: '📝', title: 'No tasks yet', text: 'Add your first task above to get started!' },
            pending: { icon: '✨', title: 'All caught up!', text: 'No pending tasks. Great job!' },
            completed: { icon: '🎉', title: 'No completed tasks', text: 'Complete some tasks to see them here!' },
            high: { icon: '🔥', title: 'No high priority tasks', text: 'No urgent tasks at the moment!' }
        };
        
        const message = emptyStateMessages[this.currentFilter] || emptyStateMessages.all;
        this.emptyState.innerHTML = `
            <div class="empty-icon">${message.icon}</div>
            <h3>${message.title}</h3>
            <p>${message.text}</p>
        `;
    }
    
    hideEmptyState() {
        this.emptyState.style.display = 'none';
    }
    
    updateStats() {
        const totalTasks = this.tasks.length;
        const completedTasks = this.tasks.filter(task => task.completed).length;
        
        this.totalTasksEl.textContent = totalTasks;
        this.completedTasksEl.textContent = completedTasks;
        
        // Update clear completed button state
        this.clearCompletedBtn.disabled = completedTasks === 0;
        this.clearCompletedBtn.style.opacity = completedTasks === 0 ? '0.5' : '1';
    }
    
    clearCompletedTasks() {
        const completedCount = this.tasks.filter(task => task.completed).length;
        
        if (completedCount === 0) {
            this.showNotification('No completed tasks to clear!', 'info');
            return;
        }
        
        // Add removing animation to completed tasks
        const completedTaskElements = document.querySelectorAll('.task-item.completed');
        completedTaskElements.forEach(el => el.classList.add('removing'));
        
        setTimeout(() => {
            this.tasks = this.tasks.filter(task => !task.completed);
            this.saveTasks();
            this.updateUI();
            this.updateStats();
            this.showNotification(`${completedCount} completed task(s) cleared!`, 'success');
        }, 300);
    }
    
    exportTasks() {
        if (this.tasks.length === 0) {
            this.showNotification('No tasks to export!', 'info');
            return;
        }
        
        const exportData = {
            tasks: this.tasks,
            exportDate: new Date().toISOString(),
            totalTasks: this.tasks.length,
            completedTasks: this.tasks.filter(task => task.completed).length
        };
        
        const dataStr = JSON.stringify(exportData, null, 2);
        const dataBlob = new Blob([dataStr], { type: 'application/json' });
        const url = URL.createObjectURL(dataBlob);
        
        const link = document.createElement('a');
        link.href = url;
        link.download = `tasks-${new Date().toISOString().split('T')[0]}.json`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
        
        this.showNotification('Tasks exported successfully!', 'success');
    }
    
    saveTasks() {
        try {
            localStorage.setItem('taskflow-tasks', JSON.stringify(this.tasks));
            localStorage.setItem('taskflow-counter', this.taskCounter.toString());
        } catch (error) {
            console.error('Failed to save tasks:', error);
            this.showNotification('Failed to save tasks to local storage', 'error');
        }
    }
    
    loadTasks() {
        try {
            const savedTasks = localStorage.getItem('taskflow-tasks');
            const savedCounter = localStorage.getItem('taskflow-counter');
            
            if (savedTasks) {
                this.tasks = JSON.parse(savedTasks);
            }
            
            if (savedCounter) {
                this.taskCounter = parseInt(savedCounter);
            }
        } catch (error) {
            console.error('Failed to load tasks:', error);
            this.showNotification('Failed to load tasks from local storage', 'error');
        }
    }
    
    addDemoTasks() {
        const demoTasks = [
            { text: 'Welcome to TaskFlow! 🎉', priority: 'high' },
            { text: 'Try adding a new task above', priority: 'medium' },
            { text: 'Click the checkbox to complete tasks', priority: 'low' },
            { text: 'Use filters to organize your view', priority: 'medium' },
            { text: 'Export your tasks when needed', priority: 'low' }
        ];
        
        demoTasks.forEach(demo => {
            const task = {
                id: ++this.taskCounter,
                text: demo.text,
                priority: demo.priority,
                completed: false,
                createdAt: new Date().toISOString(),
                completedAt: null
            };
            this.tasks.push(task);
        });
        
        // Complete the first task as an example
        if (this.tasks.length > 0) {
            this.tasks[0].completed = true;
            this.tasks[0].completedAt = new Date().toISOString();
        }
        
        this.saveTasks();
    }
    
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        // Add styles
        Object.assign(notification.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            padding: '1rem 1.5rem',
            borderRadius: '0.5rem',
            color: 'white',
            fontWeight: '500',
            fontSize: '0.875rem',
            zIndex: '1000',
            transform: 'translateX(400px)',
            transition: 'transform 0.3s ease-in-out',
            maxWidth: '300px',
            boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1)'
        });
        
        // Set background color based on type
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#6366f1'
        };
        notification.style.backgroundColor = colors[type] || colors.info;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Animate out and remove
        setTimeout(() => {
            notification.style.transform = 'translateX(400px)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize the app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.taskManager = new TaskManager();
    
    // Add some nice loading animation
    document.body.classList.add('loaded');
    
    // Focus the input for better UX
    setTimeout(() => {
        document.getElementById('taskInput').focus();
    }, 500);
});

// Add loading animation styles
const style = document.createElement('style');
style.textContent = `
    body:not(.loaded) .container {
        opacity: 0;
        transform: translateY(20px);
    }
    
    body.loaded .container {
        opacity: 1;
        transform: translateY(0);
        transition: opacity 0.5s ease-out, transform 0.5s ease-out;
    }
    
    .notification {
        user-select: none;
        cursor: default;
    }
    
    .notification:hover {
        transform: translateX(-5px) !important;
    }
`;
document.head.appendChild(style);

// Service Worker registration for offline support (optional)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then(registration => {
            console.log('SW registered: ', registration);
        }).catch(registrationError => {
            console.log('SW registration failed: ', registrationError);
        });
    });
}

// Add keyboard shortcuts
document.addEventListener('keydown', (e) => {
    // Ctrl/Cmd + Enter to add task
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('taskForm').dispatchEvent(new Event('submit'));
    }
    
    // Ctrl/Cmd + 1-4 for filters
    if ((e.ctrlKey || e.metaKey) && e.key >= '1' && e.key <= '4') {
        e.preventDefault();
        const filters = ['all', 'pending', 'completed', 'high'];
        const filterIndex = parseInt(e.key) - 1;
        if (filters[filterIndex]) {
            window.taskManager.setFilter(filters[filterIndex]);
        }
    }
});

// Add touch/swipe support for mobile
let touchStartX = 0;
let touchStartY = 0;

document.addEventListener('touchstart', (e) => {
    touchStartX = e.touches[0].clientX;
    touchStartY = e.touches[0].clientY;
}, { passive: true });

document.addEventListener('touchend', (e) => {
    if (!touchStartX || !touchStartY) return;
    
    const touchEndX = e.changedTouches[0].clientX;
    const touchEndY = e.changedTouches[0].clientY;
    
    const diffX = touchStartX - touchEndX;
    const diffY = touchStartY - touchEndY;
    
    // Only handle horizontal swipes
    if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
        const taskItem = e.target.closest('.task-item');
        if (taskItem) {
            const taskId = parseInt(taskItem.dataset.taskId);
            if (diffX > 0) { // Swipe left to delete
                window.taskManager.deleteTask(taskId);
            } else { // Swipe right to toggle
                window.taskManager.toggleTask(taskId);
            }
        }
    }
    
    touchStartX = 0;
    touchStartY = 0;
}, { passive: true });