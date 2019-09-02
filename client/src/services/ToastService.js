import { EventEmitter } from 'events';

export default class ToastService {
    /**
     * @var Toast[]
     */
    toasts = [];

    eventEmitter = new EventEmitter();

    /**
     * Add toast
     *
     * @param {string} type
     * @param {string} message
     */
    add(type, message) {
        let toast = new Toast(type, message);
        this.toasts = [...this.toasts, toast];
        this.eventEmitter.emit('toastsUpdated', this.toasts);

        setTimeout(() => {
            this.remove(toast);
        }, 1000);
    }

    /**
     * Remove toast
     *
     * @param {Toast} toastToDelete
     */
    remove(toastToDelete) {
        this.toasts = this.toasts.filter((toast) => toast !== toastToDelete);
        this.eventEmitter.emit('toastsUpdated', this.toasts);
    }
}

export class Toast {
    /**
     * Create a new Toast instance
     *
     * @param {string} type
     * @param {string} message
     */
    constructor(type, message) {
        this.type = type;
        this.message = message;
    }
}
