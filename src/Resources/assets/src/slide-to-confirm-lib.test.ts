import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { createBundleLogger } from './logger';
import {
  applySlideKey,
  ATTR_DEBUG,
  ATTR_INIT,
  ATTR_RESET,
  ATTR_SUBMIT,
  ATTR_TARGET,
  ATTR_TEXT,
  ATTR_THRESHOLD,
  confirmSlide,
  HOST_SELECTOR,
  initSlideContainer,
  destroySlideContainer,
  readConfig,
  resetSlide,
  runInit,
  runInitAndObserve,
  setBundleLogger,
  TARGET_INPUT,
  TARGET_LABEL,
  TARGET_THUMB,
  TARGET_TRACK,
} from './slide-to-confirm-lib';

function stubPointerCapture(): void {
  if (typeof HTMLElement.prototype.setPointerCapture !== 'function') {
    HTMLElement.prototype.setPointerCapture = function setPointerCapture(): void {};
  }
}

function pointerEvent(type: string, pointerId: number, clientX: number): Event {
  const Ctor = (globalThis as { PointerEvent?: typeof Event }).PointerEvent ?? Event;
  const event = new Ctor(type, { bubbles: true, cancelable: true } as EventInit);
  Object.defineProperty(event, 'pointerId', { value: pointerId, configurable: true });
  Object.defineProperty(event, 'clientX', { value: clientX, configurable: true });

  return event;
}

function keyEvent(key: string): Event {
  const event = new Event('keydown', { bubbles: true, cancelable: true });
  Object.defineProperty(event, 'key', { value: key, configurable: true });

  return event;
}

function sizeTrack(host: HTMLElement, trackWidth = 300, thumbWidth = 44): void {
  const track = host.querySelector('[data-slide-to-confirm-target="track"]') as HTMLElement;
  const thumb = host.querySelector('[data-slide-to-confirm-target="thumb"]') as HTMLElement;
  Object.defineProperty(track, 'clientWidth', { configurable: true, value: trackWidth });
  Object.defineProperty(thumb, 'offsetWidth', { configurable: true, value: thumbWidth });
}

function createHost(overrides: Record<string, string> = {}): HTMLElement {
  const host = document.createElement('div');
  host.setAttribute('data-controller', 'slide-to-confirm');
  host.setAttribute(ATTR_THRESHOLD, overrides.threshold ?? '0.85');
  host.setAttribute(ATTR_SUBMIT, overrides.submit ?? '1');
  host.setAttribute(ATTR_RESET, overrides.reset ?? '1');
  host.setAttribute(ATTR_TEXT, overrides.text ?? 'Slide to confirm');
  host.setAttribute('data-slide-to-confirm-confirmed-text-value', overrides.confirmed ?? 'Confirmed');
  if (overrides.debug !== undefined) {
    host.setAttribute(ATTR_DEBUG, overrides.debug);
  }
  host.innerHTML = `
    <input type="checkbox" data-slide-to-confirm-target="input" value="1" />
    <div data-slide-to-confirm-target="track">
      <span data-slide-to-confirm-target="label">Slide to confirm</span>
      <button type="button" data-slide-to-confirm-target="thumb"></button>
    </div>
  `;
  document.body.appendChild(host);
  sizeTrack(host);

  return host;
}

