import languages from '@lang/languages.json';
import { createI18n } from 'vue-i18n';

export const LANGUAGES = languages.available;
export const SUPPORT_LOCALES = languages.available.map(l => l.value);
export const RTL_LOCALES = languages.available.filter(l => l.rtl).map(l => l.value);

export const isRtlLocale = locale => RTL_LOCALES.includes(locale);

export const getDocumentDirection = locale => (isRtlLocale(locale) ? 'rtl' : 'ltr');

const i18n = createI18n({
  messages: {},
  legacy: false,
  missingWarn: true,
  mode: 'composition',
  fallbackWarn: false,
  fallbackLocale: 'en',
  warnHtmlMessage: false,
  locale: typeof window === 'undefined' ? 'en' : window.Locale || 'en',
  missing: async (locale, key) => {
    // console.log('"' + key + '" missing for ' + locale + ' locale.');
    // console.log('"' + key + '": "' + key + '",');
  },
});

const loadedLocales = new Set();

/**
 * Fetch a language file from the backend and register it with vue-i18n.
 * Falls back silently if the request fails so the UI keeps rendering keys.
 *
 * @param {string} locale
 * @param {{ force?: boolean }} [options]
 * @returns {Promise<void>}
 */
export async function loadLocaleMessages(locale, { force = false } = {}) {
  if (typeof window === 'undefined' || !locale) {
    return;
  }
  if (!force && loadedLocales.has(locale)) {
    return;
  }
  if (!SUPPORT_LOCALES.includes(locale)) {
    return;
  }

  try {
    const response = await fetch(`/lang/${locale}`, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
      cache: 'no-cache',
    });
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }
    const messages = await response.json();
    i18n.global.setLocaleMessage(locale, { ...messages, default: 'default' });
    loadedLocales.add(locale);
  } catch (error) {
    console.error(`Failed to load "${locale}" language file.`, error);
  }
}

export default i18n;
