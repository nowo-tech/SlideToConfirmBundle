import { Application } from '@hotwired/stimulus';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { createBundleLogger } from '../src/logger';
import { ATTR_INIT, setBundleLogger } from '../src/slide-to-confirm-lib';
import SlideToConfirmController from './slide_to_confirm_controller';

describe('slide_to_confirm_controller', () => {
  let application: Application;

  beforeEach(() => {
    vi.spyOn(console, 'debug').mockImplementation(() => {});
    vi.spyOn(console, 'warn').mockImplementation(() => {});
    setBundleLogger(createBundleLogger('slide-to-confirm', { alwaysLog: true }));
    application = Application.start();
    application.register('slide-to-confirm', SlideToConfirmController);
  });

  afterEach(() => {
    application.stop();
    document.body.innerHTML = '';
    vi.restoreAllMocks();
  });

  it('initializes a host on connect', async () => {
    const host = document.createElement('div');
    host.setAttribute('data-controller', 'slide-to-confirm');
    host.innerHTML = `
      <div data-slide-to-confirm-target="track">
        <button type="button" data-slide-to-confirm-target="thumb"></button>
      </div>
    `;
    document.body.appendChild(host);
    await new Promise((r) => setTimeout(r, 0));
    expect(host.getAttribute(ATTR_INIT)).toBe('1');
    host.remove();
    await new Promise((r) => setTimeout(r, 0));
    expect(host.getAttribute(ATTR_INIT)).toBeNull();
  });

  it('skips init when the track is missing', async () => {
    const host = document.createElement('div');
    host.setAttribute('data-controller', 'slide-to-confirm');
    document.body.appendChild(host);
    await new Promise((r) => setTimeout(r, 0));
    expect(host.getAttribute(ATTR_INIT)).toBeNull();
  });

  it('ignores non-HTML elements (e.g. SVG) on connect and disconnect', async () => {
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('data-controller', 'slide-to-confirm');
    document.body.appendChild(svg);
    await new Promise((r) => setTimeout(r, 0));
    expect(svg.getAttribute(ATTR_INIT)).toBeNull();
    svg.remove();
    await new Promise((r) => setTimeout(r, 0));
    expect(svg.getAttribute(ATTR_INIT)).toBeNull();
  });
});