describe('slide-to-confirm-lib', () => {
  beforeEach(() => {
    stubPointerCapture();
    vi.spyOn(console, 'log').mockImplementation(() => {});
    vi.spyOn(console, 'debug').mockImplementation(() => {});
    vi.spyOn(console, 'info').mockImplementation(() => {});
    vi.spyOn(console, 'warn').mockImplementation(() => {});
    vi.spyOn(console, 'error').mockImplementation(() => {});
    setBundleLogger(createBundleLogger('slide-to-confirm', { alwaysLog: true }));
  });

  afterEach(() => {
    document.body.innerHTML = '';
    vi.restoreAllMocks();
  });

  describe('constants', () => {
    it('exports host selector and target names', () => {
      expect(HOST_SELECTOR).toContain('nowo-slide-to-confirm');
      expect(ATTR_TARGET).toBe('data-slide-to-confirm-target');
      expect(TARGET_INPUT).toBe('input');
      expect(TARGET_TRACK).toBe('track');
      expect(TARGET_THUMB).toBe('thumb');
      expect(TARGET_LABEL).toBe('label');
    });
  });

  describe('readConfig', () => {
    it('parses attributes including truthy variants and threshold bounds', () => {
      const host = createHost({ threshold: '1.5', submit: 'true', reset: 'yes', debug: '1' });
      const config = readConfig(host);
      expect(config.threshold).toBe(1);
      expect(config.submitOnConfirm).toBe(true);
      expect(config.resetOnRelease).toBe(true);
      expect(config.debug).toBe(true);

      host.setAttribute(ATTR_THRESHOLD, '0.2');
      host.setAttribute(ATTR_SUBMIT, 'no');
      host.setAttribute(ATTR_RESET, '');
      host.removeAttribute(ATTR_DEBUG);
      const low = readConfig(host);
      expect(low.threshold).toBe(0.5);
      expect(low.submitOnConfirm).toBe(false);
      expect(low.resetOnRelease).toBe(false);
      expect(low.debug).toBe(false);
    });

    it('uses default threshold when the attribute is not numeric', () => {
      const host = createHost({ threshold: 'abc' });
      expect(readConfig(host).threshold).toBe(0.85);
    });
  });

  describe('initSlideContainer', () => {
    it('returns false for non-HTMLElement hosts', () => {
      const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
      expect(initSlideContainer(svg)).toBe(false);
    });

    it('returns false without a track and when already initialized', () => {
      const empty = document.createElement('div');
      expect(initSlideContainer(empty)).toBe(false);

      const host = createHost();
      expect(initSlideContainer(host)).toBe(true);
      expect(host.getAttribute(ATTR_INIT)).toBe('1');
      expect(initSlideContainer(host)).toBe(false);
    });

    it('warns when the thumb is missing', () => {
      const host = document.createElement('div');
      host.innerHTML = '<div data-slide-to-confirm-target="track"></div>';
      document.body.appendChild(host);
      expect(initSlideContainer(host)).toBe(true);
      expect(console.warn).toHaveBeenCalled();
    });

    it('enables debug logging from the host attribute', () => {
      const host = createHost({ debug: '1' });
      initSlideContainer(host);
      expect(initSlideContainer(host)).toBe(false);
    });

    it('locks an already checked input in the confirmed state', () => {
      const host = createHost();
      const input = host.querySelector('input') as HTMLInputElement;
      input.checked = true;
      initSlideContainer(host);
      expect(host.classList.contains('is-confirmed')).toBe(true);
      expect(host.querySelector('[data-slide-to-confirm-target="label"]')?.textContent).toBe('Confirmed');
    });
  });

  describe('pointer interaction', () => {
    it('confirms and requestSubmits when released past the threshold', () => {
      const form = document.createElement('form');
      const requestSubmit = vi.fn();
      form.requestSubmit = requestSubmit;
      document.body.appendChild(form);
      const host = createHost({ threshold: '0.5' });
      form.appendChild(host);
      initSlideContainer(host);
      const thumb = host.querySelector('button') as HTMLButtonElement;

      thumb.dispatchEvent(pointerEvent('pointerdown', 1, 10));
      thumb.dispatchEvent(pointerEvent('pointermove', 1, 200));
      thumb.dispatchEvent(pointerEvent('pointerup', 1, 200));

      expect((host.querySelector('input') as HTMLInputElement).checked).toBe(true);
      expect(host.classList.contains('is-confirmed')).toBe(true);
      expect(requestSubmit).toHaveBeenCalled();
    });

    it('falls back to form.submit when requestSubmit is unavailable', () => {
      const form = document.createElement('form');
      Object.defineProperty(form, 'requestSubmit', { configurable: true, value: undefined });
      const submit = vi.fn();
      form.submit = submit;
      document.body.appendChild(form);
      const host = createHost({ threshold: '0.5' });
      form.appendChild(host);
      initSlideContainer(host);
      confirmSlide(host);
      expect(submit).toHaveBeenCalled();
    });

    it('resets when released below the threshold', () => {
      const host = createHost({ threshold: '0.9' });
      initSlideContainer(host);
      const thumb = host.querySelector('button') as HTMLButtonElement;
      thumb.dispatchEvent(pointerEvent('pointerdown', 7, 10));
      thumb.dispatchEvent(pointerEvent('pointermove', 7, 30));
      thumb.dispatchEvent(pointerEvent('pointerup', 7, 30));
      expect((host.querySelector('input') as HTMLInputElement).checked).toBe(false);
      expect(host.classList.contains('is-confirmed')).toBe(false);
    });

    it('keeps the partial position when reset-on-release is disabled', () => {
      const host = createHost({ threshold: '0.9', reset: '0', submit: '0' });
      initSlideContainer(host);
      const thumb = host.querySelector('button') as HTMLButtonElement;
      thumb.dispatchEvent(pointerEvent('pointerdown', 2, 10));
      thumb.dispatchEvent(pointerEvent('pointermove', 2, 80));
      thumb.dispatchEvent(pointerEvent('pointercancel', 2, 80));
      expect(host.classList.contains('is-confirmed')).toBe(false);
    });

    it('ignores pointer events with a different pointer id or while not dragging', () => {
      const host = createHost();
      initSlideContainer(host);
      const thumb = host.querySelector('button') as HTMLButtonElement;
      thumb.dispatchEvent(pointerEvent('pointermove', 9, 200));
      thumb.dispatchEvent(pointerEvent('pointerup', 9, 200));
      thumb.dispatchEvent(pointerEvent('pointerdown', 3, 10));
      thumb.dispatchEvent(pointerEvent('pointermove', 99, 200));
      expect(host.classList.contains('is-confirmed')).toBe(false);
    });

    it('ignores pointerdown when disabled or already confirmed', () => {
      const host = createHost({ submit: '0' });
      const thumb = host.querySelector('button') as HTMLButtonElement;
      initSlideContainer(host);
      confirmSlide(host);
      thumb.dispatchEvent(pointerEvent('pointerdown', 1, 10));
      expect(host.classList.contains('is-dragging')).toBe(false);

      resetSlide(host);
      thumb.disabled = true;
      thumb.dispatchEvent(pointerEvent('pointerdown', 1, 10));
      expect(host.classList.contains('is-dragging')).toBe(false);
    });

    it('does not submit when submit-on-confirm is false', () => {
      const form = document.createElement('form');
      const requestSubmit = vi.fn();
      form.requestSubmit = requestSubmit;
      document.body.appendChild(form);
      const host = createHost({ submit: '0' });
      form.appendChild(host);
      initSlideContainer(host);
      confirmSlide(host);
      expect(requestSubmit).not.toHaveBeenCalled();
      expect((host.querySelector('input') as HTMLInputElement).checked).toBe(true);
    });

    it('mirrors pointer travel in RTL', () => {
      vi.spyOn(window, 'getComputedStyle').mockImplementation(
        () => ({ direction: 'rtl' }) as CSSStyleDeclaration,
      );
      const host = createHost({ threshold: '0.5', submit: '0' });
      const track = host.querySelector('[data-slide-to-confirm-target="track"]') as HTMLElement;
      track.setAttribute('dir', 'rtl');
      initSlideContainer(host);
      const thumb = host.querySelector('button') as HTMLButtonElement;
      thumb.dispatchEvent(pointerEvent('pointerdown', 1, 200));
      thumb.dispatchEvent(pointerEvent('pointermove', 1, 10));
      thumb.dispatchEvent(pointerEvent('pointerup', 1, 10));
      expect((host.querySelector('input') as HTMLInputElement).checked).toBe(true);
    });

    it('keeps the start ratio when the track has no travel', () => {
      const host = createHost({ threshold: '0.5' });
      sizeTrack(host, 44, 44);
      initSlideContainer(host);
      const thumb = host.querySelector('button') as HTMLButtonElement;
      thumb.dispatchEvent(pointerEvent('pointerdown', 1, 10));
      thumb.dispatchEvent(pointerEvent('pointermove', 1, 200));
      expect(host.classList.contains('is-confirmed')).toBe(false);
    });

    it('keeps the start ratio when the track target disappears mid-drag', () => {
      const host = createHost({ threshold: '0.5' });
      initSlideContainer(host);
      const thumb = host.querySelector('button') as HTMLButtonElement;
      const track = host.querySelector('[data-slide-to-confirm-target="track"]') as HTMLElement;
      thumb.dispatchEvent(pointerEvent('pointerdown', 1, 10));
      track.removeAttribute('data-slide-to-confirm-target');
      thumb.dispatchEvent(pointerEvent('pointermove', 1, 200));
      thumb.dispatchEvent(pointerEvent('pointerup', 1, 200));
      expect(host.classList.contains('is-confirmed')).toBe(false);
    });
  });

  describe('keyboard', () => {
    it('moves with arrows, confirms with End/Enter/Space, and resets with Home', () => {
      const host = createHost({ threshold: '0.15', submit: '0' });
      expect(initSlideContainer(host)).toBe(true);

      applySlideKey(host, 'End');
      expect((host.querySelector('input') as HTMLInputElement).checked).toBe(true);

      resetSlide(host);
      expect((host.querySelector('input') as HTMLInputElement).checked).toBe(false);

      applySlideKey(host, 'ArrowRight');
      applySlideKey(host, 'ArrowLeft');
      applySlideKey(host, 'Home');
      expect((host.querySelector('input') as HTMLInputElement).checked).toBe(false);

      for (let i = 0; i < 10; i += 1) {
        applySlideKey(host, 'ArrowRight');
      }
      applySlideKey(host, 'Enter');
      expect((host.querySelector('input') as HTMLInputElement).checked).toBe(true);

      resetSlide(host);
      for (let i = 0; i < 10; i += 1) {
        applySlideKey(host, 'ArrowRight');
      }
      applySlideKey(host, ' ');
      expect((host.querySelector('input') as HTMLInputElement).checked).toBe(true);

      resetSlide(host);
      applySlideKey(host, 'Enter');
      expect((host.querySelector('input') as HTMLInputElement).checked).toBe(false);
      applySlideKey(host, 'a');

      const thumb = host.querySelector('button') as HTMLButtonElement;
      thumb.dispatchEvent(new Event('keydown', { bubbles: true, cancelable: true }));
      thumb.dispatchEvent(keyEvent('End'));
      expect((host.querySelector('input') as HTMLInputElement).checked).toBe(true);
    });

    it('swaps arrow directions in RTL and ignores keys while confirmed or disabled', () => {
      const host = createHost({ submit: '0', threshold: '0.95' });
      const track = host.querySelector('[data-slide-to-confirm-target="track"]') as HTMLElement;
      track.setAttribute('dir', 'rtl');
      initSlideContainer(host);
      applySlideKey(host, 'ArrowLeft');
      applySlideKey(host, 'ArrowRight');
      expect(track.getAttribute('dir')).toBe('rtl');

      const styleRtlHost = createHost({ submit: '0', threshold: '0.95' });
      vi.spyOn(window, 'getComputedStyle').mockImplementation(
        () => ({ direction: 'rtl' }) as CSSStyleDeclaration,
      );
      initSlideContainer(styleRtlHost);
      applySlideKey(styleRtlHost, 'ArrowLeft');
      applySlideKey(styleRtlHost, 'ArrowRight');
      vi.restoreAllMocks();

      confirmSlide(host);
      applySlideKey(host, 'ArrowLeft');
      resetSlide(host);
      const thumb = host.querySelector('button') as HTMLButtonElement;
      thumb.disabled = true;
      applySlideKey(host, 'ArrowLeft');
    });
  });

  describe('runInit and observer', () => {
    it('initializes matching hosts and observes added nodes', () => {
      const host = createHost();
      expect(runInit(document)).toBe(1);
      expect(runInit(document)).toBe(0);

      const observer = runInitAndObserve(document);
      const nestedRoot = document.createElement('div');
      const child = createHost();
      nestedRoot.appendChild(child);
      document.body.appendChild(nestedRoot);
      observer.observe(document.documentElement, { childList: true, subtree: true });
      const added = createHost();
      document.body.appendChild(added);
      observer.disconnect();
    });

    it('destroySlideContainer unbinds listeners and allows re-init', () => {
      const host = createHost({ submit: '0' });
      expect(initSlideContainer(host)).toBe(true);
      destroySlideContainer(host);
      expect(host.getAttribute(ATTR_INIT)).toBeNull();
      destroySlideContainer(host);
      expect(initSlideContainer(host)).toBe(true);
    });

    it('observes a host that matches the selector', async () => {
      const observer = runInitAndObserve(document.body);
      const el = document.createElement('div');
      el.setAttribute('data-controller', 'slide-to-confirm');
      el.innerHTML = '<div data-slide-to-confirm-target="track"><button data-slide-to-confirm-target="thumb"></button></div>';
      document.body.appendChild(el);
      await new Promise((r) => setTimeout(r, 30));
      expect(el.getAttribute(ATTR_INIT)).toBe('1');

      const nested = document.createElement('div');
      nested.setAttribute('data-controller', 'slide-to-confirm');
      nested.innerHTML = '<div data-slide-to-confirm-target="track"><button data-slide-to-confirm-target="thumb"></button></div>';
      const wrapper = document.createElement('section');
      wrapper.appendChild(nested);
      document.body.appendChild(wrapper);
      await new Promise((r) => setTimeout(r, 30));
      observer.disconnect();
      expect(nested.getAttribute(ATTR_INIT)).toBe('1');
    });

    it('ignores non-element added nodes in the observer', async () => {
      const observer = runInitAndObserve(document.body);
      document.body.appendChild(document.createTextNode('x'));
      document.body.appendChild(document.createComment('y'));
      await new Promise((r) => setTimeout(r, 30));
      observer.disconnect();
    });
  });

  describe('visual helpers', () => {
    it('skips label updates when texts are empty', () => {
      const host = createHost({ text: '', confirmed: '' });
      host.removeAttribute(ATTR_TEXT);
      host.removeAttribute('data-slide-to-confirm-confirmed-text-value');
      const label = host.querySelector('[data-slide-to-confirm-target="label"]') as HTMLElement;
      const original = label.textContent;
      initSlideContainer(host);
      confirmSlide(host);
      expect(label.textContent).toBe(original);
    });

    it('applySlideKey no-ops without a bound session', () => {
      const host = document.createElement('div');
      applySlideKey(host, 'End');
      host.innerHTML = '<button type="button" data-slide-to-confirm-target="thumb"></button>';
      applySlideKey(host, 'End');
      expect((host.querySelector('input') as HTMLInputElement | null)?.checked ?? false).toBe(false);
    });

    it('confirmSlide without input or track is safe', () => {
      const host = document.createElement('div');
      document.body.appendChild(host);
      confirmSlide(host);
      resetSlide(host);
      resetSlide(document.createElement('div'));
    });
  });
});
