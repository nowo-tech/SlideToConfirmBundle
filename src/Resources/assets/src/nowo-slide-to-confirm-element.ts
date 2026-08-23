/**
 * Autonomous custom element `<nowo-slide-to-confirm>` used by the default form theme.
 */

import { initSlideContainer } from './slide-to-confirm-lib';

export class NowoSlideToConfirmElement extends HTMLElement {
  connectedCallback(): void {
    initSlideContainer(this);
  }
}

export function ensureNowoSlideToConfirmDefined(): void {
  if (typeof customElements === 'undefined') {
    return;
  }
  if (customElements.get('nowo-slide-to-confirm') !== undefined) {
    return;
  }
  customElements.define('nowo-slide-to-confirm', NowoSlideToConfirmElement);
}
