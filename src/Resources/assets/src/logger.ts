/**
 * =============================================================================
 * BUNDLE LOGGER - Slide To Confirm Bundle (always-on logging)
 * =============================================================================
 *
 * Same API shape as other Nowo bundles.
 *
 * For SlideToConfirmBundle this logger supports a debug toggle:
 * - scriptLoaded() always logs
 * - debug/info/warn/error are enabled only after setDebug(true) unless alwaysLog is set.
 *
 * USAGE:
 *
 * 1. In the entry (index.ts), create and register the logger:
 *
 *    import { createBundleLogger, setBundleLogger } from './logger';
 *    declare const __SELECT_ALL_CHOICE_BUILD_TIME__: string;
 *
 *    const log = createBundleLogger('slide-to-confirm', {
 *      buildTime: typeof __SELECT_ALL_CHOICE_BUILD_TIME__ !== 'undefined' ? __SELECT_ALL_CHOICE_BUILD_TIME__ : undefined,
 *      alwaysLog: false,
 *    });
 *    log.scriptLoaded();
 *    setBundleLogger(log);
 *
 * 2. In other modules, get the logger and use level methods:
 *
 *    import { getLogger } from './logger';
 *    const log = getLogger();
 *    log.debug('detail', { foo: 1 });
 *    log.info('something happened');
 *    log.warn('unexpected', err);
 *    log.error('failure', err);
 *
 * =============================================================================
 */

export type BundleLoggerOptions = {
  /** If set, scriptLoaded() will include this (e.g. build/compilation time). */
  buildTime?: string;
  /** When true, debug/info/warn/error always output (no setDebug needed). */
  alwaysLog?: boolean;
};

export type BundleLogger = {
  /** Call once at startup. Logs "script loaded" and optional build time. Always shown. */
  scriptLoaded: () => void;
  /** No-op when alwaysLog is true; kept for API compatibility. */
  setDebug: (enabled: boolean) => void;
  debug: (...args: unknown[]) => void;
  info: (...args: unknown[]) => void;
  warn: (...args: unknown[]) => void;
  error: (...args: unknown[]) => void;
};

const STYLES = {
  script: 'color:#0ea5e9;font-weight:bold',
  debug: 'color:#6b7280',
  info: 'color:#2563eb',
  warn: 'color:#d97706',
  error: 'color:#dc2626;font-weight:bold',
} as const;

const EMOJI = {
  script: '📦',
  debug: '🔍',
  info: 'ℹ️',
  warn: '⚠️',
  error: '❌',
} as const;

function formatArgs(args: unknown[]): unknown[] {
  return args.map((a) =>
    typeof a === 'object' && a !== null && !(a instanceof Error) ? JSON.stringify(a) : a,
  );
}

type ConsoleLevel = 'debug' | 'info' | 'warn' | 'error';

function logScriptLoaded(prefix: string, buildTime: string | undefined): void {
  if (buildTime !== undefined && buildTime !== '') {
    console.log(
      `%c${EMOJI.script} ${prefix} script loaded, build time: %c${buildTime}`,
      STYLES.script,
      'color:#059669',
    );
    return;
  }
  console.log(`%c${EMOJI.script} ${prefix} script loaded`, STYLES.script);
}

function emitLevelLog(level: ConsoleLevel, prefix: string, args: unknown[]): void {
  const emoji = EMOJI[level];
  const style = STYLES[level];
  const label = `%c${emoji} ${prefix}`;
  const logFn = console[level] as (...fnArgs: unknown[]) => void;
  if (args.length > 0) {
    logFn(label, style, ...formatArgs(args));
    return;
  }
  logFn(label, style);
}

function makeLevelMethod(
  isEnabled: () => boolean,
  prefix: string,
  level: ConsoleLevel,
): (...args: unknown[]) => void {
  return (...args: unknown[]): void => {
    if (!isEnabled()) return;
    emitLevelLog(level, prefix, args);
  };
}

function noop(): void {}

let instance: BundleLogger | null = null;

/**
 * Registers the bundle logger. Call once from the entry (index.ts) after createBundleLogger.
 */
export function setBundleLogger(log: BundleLogger): void {
  instance = log;
}

/**
 * Clears the registered logger (for tests only). After this, getLogger() returns the no-op logger until setBundleLogger is called again.
 */
export function clearBundleLoggerForTest(): void {
  instance = null;
}

/**
 * Returns the bundle logger. Use in Overlay, BlockStorage, etc.
 * If never set, returns a no-op logger so tests do not need to call setBundleLogger.
 */
export function getLogger(): BundleLogger {
  if (instance !== null) {
    return instance;
  }
  return {
    scriptLoaded: noop,
    setDebug: noop,
    debug: noop,
    info: noop,
    warn: noop,
    error: noop,
  };
}

/**
 * Creates a bundle logger for SlideToConfirmBundle.
 *
 * @param name - Short name for log prefix (e.g. 'slide-to-confirm').
 * @param options - buildTime for scriptLoaded(); set alwaysLog to force debug/info/warn/error to always output.
 */
export function createBundleLogger(name: string, options: BundleLoggerOptions = {}): BundleLogger {
  const prefix = `[${name}]`;
  const { buildTime, alwaysLog = false } = options;
  const logAlways = alwaysLog === true;
  let debugEnabled = logAlways;

  return {
    scriptLoaded(): void {
      logScriptLoaded(prefix, buildTime);
    },

    setDebug(enabled: boolean): void {
      // If alwaysLog is enabled, debug/info/warn/error are always visible.
      debugEnabled = logAlways ? true : enabled;
    },

    debug: makeLevelMethod(() => debugEnabled, prefix, 'debug'),
    info: makeLevelMethod(() => debugEnabled, prefix, 'info'),
    warn: makeLevelMethod(() => debugEnabled, prefix, 'warn'),
    error: makeLevelMethod(() => debugEnabled, prefix, 'error'),
  };
}
