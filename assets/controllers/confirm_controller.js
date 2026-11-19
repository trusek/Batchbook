import { Controller } from '@hotwired/stimulus';

/*
 * Pyta o potwierdzenie przed wysłaniem formularza (np. usunięcia).
 *
 * <form data-controller="confirm" data-confirm-message-value="Usunąć?" data-action="submit->confirm#ask">
 */
export default class extends Controller {
    static values = { message: { type: String, default: 'Na pewno?' } };

    ask(event) {
        if (!window.confirm(this.messageValue)) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }
}
