/**
 * Demo app entry: start Stimulus and register the bundle's slide-to-confirm controller.
 */
import { Application } from '@hotwired/stimulus';
import SlideToConfirmController from '/var/slide-to-confirm-bundle/src/Resources/assets/controllers/slide_to_confirm_controller.ts';
import { createBundleLogger } from '/var/slide-to-confirm-bundle/src/Resources/assets/src/logger.ts';
import { setBundleLogger } from '/var/slide-to-confirm-bundle/src/Resources/assets/src/slide-to-confirm-lib.ts';
import '/var/slide-to-confirm-bundle/src/Resources/assets/css/slide-to-confirm.css';

declare const __SLIDE_TO_CONFIRM_BUILD_TIME__: string;

const log = createBundleLogger('slide-to-confirm', {
  buildTime:
    typeof __SLIDE_TO_CONFIRM_BUILD_TIME__ !== 'undefined' ? __SLIDE_TO_CONFIRM_BUILD_TIME__ : undefined,
});
log.scriptLoaded();
setBundleLogger(log);

const application = Application.start();
application.register('slide-to-confirm', SlideToConfirmController);

document.addEventListener('nowo-slide-to-confirm:confirmed', (event: Event) => {
  const host = event.target;
  if (!(host instanceof HTMLElement)) {
    return;
  }
  const form = host.closest('form');
  const submit = form?.querySelector<HTMLButtonElement>('[data-demo-gated-submit]');
  if (submit) {
    submit.disabled = false;
  }
});
