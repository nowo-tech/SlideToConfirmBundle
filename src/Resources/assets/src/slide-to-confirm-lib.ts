/**
 * Slide-to-confirm / swipe-to-submit library shared by the custom element,
 * the Stimulus controller, and the standalone IIFE.
 */

import { getLogger, setBundleLogger } from './logger';
import type { BundleLogger } from './logger';

export const ATTR_INIT = 'data-slide-to-confirm-init';
export const ATTR_DEBUG = 'data-slide-to-confirm-debug-value';
export const ATTR_THRESHOLD = 'data-slide-to-confirm-threshold-value';
export const ATTR_SUBMIT = 'data-slide-to-confirm-submit-on-confirm-value';
export const ATTR_RESET = 'data-slide-to-confirm-reset-on-release-value';
export const ATTR_TEXT = 'data-slide-to-confirm-text-value';
export const ATTR_CONFIRMED_TEXT = 'data-slide-to-confirm-confirmed-text-value';
export const ATTR_TARGET = 'data-slide-to-confirm-target';

export const TARGET_INPUT = 'input';
export const TARGET_TRACK = 'track';
export const TARGET_THUMB = 'thumb';
export const TARGET_LABEL = 'label';

export const HOST_SELECTOR = 'nowo-slide-to-confirm, [data-controller*="slide-to-confirm"]';

export type SlideConfig = {
  threshold: number;
  submitOnConfirm: boolean;
  resetOnRelease: boolean;
  text: string;
  confirmedText: string;
  debug: boolean;
};

type SlideSession = {
  ratio: number;
  dragging: boolean;
  pointerId: number | null;
  startX: number;
  startRatio: number;
};

const sessions = new WeakMap<HTMLElement, SlideSession>();

type HostBindings = {
  thumb: HTMLButtonElement;
  onPointerDown: (event: PointerEvent) => void;
  onPointerMove: (event: PointerEvent) => void;
  onPointerUp: (event: PointerEvent) => void;
  onKeyDown: (event: Event) => void;
};

const bindings = new WeakMap<HTMLElement, HostBindings>();

export { getLogger, setBundleLogger };
export type { BundleLogger };

function isTruthyAttr(value: string | null): boolean {
  if (value === null) {
    return false;
  }
  const normalized = value.trim().toLowerCase();

  return normalized === '1' || normalized === 'true' || normalized === 'yes';
}

function parseThreshold(value: string | null): number {
  const parsed = Number.parseFloat(value ?? '');
  if (!Number.isFinite(parsed)) {
    return 0.85;
  }
  if (parsed < 0.5) {
    return 0.5;
  }
  if (parsed > 1) {
    return 1;
  }

  return parsed;
}

export function readConfig(host: HTMLElement): SlideConfig {
  return {
    threshold: parseThreshold(host.getAttribute(ATTR_THRESHOLD)),
    submitOnConfirm: isTruthyAttr(host.getAttribute(ATTR_SUBMIT)),
    resetOnRelease: isTruthyAttr(host.getAttribute(ATTR_RESET)),
    text: host.getAttribute(ATTR_TEXT) ?? '',
    confirmedText: host.getAttribute(ATTR_CONFIRMED_TEXT) ?? '',
    debug: isTruthyAttr(host.getAttribute(ATTR_DEBUG)),
  };
}

function queryTarget<T extends Element>(host: HTMLElement, name: string): T | null {
  return host.querySelector<T>(`[${ATTR_TARGET}="${name}"]`);
}

function isRtl(track: HTMLElement): boolean {
  if (track.getAttribute('dir') === 'rtl' || track.dir === 'rtl') {
    return true;
  }

  return getComputedStyle(track).direction === 'rtl';
}

function clamp(value: number, min: number, max: number): number {
  return Math.min(max, Math.max(min, value));
}

function maxTravel(track: HTMLElement, thumb: HTMLElement): number {
  const travel = track.clientWidth - thumb.offsetWidth;

  return travel > 0 ? travel : 0;
}

function applyVisual(host: HTMLElement, ratio: number): void {
  const track = queryTarget<HTMLElement>(host, TARGET_TRACK);
  const thumb = queryTarget<HTMLButtonElement>(host, TARGET_THUMB);
  if (track === null || thumb === null) {
    return;
  }
  const travel = maxTravel(track, thumb);
  const offset = ratio * travel;
  const sign = isRtl(track) ? -1 : 1;
  thumb.style.transform = `translateX(${sign * offset}px)`;
  thumb.setAttribute('aria-valuenow', String(Math.round(ratio * 100)));
  // Progress fill behind the thumb (same colour as the circle).
  host.style.setProperty('--nowo-slide-progress', String(ratio));
}

