import { EventEmitter } from 'events';

export default class ToastService {
    toasts = [];

    eventEmitter = new EventEmitter();

    add(type, message) {
        let toast = new Toast(type, message);
        this.toasts = [...this.toasts, toast];
        this.eventEmitter.emit('toastsUpdated', this.toasts);

        setTimeout(() => {
            this.remove(toast);
        }, 1000);
    }

    remove(toastToDelete) {
        this.toasts = this.toasts.filter((toast) => toast !== toastToDelete);
        this.eventEmitter.emit('toastsUpdated', this.toasts);
    }
}

export class Toast {
    constructor(type, message) {
        this.type = type;
        this.message = message;
    }
}
