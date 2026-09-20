import Alpine from 'alpinejs';

// Admin JS: confirm dialogs, toasts, bulk selection
document.addEventListener('alpine:init', () => {
    // Toast system
    Alpine.store('toast', {
        message: '',
        type: 'success',
        show(message, type = 'success') {
            this.message = message;
            this.type = type;
            setTimeout(() => { this.message = ''; }, 4000);
        }
    });

    // Confirm dialog
    Alpine.store('confirm', {
        open: false,
        title: '',
        message: '',
        actionLabel: '',
        onConfirm: null,
        confirm(title, message, actionLabel, onConfirm) {
            this.title = title;
            this.message = message;
            this.actionLabel = actionLabel;
            this.onConfirm = onConfirm;
            this.open = true;
        },
        cancel() {
            this.open = false;
            this.onConfirm = null;
        },
        execute() {
            if (this.onConfirm) this.onConfirm();
            this.open = false;
            this.onConfirm = null;
        }
    });
});

// Start Alpine after the alpine:init listener above is registered
window.Alpine = Alpine;
Alpine.start();