function setLabel(host: HTMLElement, confirmed: boolean, config: SlideConfig): void {
  const label = queryTarget<HTMLElement>(host, TARGET_LABEL);
  if (label === null) {
    return;
  }
  const next = confirmed ? config.confirmedText : config.text;
  if (next !== '') {
    label.textContent = next;
  }
}

export function confirmSlide(host: HTMLElement, config: SlideConfig = readConfig(host)): void {
  const input = queryTarget<HTMLInputElement>(host, TARGET_INPUT);
  const session = sessions.get(host) ?? { ratio: 1, dragging: false, pointerId: null, startX: 0, startRatio: 0 };
  session.ratio = 1;
  session.dragging = false;
  session.pointerId = null;
  sessions.set(host, session);
  host.classList.remove('is-dragging');
  host.classList.add('is-confirmed');
  applyVisual(host, 1);
  setLabel(host, true, config);
  if (input !== null && !input.checked) {
    input.checked = true;
    input.dispatchEvent(new Event('change', { bubbles: true }));
  }
  host.dispatchEvent(new CustomEvent('nowo-slide-to-confirm:confirmed', { bubbles: true }));
  getLogger().info('slide confirmed');
  if (config.submitOnConfirm) {
    const form = host.closest('form');
    if (form instanceof HTMLFormElement) {
      if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
      } else {
        form.submit();
      }
    }
  }
}

export function resetSlide(host: HTMLElement, config: SlideConfig = readConfig(host)): void {
  const input = queryTarget<HTMLInputElement>(host, TARGET_INPUT);
  const session = sessions.get(host) ?? { ratio: 0, dragging: false, pointerId: null, startX: 0, startRatio: 0 };
  session.ratio = 0;
  session.dragging = false;
  session.pointerId = null;
  sessions.set(host, session);
  host.classList.remove('is-dragging', 'is-confirmed');
  applyVisual(host, 0);
  setLabel(host, false, config);
  if (input !== null && input.checked) {
    input.checked = false;
    input.dispatchEvent(new Event('change', { bubbles: true }));
  }
  getLogger().debug('slide reset');
}

function ratioFromClientX(host: HTMLElement, clientX: number, startX: number, startRatio: number): number {
  const track = queryTarget<HTMLElement>(host, TARGET_TRACK);
  const thumb = queryTarget<HTMLButtonElement>(host, TARGET_THUMB);
  if (track === null || thumb === null) {
    return startRatio;
  }
  const travel = maxTravel(track, thumb);
  if (travel === 0) {
    return startRatio;
  }
  const delta = clientX - startX;
  const signed = isRtl(track) ? -delta : delta;

  return clamp(startRatio + signed / travel, 0, 1);
}

export function applySlideKey(host: HTMLElement, key: string, config: SlideConfig = readConfig(host)): void {
  const thumb = queryTarget<HTMLButtonElement>(host, TARGET_THUMB);
  if (thumb === null || thumb.disabled || host.classList.contains('is-confirmed')) {
    return;
  }
  const session = sessions.get(host);
  const track = queryTarget<HTMLElement>(host, TARGET_TRACK);
  if (session === undefined || track === null) {
    return;
  }
  const rtl = isRtl(track);
  const step = 0.1;
  if (key === 'ArrowRight' || key === 'ArrowLeft') {
    const forward = key === 'ArrowRight';
    const delta = forward === rtl ? -step : step;
    session.ratio = clamp(session.ratio + delta, 0, 1);
    applyVisual(host, session.ratio);
    return;
  }
  if (key === 'Home') {
    resetSlide(host, config);
    return;
  }
  if (key === 'End') {
    confirmSlide(host, config);
    return;
  }
  if ((key === 'Enter' || key === ' ') && session.ratio >= config.threshold) {
    confirmSlide(host, config);
  }
}

