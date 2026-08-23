import { afterEach, describe, expect, it, vi } from 'vitest';
import {
  ensureNowoSlideToConfirmDefined,
  NowoSlideToConfirmElement,
} from './nowo-slide-to-confirm-element';

describe('nowo-slide-to-confirm-element', () => {
  afterEach(() => {
    vi.restoreAllMocks();
    document.body.innerHTML = '';
  });

  it('defines the custom element once', () => {
    ensureNowoSlideToConfirmDefined();
    ensureNowoSlideToConfirmDefined();
    expect(customElements.get('nowo-slide-to-confirm')).toBe(NowoSlideToConfirmElement);
  });

  it('initializes on connectedCallback', () => {
    ensureNowoSlideToConfirmDefined();
    const el = document.createElement('nowo-slide-to-confirm') as NowoSlideToConfirmElement;
    el.innerHTML = '<div data-slide-to-confirm-target="track"><button data-slide-to-confirm-target="thumb"></button></div>';
    document.body.appendChild(el);
    expect(el.getAttribute('data-slide-to-confirm-init')).toBe('1');
  });

  it('no-ops when customElements is unavailable', () => {
    const original = globalThis.customElements;
    Object.defineProperty(globalThis, 'customElements', { configurable: true, value: undefined });
    expect(() => ensureNowoSlideToConfirmDefined()).not.toThrow();
    Object.defineProperty(globalThis, 'customElements', { configurable: true, value: original });
  });
});
