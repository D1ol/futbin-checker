import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['count'];
    count = 0;



    increment() {
        this.count++;
        this.countTarget.innerText = this.count;
    }
}
