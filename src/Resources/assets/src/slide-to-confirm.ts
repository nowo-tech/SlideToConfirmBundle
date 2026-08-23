/**
 * SlideToConfirmBundle standalone entry.
 * Defines the `nowo-slide-to-confirm` custom element and auto-inits hosts on DOM ready.
 */

import '../css/slide-to-confirm.css';
import { createBundleLogger } from './logger';
import { ensureNowoSlideToConfirmDefined } from './nowo-slide-to-confirm-element';
import {
  confirmSlide,
  destroySlideContainer,
  getLogger,
  initSlideContainer,
  resetSlide,
  runInit,
  runInitAndObserve,
  setBundleLogger,
} from './slide-to-confirm-lib';

ensureNowoSlideToConfirmDefined();

declare const __SLIDE_TO_CONFIRM_BUILD_TIME__: string;

const log = createBundleLogger('slide-to-confirm', {
  buildTime:
    typeof __SLIDE_TO_CONFIRM_BUILD_TIME__ !== 'undefined' ? __SLIDE_TO_CONFIRM_BUILD_TIME__ : undefined,
});
log.scriptLoaded();
setBundleLogger(log);

if (typeof window !== 'undefined') {
  getLogger().debug('standalone entry: exposing NowoSlideToConfirm on window');
  (window as unknown as {
    NowoSlideToConfirm?: {
      initSlideContainer: typeof initSlideContainer;
      destroySlideContainer: typeof destroySlideContainer;
      runInit: typeof runInit;
      runInitAndObserve: typeof runInitAndObserve;
      confirmSlide: typeof confirmSlide;
      resetSlide: typeof resetSlide;
    };
  }).NowoSlideToConfirm = {
    initSlideContainer,
    destroySlideContainer,
    runInit,
    runInitAndObserve,
    confirmSlide,
    resetSlide,
  };
}

if (document.readyState === 'loading') {
  getLogger().debug('standalone entry: DOM loading, scheduling runInitAndObserve on DOMContentLoaded');
  document.addEventListener('DOMContentLoaded', () => {
    runInitAndObserve();
  });
} else {
  getLogger().debug('standalone entry: DOM ready, running runInitAndObserve now');
  runInitAndObserve();
}
