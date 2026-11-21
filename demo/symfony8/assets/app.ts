/**
 * Demo app entry (Pentatrion Vite + TypeScript).
 * Starts Stimulus and registers the bundle's slide-to-confirm controller.
 */
import { Application } from '@hotwired/stimulus';
import SlideToConfirmController from '@bundle/controllers/slide_to_confirm_controller.ts';
import { createBundleLogger } from '@bundle/src/logger.ts';
import { setBundleLogger } from '@bundle/src/slide-to-confirm-lib.ts';

declare const __SLIDE_TO_CONFIRM_BUILD_TIME__: string;

const log = createBundleLogger('slide-to-confirm', {
  buildTime:
    typeof __SLIDE_TO_CONFIRM_BUILD_TIME__ !== 'undefined' ? __SLIDE_TO_CONFIRM_BUILD_TIME__ : undefined,
});
log.scriptLoaded();
setBundleLogger(log);

const application = Application.start();
// Marker so we can verify Vite compiled this entry (not the plugin stub).
application.register('slide-to-confirm', SlideToConfirmController);

document.addEventListener('nowo-slide-to-confirm:confirmed', (event: Event) => {
  const host = event.target;
  if (!(host instanceof HTMLElement)) {
    return;
  }
  // The gated submit sits in the section card footer (outside the form, linked with the form attribute).
  const submit = document.querySelector<HTMLButtonElement>('[data-demo-gated-submit]');
  if (submit) {
    submit.disabled = false;
  }
});