function bindHost(host: HTMLElement, config: SlideConfig): void {
  const thumb = queryTarget<HTMLButtonElement>(host, TARGET_THUMB);
  const input = queryTarget<HTMLInputElement>(host, TARGET_INPUT);
  if (thumb === null) {
    getLogger().warn('slide-to-confirm: missing thumb target');

    return;
  }

  const initial = input?.checked === true ? 1 : 0;
  sessions.set(host, { ratio: initial, dragging: false, pointerId: null, startX: 0, startRatio: initial });
  if (initial === 1) {
    host.classList.add('is-confirmed');
    setLabel(host, true, config);
  }
  applyVisual(host, initial);

  const onPointerDown = (event: PointerEvent): void => {
    if (thumb.disabled || host.classList.contains('is-confirmed')) {
      return;
    }
    const session = sessions.get(host) as SlideSession;
    session.dragging = true;
    session.pointerId = event.pointerId;
    session.startX = event.clientX;
    session.startRatio = session.ratio;
    host.classList.add('is-dragging');
    thumb.setPointerCapture(event.pointerId);
    getLogger().debug('slide pointerdown');
  };

  const onPointerMove = (event: PointerEvent): void => {
    const session = sessions.get(host);
    if (session === undefined || !session.dragging || session.pointerId !== event.pointerId) {
      return;
    }
    session.ratio = ratioFromClientX(host, event.clientX, session.startX, session.startRatio);
    applyVisual(host, session.ratio);
  };

  const finishPointer = (event: PointerEvent): void => {
    const session = sessions.get(host);
    if (session === undefined || !session.dragging || session.pointerId !== event.pointerId) {
      return;
    }
    session.dragging = false;
    session.pointerId = null;
    host.classList.remove('is-dragging');
    if (session.ratio >= config.threshold) {
      confirmSlide(host, config);
      return;
    }
    if (config.resetOnRelease) {
      resetSlide(host, config);
    }
  };

  const onKeyDown = (event: Event): void => {
    const key = (event as { key?: string }).key;
    if (typeof key !== 'string' || key === '') {
      return;
    }
    if (['ArrowRight', 'ArrowLeft', 'Home', 'End', 'Enter', ' '].includes(key)) {
      event.preventDefault();
    }
    applySlideKey(host, key, config);
  };

  thumb.addEventListener('pointerdown', onPointerDown);
  thumb.addEventListener('pointermove', onPointerMove);
  thumb.addEventListener('pointerup', finishPointer);
  thumb.addEventListener('pointercancel', finishPointer);
  thumb.addEventListener('keydown', onKeyDown);
  bindings.set(host, {
    thumb,
    onPointerDown,
    onPointerMove,
    onPointerUp: finishPointer,
    onKeyDown,
  });
}

/**
 * Removes pointer/keyboard listeners bound by {@link initSlideContainer}.
 * Safe to call when the host was never initialized.
 */
export function destroySlideContainer(host: HTMLElement): void {
  const bound = bindings.get(host);
  if (bound !== undefined) {
    bound.thumb.removeEventListener('pointerdown', bound.onPointerDown);
    bound.thumb.removeEventListener('pointermove', bound.onPointerMove);
    bound.thumb.removeEventListener('pointerup', bound.onPointerUp);
    bound.thumb.removeEventListener('pointercancel', bound.onPointerUp);
    bound.thumb.removeEventListener('keydown', bound.onKeyDown);
    bindings.delete(host);
  }
  sessions.delete(host);
  host.removeAttribute(ATTR_INIT);
}

export function initSlideContainer(host: Element): boolean {
  if (!(host instanceof HTMLElement)) {
    return false;
  }
  if (host.getAttribute(ATTR_INIT) === '1') {
    return false;
  }
  const config = readConfig(host);
  if (config.debug) {
    getLogger().setDebug(true);
  }
  const track = queryTarget<HTMLElement>(host, TARGET_TRACK);
  if (track === null) {
    getLogger().debug('slide-to-confirm: init skipped (no track target)');

    return false;
  }
  host.setAttribute(ATTR_INIT, '1');
  bindHost(host, config);
  getLogger().debug('slide-to-confirm: container initialized');

  return true;
}

export function runInit(root: ParentNode = document): number {
  const nodes = root.querySelectorAll(HOST_SELECTOR);
  let count = 0;
  nodes.forEach((node) => {
    if (node instanceof HTMLElement && initSlideContainer(node)) {
      count += 1;
    }
  });
  getLogger().debug('slide-to-confirm: initialized hosts', { count });

  return count;
}

export function runInitAndObserve(root: ParentNode = document): MutationObserver {
  runInit(root);
  const observer = new MutationObserver((mutations) => {
    for (const mutation of mutations) {
      mutation.addedNodes.forEach((node) => {
        if (!(node instanceof HTMLElement)) {
          return;
        }
        if (node.matches(HOST_SELECTOR)) {
          initSlideContainer(node);
        }
        node.querySelectorAll(HOST_SELECTOR).forEach((child) => {
          initSlideContainer(child);
        });
      });
    }
  });
  observer.observe(root instanceof Document ? root.documentElement : (root as Node), {
    childList: true,
    subtree: true,
  });

  return observer;
}
