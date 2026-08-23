/**
 * Stimulus controller for the slide-to-confirm widget.
 *
 * Register: application.register('slide-to-confirm', SlideToConfirmController);
 */

import { Controller } from '@hotwired/stimulus';
import { ensureNowoSlideToConfirmDefined } from '../src/nowo-slide-to-confirm-element';
import { destroySlideContainer, getLogger, initSlideContainer } from '../src/slide-to-confirm-lib';

ensureNowoSlideToConfirmDefined();

export default class SlideToConfirmController extends Controller {
  connect(): void {
    getLogger().debug('slide-to-confirm (controller): connect', {
      isHTMLElement: this.element instanceof HTMLElement,
    });
    if (this.element instanceof HTMLElement) {
      const ok = initSlideContainer(this.element);
      if (ok) {
        getLogger().debug('slide-to-confirm (controller): container initialized');
      } else {
        getLogger().debug('slide-to-confirm (controller): init skipped');
      }
    }
  }

  disconnect(): void {
    if (this.element instanceof HTMLElement) {
      destroySlideContainer(this.element);
    }
  }
}
