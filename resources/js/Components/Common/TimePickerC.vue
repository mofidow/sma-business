<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { InputError, InputLabel } from '@/Components/Jet';

const CONFIG = {
  HOUR_TOKENS: ['HH', 'H', 'hh', 'h', 'kk', 'k'],
  MINUTE_TOKENS: ['mm', 'm'],
  SECOND_TOKENS: ['ss', 's'],
  APM_TOKENS: ['A', 'a'],
  BASIC_TYPES: ['hour', 'minute', 'second', 'apm'],
};

const DEFAULT_OPTIONS = {
  format: 'HH:mm',
  minuteInterval: 1,
  secondInterval: 1,
  hourRange: null,
  minuteRange: null,
  secondRange: null,
  hideDisabledHours: false,
  hideDisabledMinutes: false,
  hideDisabledSeconds: false,
  hideDisabledItems: false,
  hideDropdown: false,
  blurDelay: 300,
  manualInputTimeout: 1000,
  dropOffsetHeight: 160,
};

const props = defineProps({
  value: { type: [Object, String] },
  label: { type: String },
  error: { type: String },
  format: { type: String },
  minuteInterval: { type: [Number, String] },
  secondInterval: { type: [Number, String] },

  hourRange: { type: Array },
  minuteRange: { type: Array },
  secondRange: { type: Array },

  hideDisabledHours: { type: Boolean, default: false },
  hideDisabledMinutes: { type: Boolean, default: false },
  hideDisabledSeconds: { type: Boolean, default: false },
  hideDisabledItems: { type: Boolean, default: false },

  hideClearButton: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  closeOnComplete: { type: Boolean, default: false },

  id: { type: String },
  name: { type: String },
  inputClass: { type: [String, Object, Array] },
  placeholder: { type: String },
  tabindex: { type: [Number, String], default: 0 },
  inputWidth: { type: String },
  autocomplete: { type: String, default: 'off' },

  hourLabel: { type: String },
  minuteLabel: { type: String },
  secondLabel: { type: String },
  apmLabel: { type: String },

  amText: { type: String },
  pmText: { type: String },

  blurDelay: { type: [Number, String] },
  advancedKeyboard: { type: Boolean, default: false },
  lazy: { type: Boolean, default: false },
  autoScroll: { type: Boolean, default: false },
  dropDirection: { type: String, default: 'down' },
  dropOffsetHeight: { type: [Number, String] },
  containerId: { type: String },
  appendToBody: { type: Boolean, default: false },
  manualInput: { type: Boolean, default: false },
  manualInputTimeout: { type: [Number, String] },
  hideDropdown: { type: Boolean, default: false },
  fixedDropdownButton: { type: Boolean, default: false },
  debugMode: { type: Boolean, default: false },

  rounded: { type: Boolean, default: false },
  size: { type: String },
  readonly: { type: Boolean, default: false },
});

const emit = defineEmits(['input', 'change', 'error', 'focus', 'blur', 'open', 'close']);

// Template refs
const rootEl = ref(null);
const input = ref(null);
const dropdown = ref(null);

// Reactive state
const timeValue = ref({});
const hours = ref([]);
const minutes = ref([]);
const seconds = ref([]);
const apms = ref([]);
const isActive = ref(false);
const showDropdown = ref(false);
const isFocusing = ref(false);
const debounceTimer = ref(undefined);
const hourType = ref('HH');
const minuteType = ref('mm');
const secondType = ref('');
const apmType = ref('');
const hour = ref('');
const minute = ref('');
const second = ref('');
const apm = ref('');
const fullValues = ref(undefined);
const bakDisplayTime = ref(undefined);
const doClearApmChecking = ref(false);
const selectionTimer = ref(undefined);
const kbInputTimer = ref(undefined);
const kbInputLog = ref('');
const bakCurrentPos = ref(undefined);
const forceDropOnTop = ref(false);

// Computed

const opts = computed(() => {
  const result = {};
  Object.keys(DEFAULT_OPTIONS).forEach((key) => {
    result[key] = DEFAULT_OPTIONS[key];
  });
  if (props.format && props.format.length) {
    result.format = props.format;
  }
  if (props.minuteInterval !== undefined && !isNaN(props.minuteInterval) && Number(props.minuteInterval) > 0 && Number(props.minuteInterval) <= 60) {
    result.minuteInterval = Number(props.minuteInterval);
  }
  if (props.secondInterval !== undefined && !isNaN(props.secondInterval) && Number(props.secondInterval) > 0 && Number(props.secondInterval) <= 60) {
    result.secondInterval = Number(props.secondInterval);
  }
  if (props.hourRange !== undefined) { result.hourRange = props.hourRange; }
  if (props.minuteRange !== undefined) { result.minuteRange = props.minuteRange; }
  if (props.secondRange !== undefined) { result.secondRange = props.secondRange; }
  if (props.hideDisabledHours !== undefined) { result.hideDisabledHours = props.hideDisabledHours; }
  if (props.hideDisabledMinutes !== undefined) { result.hideDisabledMinutes = props.hideDisabledMinutes; }
  if (props.hideDisabledSeconds !== undefined) { result.hideDisabledSeconds = props.hideDisabledSeconds; }
  if (props.hideDisabledItems !== undefined) {
    result.hideDisabledItems = props.hideDisabledItems || props.hideDisabledHours || props.hideDisabledMinutes || props.hideDisabledSeconds;
  }
  if (props.blurDelay !== undefined && !isNaN(props.blurDelay) && Number(props.blurDelay) > 0) {
    result.blurDelay = Number(props.blurDelay);
  }
  if (props.manualInputTimeout !== undefined && !isNaN(props.manualInputTimeout) && Number(props.manualInputTimeout) > 0) {
    result.manualInputTimeout = Number(props.manualInputTimeout);
  }
  if (props.dropOffsetHeight !== undefined && !isNaN(props.dropOffsetHeight) && Number(props.dropOffsetHeight) > 0) {
    result.dropOffsetHeight = Number(props.dropOffsetHeight);
  }
  if (props.hideDropdown !== undefined) { result.hideDropdown = props.hideDropdown; }
  return result;
});

const useStringValue = computed(() => typeof props.value === 'string');

const formatString = computed(() => opts.value.format || DEFAULT_OPTIONS.format);

const inUse = computed(() => {
  const typesInUse = [];
  const tokensInUse = [];
  const formatStr = formatString.value;
  CONFIG.HOUR_TOKENS.forEach((token) => {
    if (formatStr.includes(token)) { typesInUse.push('hour'); tokensInUse.push(token); }
  });
  CONFIG.MINUTE_TOKENS.forEach((token) => {
    if (formatStr.includes(token)) { typesInUse.push('minute'); tokensInUse.push(token); }
  });
  CONFIG.SECOND_TOKENS.forEach((token) => {
    if (formatStr.includes(token)) { typesInUse.push('second'); tokensInUse.push(token); }
  });
  CONFIG.APM_TOKENS.forEach((token) => {
    if (formatStr.includes(token)) { typesInUse.push('apm'); tokensInUse.push(token); }
  });
  const sortedTypes = [];
  const sortedTokens = [];
  const formatParts = formatStr.split(/[^A-Za-z]+/);
  formatParts.forEach((part) => {
    const idx = tokensInUse.indexOf(part);
    if (idx !== -1 && !sortedTypes.includes(typesInUse[idx])) {
      sortedTypes.push(typesInUse[idx]);
      sortedTokens.push(part);
    }
  });
  return {
    hour: typesInUse.includes('hour'),
    minute: typesInUse.includes('minute'),
    second: typesInUse.includes('second'),
    apm: typesInUse.includes('apm'),
    types: sortedTypes,
    tokens: sortedTokens,
  };
});

const displayTime = computed(() => {
  let str = formatString.value;
  if (inUse.value.hour && hourType.value) {
    str = str.replace(new RegExp(hourType.value, 'g'), hour.value || hourType.value);
  }
  if (inUse.value.minute && minuteType.value) {
    str = str.replace(new RegExp(minuteType.value, 'g'), minute.value || minuteType.value);
  }
  if (inUse.value.second && secondType.value) {
    str = str.replace(new RegExp(secondType.value, 'g'), second.value || secondType.value);
  }
  if (inUse.value.apm && apmType.value) {
    str = str.replace(new RegExp(apmType.value, 'g'), apm.value || apmType.value);
  }
  return str;
});

const customDisplayTime = computed(() => {
  let str = displayTime.value;
  if (props.amText && apm.value === 'am') { str = str.replace(/am/gi, props.amText); }
  if (props.pmText && apm.value === 'pm') { str = str.replace(/pm/gi, props.pmText); }
  return str;
});

const inputIsEmpty = computed(() => formatString.value === displayTime.value);

const allValueSelected = computed(() => {
  if (inUse.value.hour && !hour.value) { return false; }
  if (inUse.value.minute && !minute.value) { return false; }
  if (inUse.value.second && !second.value) { return false; }
  if (inUse.value.apm && !apm.value) { return false; }
  return true;
});

const columnsSequence = computed(() => inUse.value.types);

const showClearBtn = computed(() => !props.hideClearButton && !props.disabled && !inputIsEmpty.value);

const showDropdownBtn = computed(() => props.fixedDropdownButton || (props.hideDropdown && isActive.value && !showDropdown.value));

const baseOn12Hours = computed(() => hourType.value === 'h' || hourType.value === 'hh');

const hourRangeIn24HrFormat = computed(() => {
  if (!opts.value.hourRange) { return null; }
  if (!baseOn12Hours.value) { return opts.value.hourRange; }
  const result = [];
  opts.value.hourRange.forEach((range) => {
    if (Array.isArray(range)) {
      const start = range[0];
      const end = range[1];
      if (typeof start === 'string' && is12hRange(start)) {
        const m = match12hRange(start);
        const h = parseInt(m[1]);
        const meridiem = m[2].toLowerCase();
        result.push([meridiem === 'a' ? (h === 12 ? 0 : h) : h === 12 ? 12 : h + 12, ...(end !== undefined ? [end] : [])]);
      } else {
        result.push(range);
      }
    } else {
      result.push(range);
    }
  });
  return result;
});

const restrictedHourRange = computed(() => {
  if (!opts.value.hourRange) { return null; }
  const result = [];
  const rangeList = baseOn12Hours.value ? opts.value.hourRange : hourRangeIn24HrFormat.value;
  if (!rangeList) { return null; }
  rangeList.forEach((range) => {
    if (Array.isArray(range)) {
      const [start, end] = range;
      for (let i = Number(start); i <= Number(end); i++) {
        if (!result.includes(i)) { result.push(i); }
      }
    } else {
      const val = Number(range);
      if (!result.includes(val)) { result.push(val); }
    }
  });
  return result;
});

const validHoursList = computed(() => {
  if (!props.manualInput) { return []; }
  return hours.value.filter((hr) => !isDisabled('hour', hr));
});

const has = computed(() => {
  const result = { am: false, pm: false, customApmText: false };
  if (!inUse.value.apm) { return result; }
  if (baseOn12Hours.value) {
    if (!restrictedHourRange.value) {
      result.am = true;
      result.pm = true;
    } else {
      restrictedHourRange.value.forEach((hr) => {
        if (hr >= 1 && hr <= 12) {
          const amHours = is12hRange(String(hr)) ? match12hRange(String(hr)) : null;
          if (amHours) {
            if (amHours[2].toLowerCase() === 'a') { result.am = true; }
            else { result.pm = true; }
          }
        }
      });
      if (!result.am && !result.pm) { result.am = true; result.pm = true; }
    }
  }
  if (props.amText || props.pmText) { result.customApmText = true; }
  return result;
});

const minuteRangeList = computed(() => {
  if (!opts.value.minuteRange) { return null; }
  return renderRangeList(opts.value.minuteRange, 'minute');
});

const secondRangeList = computed(() => {
  if (!opts.value.secondRange) { return null; }
  return renderRangeList(opts.value.secondRange, 'second');
});

const hourLabelText = computed(() => props.hourLabel !== undefined ? props.hourLabel : 'HH');
const minuteLabelText = computed(() => props.minuteLabel !== undefined ? props.minuteLabel : 'MM');
const secondLabelText = computed(() => props.secondLabel !== undefined ? props.secondLabel : 'SS');
const apmLabelText = computed(() => props.apmLabel !== undefined ? props.apmLabel : 'AM/PM');

const inputWidthStyle = computed(() => {
  if (props.inputWidth && props.inputWidth.trim().length) { return { width: props.inputWidth }; }
  return {};
});

const tokenRegexBase = computed(() => {
  const tokens = CONFIG.HOUR_TOKENS.concat(CONFIG.MINUTE_TOKENS, CONFIG.SECOND_TOKENS, CONFIG.APM_TOKENS);
  return tokens.join('|');
});

const tokenChunks = computed(() => {
  if (!props.manualInput) { return []; }
  const formatStr = formatString.value;
  const regex = new RegExp(`(${tokenRegexBase.value})|.`, 'g');
  const chunks = [];
  let match;
  while ((match = regex.exec(formatStr)) !== null) {
    const token = match[1];
    if (token) { chunks.push({ token, type: getTokenType(token), len: token.length }); }
  }
  return chunks;
});

const needsPosCalibrate = computed(() => tokenChunks.value.some((c) => c.len < 2));

const tokenChunksPos = computed(() => {
  if (!props.manualInput) { return []; }
  const formatStr = formatString.value;
  const result = [];
  tokenChunks.value.forEach((chunk) => {
    const startPos = formatStr.indexOf(chunk.token);
    const endPos = startPos + chunk.len;
    result.push({ ...chunk, start: startPos, end: endPos });
  });
  if (needsPosCalibrate.value) {
    let offset = 0;
    result.forEach((chunk) => {
      chunk.start += offset;
      if (chunk.len < 2) { offset += 2 - chunk.len; }
      chunk.end = chunk.start + Math.max(chunk.len, 2);
    });
  }
  return result;
});

const invalidValues = computed(() => {
  const result = [];
  if (!props.manualInput || inputIsEmpty.value) { return result; }
  const inputVal = input.value ? input.value.value : '';
  tokenChunksPos.value.forEach((chunk) => {
    const sliceVal = inputVal.slice(chunk.start, chunk.end);
    if (sliceVal && sliceVal !== chunk.token && !isValidValue(chunk.token, sliceVal)) {
      result.push(chunk.type);
    }
  });
  return result;
});

const hasInvalidInput = computed(() => Boolean(invalidValues.value.length));

const autoDirectionEnabled = computed(() => props.dropDirection === 'auto');

const dropdownDirClass = computed(() => {
  if (autoDirectionEnabled.value) { return forceDropOnTop.value ? 'drop-up' : 'drop-down'; }
  return props.dropDirection === 'up' ? 'drop-up' : 'drop-down';
});

// Watchers

watch(() => opts.value.format, renderFormat);
watch(() => opts.value.minuteInterval, (v) => renderList('minute', v));
watch(() => opts.value.secondInterval, (v) => renderList('second', v));
watch(() => props.value, readValues, { deep: true });
watch(displayTime, fillValues);
watch(() => props.disabled, (toDisabled) => {
  if (toDisabled) { isActive.value = false; showDropdown.value = false; }
});
watch(() => invalidValues.value.length, (newLen, oldLen) => {
  if (newLen && !oldLen) { emit('error', invalidValues.value); }
  else if (!newLen && oldLen) { emit('error', []); }
});

// Lifecycle

onMounted(() => {
  window.clearTimeout(debounceTimer.value);
  window.clearTimeout(selectionTimer.value);
  window.clearTimeout(kbInputTimer.value);
  renderFormat();
});

onBeforeUnmount(() => {
  window.clearTimeout(debounceTimer.value);
  window.clearTimeout(selectionTimer.value);
  window.clearTimeout(kbInputTimer.value);
});

// Methods

function formatValue(token, i) {
  switch (token) {
    case 'H':
    case 'k':
    case 'h':
      return String(i);
    case 'HH':
    case 'kk':
    case 'hh':
    case 'mm':
    case 'ss':
    case 'm':
    case 's':
      return i < 10 ? `0${i}` : String(i);
    default:
      return '';
  }
}

function checkAcceptingType(validValues, formatToken, currentValue) {
  if (!validValues || !validValues.length || !formatToken || !currentValue) { return false; }
  const smallValue = typeof currentValue === 'number' ? currentValue : parseInt(currentValue, 10);
  return validValues.includes(smallValue);
}

function renderFormat(newFormat) {
  const newFmt = newFormat || opts.value.format || DEFAULT_OPTIONS.format;
  let newHourType = '';
  let newMinuteType = '';
  let newSecondType = '';
  let newApmType = '';
  CONFIG.HOUR_TOKENS.forEach((token) => { if (newFmt.includes(token)) { newHourType = token; } });
  CONFIG.MINUTE_TOKENS.forEach((token) => { if (newFmt.includes(token)) { newMinuteType = token; } });
  CONFIG.SECOND_TOKENS.forEach((token) => { if (newFmt.includes(token)) { newSecondType = token; } });
  CONFIG.APM_TOKENS.forEach((token) => { if (newFmt.includes(token)) { newApmType = token; } });
  hourType.value = newHourType;
  minuteType.value = newMinuteType;
  secondType.value = newSecondType;
  apmType.value = newApmType;
  renderHoursList();
  if (newMinuteType) { renderList('minute'); } else { minutes.value = []; minute.value = ''; }
  if (newSecondType) { renderList('second'); } else { seconds.value = []; second.value = ''; }
  if (newApmType) { renderApmList(); } else { apms.value = []; apm.value = ''; }
  readValues();
}

function renderHoursList() {
  const hoursToken = hourType.value;
  if (!hoursToken) { hours.value = []; return; }
  const startH = hoursToken === 'k' || hoursToken === 'kk' ? 1 : 0;
  const endH = hoursToken === 'k' || hoursToken === 'kk' ? 24 : (hoursToken === 'h' || hoursToken === 'hh' ? 12 : 23);
  const list = [];
  for (let i = startH; i <= endH; i++) {
    const val = formatValue(hoursToken, i);
    if (val) { list.push(val); }
  }
  hours.value = list;
}

function renderList(type, interval) {
  const tokenMap = { minute: minuteType.value, second: secondType.value };
  const token = tokenMap[type];
  if (!token) { return; }
  const step = interval || (type === 'minute' ? opts.value.minuteInterval : opts.value.secondInterval);
  const list = [];
  for (let i = 0; i < 60; i += step) { list.push(formatValue(token, i)); }
  if (type === 'minute') { minutes.value = list; } else { seconds.value = list; }
}

function renderApmList() {
  if (!apmType.value) { apms.value = []; return; }
  apms.value = apmType.value === 'A' ? ['AM', 'PM'] : ['am', 'pm'];
}

function readValues() {
  if (props.value === undefined || props.value === null) { return; }
  if (useStringValue.value) { readStringValues(props.value); }
  else { readObjectValues(props.value); }
}

function readObjectValues(objValue) {
  if (!objValue || typeof objValue !== 'object') { return; }
  CONFIG.BASIC_TYPES.forEach((type) => {
    const token = getTokenByType(type);
    if (!token) { return; }
    const objKey = type === 'apm' ? apmType.value : token;
    const rawVal = objValue[token] !== undefined ? objValue[token] : (objValue[objKey] !== undefined ? objValue[objKey] : null);
    if (rawVal === null) { return; }
    const sanitized = sanitizedValue(token, rawVal);
    if (type === 'hour') { hour.value = sanitized; }
    else if (type === 'minute') { minute.value = sanitized; }
    else if (type === 'second') { second.value = sanitized; }
    else if (type === 'apm') { apm.value = sanitized; }
  });
}

function getMatchAllByRegex(str, regex) {
  if (!str || !regex) { return []; }
  return polyfillMatchAll(str, regex);
}

function readStringValues(strValue) {
  if (!strValue || typeof strValue !== 'string') { return; }
  const fmtStr = formatString.value;
  let processedValue = strValue;
  if ((props.amText?.length || props.pmText?.length)) {
    processedValue = replaceCustomApmText(processedValue);
  }
  setValueFromString(processedValue, fmtStr);
}

function polyfillMatchAll(str, regex) {
  const result = [];
  let match;
  const re = new RegExp(regex.source, regex.flags.includes('g') ? regex.flags : `${regex.flags}g`);
  while ((match = re.exec(str)) !== null) { result.push(match); }
  return result;
}

function addFallbackValues(rawData) {
  const data = rawData || {};
  CONFIG.BASIC_TYPES.forEach((type) => { if (!data[type]) { data[type] = ''; } });
  return data;
}

function setValueFromString(strValue, formatStr) {
  if (!strValue || !formatStr) { return; }
  const tokenRegex = new RegExp(`(${tokenRegexBase.value})`, 'g');
  const tokens = formatStr.match(tokenRegex);
  if (!tokens || !tokens.length) { return; }
  let regexStr = formatStr;
  tokens.forEach((token) => {
    const tokenRxStr = getTokenRegex(token);
    if (tokenRxStr) { regexStr = regexStr.replace(token, tokenRxStr); }
  });
  const matches = strValue.match(new RegExp(`^${regexStr}$`));
  if (!matches) { return; }
  tokens.forEach((token, idx) => {
    const val = matches[idx + 1];
    if (val === token) { return; }
    const type = getTokenType(token);
    const sanitized = sanitizedValue(token, val);
    if (type === 'hour') { hour.value = sanitized; }
    else if (type === 'minute') { minute.value = sanitized; }
    else if (type === 'second') { second.value = sanitized; }
    else if (type === 'apm') { apm.value = sanitized; }
  });
}

function fillValues(newDisplayTime) {
  const fmtStr = formatString.value;
  if (!newDisplayTime || newDisplayTime === fmtStr) { return; }
  const tokenRegex = new RegExp(`(${tokenRegexBase.value})`, 'g');
  const tokens = fmtStr.match(tokenRegex);
  if (!tokens) { return; }
  let regexStr = fmtStr;
  tokens.forEach((token) => {
    const tokenRxStr = getTokenRegex(token);
    if (tokenRxStr) { regexStr = regexStr.replace(token, tokenRxStr); }
  });
  const matches = newDisplayTime.match(new RegExp(`^${regexStr}$`));
  if (!matches) { return; }
  const data = {};
  tokens.forEach((token, idx) => {
    const val = matches[idx + 1];
    const type = getTokenType(token);
    if (type && val !== token) { data[type] = val; }
  });
  if (!data.hour && hourType.value) { data.hour = ''; }
  if (!data.minute && minuteType.value) { data.minute = ''; }
  if (!data.second && secondType.value) { data.second = ''; }
  if (!data.apm && apmType.value) { data.apm = ''; }
  timeValue.value = addFallbackValues(data);
}

function getFullData() {
  const data = {};
  if (hourType.value) { data[hourType.value] = hour.value; }
  if (minuteType.value) { data[minuteType.value] = minute.value; }
  if (secondType.value) { data[secondType.value] = second.value; }
  if (apmType.value) { data[apmType.value] = apm.value; }
  return data;
}

function emitTimeValue() {
  if (inputIsEmpty.value) {
    if (useStringValue.value) { emit('input', ''); emit('change', ''); }
    else { emit('input', getFullData()); emit('change', getFullData()); }
    return;
  }
  if (useStringValue.value) {
    emit('input', customDisplayTime.value);
    if (!props.lazy) { emit('change', customDisplayTime.value); }
  } else {
    const data = getFullData();
    emit('input', data);
    if (!props.lazy) { emit('change', data); }
  }
  fullValues.value = getFullData();
}

function translate12hRange(value) {
  const valueMatch = match12hRange(value);
  if (!valueMatch) { return value; }
  const hour12 = parseInt(valueMatch[1]);
  const meridiem = valueMatch[2].toLowerCase();
  if (meridiem === 'a') { return hour12 === 12 ? 0 : hour12; }
  return hour12 === 12 ? 12 : hour12 + 12;
}

function isDisabled(type, value) {
  if (!value || value === type) { return false; }
  if (type === 'hour') { return isDisabledHour(value); }
  if (type === 'minute') {
    if (!opts.value.minuteRange) { return false; }
    if (minuteRangeList.value) { return !minuteRangeList.value.includes(value); }
    return notInInterval(parseInt(value, 10), opts.value.minuteInterval);
  }
  if (type === 'second') {
    if (!opts.value.secondRange) { return false; }
    if (secondRangeList.value) { return !secondRangeList.value.includes(value); }
    return notInInterval(parseInt(value, 10), opts.value.secondInterval);
  }
  if (type === 'apm') {
    if (!opts.value.hourRange) { return false; }
    const lcVal = lowerCasedApm(value);
    if (lcVal === 'am') { return !has.value.am; }
    if (lcVal === 'pm') { return !has.value.pm; }
  }
  return false;
}

function isDisabledHour(value) {
  if (!opts.value.hourRange) { return false; }
  const numVal = parseInt(value, 10);
  const rangeList = hourRangeIn24HrFormat.value;
  if (!rangeList) { return false; }
  let allowed = false;
  rangeList.forEach((range) => {
    if (allowed) { return; }
    if (Array.isArray(range)) {
      const start = is12hRange(String(range[0])) ? translate12hRange(String(range[0])) : Number(range[0]);
      const end = is12hRange(String(range[1])) ? translate12hRange(String(range[1])) : Number(range[1]);
      if (numVal >= start && numVal <= end) { allowed = true; }
    } else {
      const val = is12hRange(String(range)) ? translate12hRange(String(range)) : Number(range);
      if (numVal === val) { allowed = true; }
    }
  });
  return !allowed;
}

function notInInterval(value, interval) {
  if (!interval || interval <= 1) { return false; }
  return value % interval !== 0;
}

function renderRangeList(range, type) {
  if (!range || !range.length) { return null; }
  const token = type === 'minute' ? minuteType.value : secondType.value;
  if (!token) { return null; }
  const result = [];
  range.forEach((item) => {
    if (Array.isArray(item)) {
      const [start, end] = item;
      for (let i = Number(start); i <= Number(end); i++) {
        const formatted = formatValue(token, i);
        if (formatted && !result.includes(formatted)) { result.push(formatted); }
      }
    } else {
      const formatted = formatValue(token, Number(item));
      if (formatted && !result.includes(formatted)) { result.push(formatted); }
    }
  });
  return result;
}

function forceApmSelection() {
  if (!apmType.value || apm.value) { return; }
  if (has.value.am) { apm.value = apmType.value === 'A' ? 'AM' : 'am'; }
  else if (has.value.pm) { apm.value = apmType.value === 'A' ? 'PM' : 'pm'; }
}

function emptyApmSelection() {
  apm.value = '';
}

function apmDisplayText(apmVal) {
  if (!apmVal) { return ''; }
  const lc = lowerCasedApm(apmVal);
  if (lc === 'am' && props.amText) { return props.amText; }
  if (lc === 'pm' && props.pmText) { return props.pmText; }
  return apmVal;
}

function toggleActive() {
  if (props.disabled) { return; }
  if (isActive.value) {
    isActive.value = false;
    showDropdown.value = false;
    emit('close');
  } else {
    isActive.value = true;
    if (!props.hideDropdown) { showDropdown.value = true; emit('open'); }
  }
}

function setDropdownState(toOpen, fromButton) {
  if (props.disabled) { return; }
  if (toOpen) {
    if (!isActive.value) { isActive.value = true; }
    if (autoDirectionEnabled.value) { checkDropDirection(); }
    if (props.appendToBody) { appendDropdownToBody(); }
    showDropdown.value = true;
    emit('open');
  } else {
    if (props.appendToBody) { removeDropdownFromBody(); }
    showDropdown.value = false;
    if (!fromButton) { isActive.value = false; }
    emit('close');
  }
}

function appendDropdownToBody() {
  if (!dropdown.value || !rootEl.value) { return; }
  document.body.appendChild(dropdown.value);
  updateDropdownPos();
}

function updateDropdownPos() {
  if (!dropdown.value || !rootEl.value) { return; }
  const elRect = rootEl.value.getBoundingClientRect();
  const isDropUp = dropdownDirClass.value === 'drop-up';
  dropdown.value.style.position = 'fixed';
  dropdown.value.style.left = `${elRect.left}px`;
  dropdown.value.style.top = isDropUp ? '' : `${elRect.bottom}px`;
  dropdown.value.style.bottom = isDropUp ? `${window.innerHeight - elRect.top}px` : '';
}

function removeDropdownFromBody() {
  if (!dropdown.value) { return; }
  const parent = dropdown.value.parentElement;
  if (parent && parent !== rootEl.value) { rootEl.value?.appendChild(dropdown.value); }
}

function blurEvent() {
  if (isFocusing.value) { return; }
  if (isActive.value) { setDropdownState(false); }
}

function select(type, val) {
  if (isDisabled(type, val)) { return; }
  if (type === 'hour') {
    hour.value = val;
    if (doClearApmChecking.value) { doClearApmChecking.value = false; emptyApmSelection(); }
    if (inUse.value.apm) { forceApmSelection(); }
  } else if (type === 'minute') {
    minute.value = val;
  } else if (type === 'second') {
    second.value = val;
  } else if (type === 'apm') {
    apm.value = val;
  }
  emitTimeValue();
  if (props.closeOnComplete && allValueSelected.value) { setDropdownState(false); }
  if (props.autoScroll) { nextTick(() => { scrollToSelected(type, val); }); }
}

function clearTime() {
  hour.value = '';
  minute.value = '';
  second.value = '';
  apm.value = '';
  emitTimeValue();
}

function checkForAutoScroll() {
  if (!props.autoScroll) { return; }
  scrollToSelectedValues();
}

function scrollToSelected(type, value) {
  if (!dropdown.value) { return; }
  const listMap = { hour: '.hours', minute: '.minutes', second: '.seconds', apm: '.apms' };
  const listEl = dropdown.value.querySelector(listMap[type]);
  if (!listEl) { return; }
  const activeItem = listEl.querySelector('li.active');
  if (!activeItem) { return; }
  listEl.scrollTop = activeItem.offsetTop - listEl.offsetTop;
}

function scrollToSelectedValues() {
  CONFIG.BASIC_TYPES.forEach((type) => {
    const valMap = { hour: hour.value, minute: minute.value, second: second.value, apm: apm.value };
    const val = valMap[type];
    if (val) { scrollToSelected(type, val); }
  });
}

function onFocus() {
  if (props.disabled) { return; }
  isFocusing.value = true;
  if (!isActive.value) { setDropdownState(true); }
  if (props.manualInput) { nextTick(() => { selectFirstSlot(); }); }
  emit('focus');
}

function escBlur() {
  if (input.value) { input.value.blur(); }
}

function debounceBlur() {
  window.clearTimeout(debounceTimer.value);
  debounceTimer.value = window.setTimeout(() => {
    isFocusing.value = false;
    onBlur();
  }, opts.value.blurDelay);
}

function onBlur() {
  if (isFocusing.value) { return; }
  setDropdownState(false);
  if (props.lazy) {
    if (useStringValue.value) { emit('change', customDisplayTime.value); }
    else { emit('change', getFullData()); }
  }
  emit('blur');
}

function keepFocusing() {
  isFocusing.value = true;
  window.clearTimeout(debounceTimer.value);
}

function onTab(type, value, event) {
  if (event.shiftKey) { return; }
  const typeIdx = columnsSequence.value.indexOf(type);
  if (typeIdx < columnsSequence.value.length - 1) {
    event.preventDefault();
    const nextType = columnsSequence.value[typeIdx + 1];
    const nextListMap = { hour: '.hours', minute: '.minutes', second: '.seconds', apm: '.apms' };
    if (dropdown.value) {
      const nextList = dropdown.value.querySelector(nextListMap[nextType]);
      if (nextList) {
        const activeOrFirst = nextList.querySelector('li.active') || nextList.querySelector('li:not(.hint):not([disabled])');
        if (activeOrFirst) { activeOrFirst.focus(); }
      }
    }
  }
}

function validItemsInCol(type) {
  if (!dropdown.value) { return []; }
  const listMap = { hour: '.hours', minute: '.minutes', second: '.seconds', apm: '.apms' };
  const listEl = dropdown.value.querySelector(listMap[type]);
  if (!listEl) { return []; }
  return Array.from(listEl.querySelectorAll('li:not(.hint):not([disabled])'));
}

function activeItemInCol(type) {
  if (!dropdown.value) { return null; }
  const listMap = { hour: '.hours', minute: '.minutes', second: '.seconds', apm: '.apms' };
  const listEl = dropdown.value.querySelector(listMap[type]);
  return listEl ? listEl.querySelector('li.active') : null;
}

function getClosestSibling(el, towardNext) {
  if (!el) { return null; }
  const sibling = towardNext ? el.nextElementSibling : el.previousElementSibling;
  if (!sibling) { return null; }
  if (sibling.hasAttribute('disabled') || sibling.classList.contains('hint')) {
    return getClosestSibling(sibling, towardNext);
  }
  return sibling;
}

function prevItem(type, value) {
  const active = activeItemInCol(type);
  const prev = getClosestSibling(active, false);
  if (prev) { prev.focus(); }
}

function nextItem(type, value) {
  const active = activeItemInCol(type);
  const next = getClosestSibling(active, true);
  if (next) { next.focus(); }
}

function getSideColumnName(type, toLeft) {
  const sequence = columnsSequence.value;
  const idx = sequence.indexOf(type);
  if (toLeft) { return idx > 0 ? sequence[idx - 1] : null; }
  return idx < sequence.length - 1 ? sequence[idx + 1] : null;
}

function getFirstItemInSideColumn(type, toLeft) {
  const sideCol = getSideColumnName(type, toLeft);
  if (!sideCol) { return null; }
  const items = validItemsInCol(sideCol);
  return items.length ? items[0] : null;
}

function getActiveItemInSideColumn(type, toLeft) {
  const sideCol = getSideColumnName(type, toLeft);
  if (!sideCol) { return null; }
  return activeItemInCol(sideCol);
}

function toLeftColumn(type) {
  const target = getActiveItemInSideColumn(type, true) || getFirstItemInSideColumn(type, true);
  if (target) { target.focus(); }
}

function toRightColumn(type) {
  const target = getActiveItemInSideColumn(type, false) || getFirstItemInSideColumn(type, false);
  if (target) { target.focus(); }
}

function onMouseDown(event) {
  if (!isActive.value) { return; }
  keepFocusing();
}

function keyDownHandler(event) {
  if (!props.manualInput) { return; }
  keyboardInput(event);
}

function onCompostionStart() {
  // composition start noop
}

function onCompostionEnd() {
  if (props.manualInput && input.value) {
    input.value.value = customDisplayTime.value || formatString.value;
    selectFirstSlot();
  }
}

function pasteHandler(event) {
  if (!props.manualInput) { return; }
  event.preventDefault();
  const pastedText = (event.clipboardData || window.clipboardData)?.getData('text') || '';
  if (!pastedText) { return; }
  const processed = replaceCustomApmText(pastedText);
  setValueFromString(processed, formatString.value);
  emitTimeValue();
}

function arrowHandler(event) {
  const key = event.key;
  const chunk = getCurrentTokenChunk();
  if (!chunk) { return; }
  const type = chunk.type;
  const listValues = { hour: hours.value, minute: minutes.value, second: seconds.value, apm: apms.value };
  const list = listValues[type] || [];
  const currentVal = { hour: hour.value, minute: minute.value, second: second.value, apm: apm.value }[type];
  const currentIdx = list.indexOf(currentVal);
  if (key === 'ArrowUp') {
    event.preventDefault();
    const prevIdx = currentIdx > 0 ? currentIdx - 1 : list.length - 1;
    select(type, list[prevIdx]);
  } else if (key === 'ArrowDown') {
    event.preventDefault();
    const nextIdx = currentIdx < list.length - 1 ? currentIdx + 1 : 0;
    select(type, list[nextIdx]);
  }
}

function tabHandler(event) {
  if (!props.manualInput) { return; }
  const chunk = getCurrentTokenChunk();
  if (!chunk) { return; }
  const lastChunk = tokenChunksPos.value[tokenChunksPos.value.length - 1];
  if (chunk.token !== lastChunk.token) {
    event.preventDefault();
    toLateralToken(false);
  }
}

function keyboardInput(event) {
  const key = event.key;
  if (key === 'ArrowUp' || key === 'ArrowDown') { arrowHandler(event); return; }
  if (key === 'ArrowLeft') { event.preventDefault(); toLateralToken(true); return; }
  if (key === 'ArrowRight') { event.preventDefault(); toLateralToken(false); return; }
  if (key === 'Tab') { tabHandler(event); return; }
  if (key === 'Backspace' || key === 'Delete') {
    event.preventDefault();
    const chunk = getCurrentTokenChunk();
    if (chunk) { select(chunk.type, ''); setSanitizedValueToSection(chunk, ''); }
    return;
  }
  if (key.length === 1 && /\d|[aApP]/.test(key)) { event.preventDefault(); setKbInput(key); }
}

function clearKbInputLog() {
  kbInputLog.value = '';
}

function debounceClearKbLog() {
  window.clearTimeout(kbInputTimer.value);
  kbInputTimer.value = window.setTimeout(clearKbInputLog, opts.value.manualInputTimeout);
}

function setKbInput(key) {
  const chunk = getCurrentTokenChunk();
  if (!chunk) { return; }
  const prevLog = kbInputLog.value;
  const newLog = prevLog + key;
  kbInputLog.value = newLog;
  debounceClearKbLog();
  if (chunk.type === 'hour') { setManualHour(newLog, chunk); return; }
  if (chunk.type === 'apm') {
    const lc = key.toLowerCase();
    if (lc === 'a' || lc === 'p') {
      const newApm = lc === 'a' ? (apmType.value === 'A' ? 'AM' : 'am') : (apmType.value === 'A' ? 'PM' : 'pm');
      if (!isDisabled('apm', newApm)) {
        select('apm', newApm);
        setSanitizedValueToSection(chunk, newApm);
        clearKbInputLog();
      }
    }
    return;
  }
  if (chunk.type === 'minute' || chunk.type === 'second') {
    const numVal = parseInt(newLog, 10);
    if (!isNaN(numVal)) {
      const token = getTokenByType(chunk.type);
      const formatted = formatValue(token, numVal);
      const list = chunk.type === 'minute' ? minutes.value : seconds.value;
      const closestItem = getClosestValidItemInCol(list, formatted, chunk.type);
      if (closestItem !== null) {
        select(chunk.type, closestItem);
        setSanitizedValueToSection(chunk, closestItem);
        if (newLog.length >= 2) { clearKbInputLog(); toNextSlot(); }
      }
    }
  }
}

function onChange() {
  if (!props.manualInput) { emitTimeValue(); }
}

function getNearestChunkByPos(pos) {
  if (!tokenChunksPos.value.length) { return null; }
  let nearest = tokenChunksPos.value[0];
  let minDist = Math.abs(pos - nearest.start);
  tokenChunksPos.value.forEach((chunk) => {
    const dist = Math.abs(pos - chunk.start);
    if (dist < minDist) { minDist = dist; nearest = chunk; }
  });
  return nearest;
}

function selectFirstValidValue() {
  if (!tokenChunksPos.value.length) { return; }
  debounceSetInputSelection(tokenChunksPos.value[0]);
}

function getClosestHourItem(numVal) {
  if (!validHoursList.value.length) { return null; }
  let closest = null;
  let minDiff = Infinity;
  validHoursList.value.forEach((hr) => {
    const hrNum = parseInt(hr, 10);
    const diff = Math.abs(numVal - hrNum);
    if (diff < minDiff) { minDiff = diff; closest = hr; }
  });
  return closest;
}

function getClosestValidItemInCol(list, targetVal, type) {
  if (!list || !list.length) { return null; }
  const targetNum = parseInt(targetVal, 10);
  let closest = null;
  let minDiff = Infinity;
  list.forEach((item) => {
    if (isDisabled(type, item)) { return; }
    const itemNum = parseInt(item, 10);
    const diff = Math.abs(targetNum - itemNum);
    if (diff < minDiff) { minDiff = diff; closest = item; }
  });
  return closest;
}

function setSanitizedValueToSection(chunk, val) {
  if (!input.value) { return; }
  const currentInputVal = input.value.value || '';
  let displayVal = val;
  if (val && val.length < 2 && chunk.len >= 2) { displayVal = val.padStart(2, '0'); }
  const newVal = currentInputVal.slice(0, chunk.start) + (displayVal || chunk.token) + currentInputVal.slice(chunk.end);
  input.value.value = newVal;
  debounceSetInputSelection(chunk);
}

function setManualHour(inputStr, chunk) {
  const numVal = parseInt(inputStr, 10);
  if (isNaN(numVal)) { return; }
  const closestItem = getClosestHourItem(numVal);
  if (closestItem !== null) {
    select('hour', closestItem);
    setSanitizedValueToSection(chunk, closestItem);
    if (inputStr.length >= 2 || numVal > 2) { clearKbInputLog(); toNextSlot(); }
  }
}

function debounceSetInputSelection(chunk) {
  window.clearTimeout(selectionTimer.value);
  bakCurrentPos.value = chunk;
  selectionTimer.value = window.setTimeout(() => { setInputSelectionRange(chunk); }, 0);
}

function setInputSelectionRange(chunk) {
  if (!input.value || !chunk) { return; }
  input.value.setSelectionRange(chunk.start, chunk.end);
}

function getCurrentTokenChunk() {
  if (!input.value || !tokenChunksPos.value.length) { return null; }
  const pos = input.value.selectionStart || 0;
  return getNearestChunkByPos(pos);
}

function selectFirstSlot() {
  if (!tokenChunksPos.value.length) { return; }
  if (input.value && !inputIsEmpty.value) { input.value.value = customDisplayTime.value; }
  else if (input.value) { input.value.value = formatString.value; }
  debounceSetInputSelection(tokenChunksPos.value[0]);
}

function toNextSlot() {
  const currentChunk = getCurrentTokenChunk();
  if (!currentChunk) { selectFirstValidValue(); return; }
  const lastChunk = tokenChunksPos.value[tokenChunksPos.value.length - 1];
  if (currentChunk.token !== lastChunk.token) { toLateralToken(false); }
}

function toLateralToken(toLeft) {
  const currentChunk = getCurrentTokenChunk();
  if (!currentChunk) { selectFirstValidValue(); return; }
  const currentChunkIndex = tokenChunksPos.value.findIndex((chk) => chk.token === currentChunk.token);
  if ((!toLeft && currentChunkIndex >= tokenChunksPos.value.length - 1) || (toLeft && currentChunkIndex === 0)) {
    if (props.debugMode) { debugLog(toLeft ? "You're in the leftmost slot already" : "You're in the rightmost slot already"); }
    return;
  }
  const targetSlotPos = toLeft ? tokenChunksPos.value[currentChunkIndex - 1] : tokenChunksPos.value[currentChunkIndex + 1];
  debounceSetInputSelection(targetSlotPos);
}

// Helpers

function isCustomApmText(inputData) {
  if (!inputData || !inputData.length) { return false; }
  if (props.amText && props.amText === inputData) { return apmType.value === 'A' ? 'AM' : 'am'; }
  if (props.pmText && props.pmText === inputData) { return apmType.value === 'A' ? 'PM' : 'pm'; }
  return false;
}

function replaceCustomApmText(inputString) {
  if (props.amText && props.amText.length && inputString.includes(props.amText)) {
    return inputString.replace(new RegExp(props.amText, 'g'), apmType.value === 'A' ? 'AM' : 'am');
  } else if (props.pmText && props.pmText.length && inputString.includes(props.pmText)) {
    return inputString.replace(new RegExp(props.pmText, 'g'), apmType.value === 'A' ? 'PM' : 'pm');
  }
  return inputString;
}

function checkDropDirection() {
  if (!rootEl.value) { return; }
  let container;
  if (props.containerId && props.containerId.length) {
    container = document.getElementById(props.containerId);
    if (!container && props.debugMode) {
      debugLog(`Container with id "${props.containerId}" not found. Fallback to document body.`);
    }
  }
  const el = rootEl.value;
  let spaceDown;
  if (container && container.offsetHeight) {
    spaceDown = container.offsetTop + container.offsetHeight - (el.offsetTop + el.offsetHeight);
  } else {
    const docHeight = Math.max(
      document.body.scrollHeight, document.documentElement.scrollHeight,
      document.body.offsetHeight, document.documentElement.offsetHeight,
      document.body.clientHeight, document.documentElement.clientHeight
    );
    spaceDown = docHeight - (el.offsetTop + el.offsetHeight);
  }
  forceDropOnTop.value = opts.value.dropOffsetHeight > spaceDown;
}

function is12hRange(value) {
  return /^\d{1,2}(a|p|A|P)$/.test(value);
}

function match12hRange(value) {
  return value.match(/^(\d{1,2})(a|p|A|P)$/);
}

function isNumber(value) {
  return !isNaN(parseFloat(value)) && isFinite(value);
}

function isBasicType(type) {
  return CONFIG.BASIC_TYPES.includes(type);
}

function lowerCasedApm(apmValue) {
  return (apmValue || '').toLowerCase();
}

function getTokenRegex(token) {
  switch (token) {
    case 'HH': return '([01][0-9]|2[0-3]|H{2})';
    case 'H': return '([0-9]{1}|1[0-9]|2[0-3]|H{1})';
    case 'hh': return '(0[1-9]|1[0-2]|h{2})';
    case 'h': return '([1-9]{1}|1[0-2]|h{1})';
    case 'kk': return '(0[1-9]|1[0-9]|2[0-4]|k{2})';
    case 'k': return '([1-9]{1}|1[0-9]|2[0-4]|k{1})';
    case 'mm': return '([0-5][0-9]|m{2})';
    case 'ss': return '([0-5][0-9]|s{2})';
    case 'm': return '([0-9]{1}|[1-5][0-9]|m{1})';
    case 's': return '([0-9]{1}|[1-5][0-9]|s{1})';
    case 'A': return '(AM|PM|A{1})';
    case 'a': return '(am|pm|a{1})';
    default: return '';
  }
}

function isEmptyValue(targetToken, testValue) {
  return !testValue || !testValue.length || (testValue && testValue === targetToken);
}

function isValidValue(targetToken, testValue) {
  if (!targetToken || isEmptyValue(targetToken, testValue)) { return false; }
  const tokenRegexStr = getTokenRegex(targetToken);
  if (!tokenRegexStr || !tokenRegexStr.length) { return false; }
  return new RegExp(`^${tokenRegexStr}$`).test(testValue);
}

function sanitizedValue(targetToken, inputValue) {
  if (isValidValue(targetToken, inputValue)) { return inputValue; }
  return '';
}

function getTokenType(token) {
  return inUse.value.types[inUse.value.tokens.indexOf(token)] || '';
}

function getTokenByType(type) {
  const map = { hour: hourType.value, minute: minuteType.value, second: secondType.value, apm: apmType.value };
  return map[type] || '';
}

function isMinuteOrSecond(type) {
  return ['minute', 'second'].includes(type);
}

function debugLog(logText) {
  if (!logText || !logText.length) { return; }
  let identifier = '';
  if (props.id) { identifier += `#${props.id}`; }
  if (props.name) { identifier += `[name=${props.name}]`; }
  if (props.inputClass) {
    let inputClasses = [];
    if (typeof props.inputClass === 'string') { inputClasses = props.inputClass.split(/\s/g); }
    else if (Array.isArray(props.inputClass)) { inputClasses = [].concat([], props.inputClass); }
    else if (typeof props.inputClass === 'object') {
      Object.keys(props.inputClass).forEach((clsName) => {
        if (props.inputClass[clsName]) { inputClasses.push(clsName); }
      });
    }
    for (const cls of inputClasses) {
      if (cls && cls.trim().length) { identifier += `.${cls.trim()}`; }
    }
  }
  const finalLogText = `DEBUG: ${logText}${identifier ? `\n\t(${identifier})` : ''}`;
  if (window.console.debug && typeof window.console.debug === 'function') { window.console.debug(finalLogText); }
  else { window.console.log(finalLogText); }
}
</script>

<template>
  <div ref="rootEl">
    <InputLabel :for="id" :value="label" v-if="label" />
    <div class="vue__time-picker time-picker" :style="inputWidthStyle">
      <input
        type="text"
        ref="input"
        :class="[
          inputClass,
          {
            'is-empty': inputIsEmpty,
            invalid: hasInvalidInput,
            'all-selected': allValueSelected,
            disabled: disabled,
            'has-custom-icon': $slots && $slots.icon,
          },
          {
            error: error,
            'rounded-md': !rounded,
            'rounded-full': rounded,
            'px-2 py-1 text-sm': size == 'sm',
            'border-gray-300 dark:border-gray-700': !error,
            'border-gray-300 opacity-60 focus:border-gray-300 focus:ring-0 dark:focus:border-gray-700': readonly,
            'focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-600 dark:focus:ring-primary-600': !readonly,
          },
        ]"
        class="block w-full shadow-xs placeholder:text-gray-400 dark:bg-gray-900 dark:text-gray-300 dark:placeholder:text-gray-600"
        :style="inputWidthStyle"
        :id="id"
        :name="name"
        :value="inputIsEmpty ? null : customDisplayTime"
        :placeholder="placeholder ? placeholder : formatString"
        :tabindex="disabled ? -1 : tabindex"
        :disabled="disabled"
        :readonly="!manualInput"
        :autocomplete="autocomplete"
        @focus="onFocus"
        @change="onChange"
        @blur="
          debounceBlur();
          blurEvent();
        "
        @mousedown="onMouseDown"
        @keydown="keyDownHandler"
        @compositionstart="onCompostionStart"
        @compositionend="onCompostionEnd"
        @paste="pasteHandler"
        @keydown.esc.exact="escBlur"
      />
      <div class="controls" v-if="showClearBtn || showDropdownBtn" tabindex="-1">
        <span
          v-if="!isActive && showClearBtn"
          class="clear-btn"
          tabindex="-1"
          :class="{ 'has-custom-btn': $slots && $slots.clearButton }"
          @click="clearTime"
        >
          <slot name="clearButton"><span class="char">&times;</span></slot>
        </span>
        <span
          v-if="showDropdownBtn"
          class="dropdown-btn"
          tabindex="-1"
          :class="{ 'has-custom-btn': $slots && $slots.dropdownButton }"
          @click="setDropdownState(fixedDropdownButton ? !showDropdown : true, true)"
          @mousedown="keepFocusing"
        >
          <slot name="dropdownButton"><span class="char">&dtrif;</span></slot>
        </span>
      </div>
      <div class="custom-icon" v-if="$slots && $slots.icon"><slot name="icon"></slot></div>
      <div class="time-picker-overlay" v-if="showDropdown" @click="toggleActive" tabindex="-1"></div>
      <div
        class="dropdown"
        ref="dropdown"
        v-show="showDropdown"
        tabindex="-1"
        :class="[dropdownDirClass]"
        :style="inputWidthStyle"
        @mouseup="keepFocusing"
        @click.stop=""
      >
        <div class="select-list" :style="inputWidthStyle" tabindex="-1">
          <!-- Common Keyboard Support: less event listeners -->
          <template v-if="!advancedKeyboard">
            <template v-for="column in columnsSequence">
              <ul v-if="column === 'hour'" :key="column" class="hours" @scroll="keepFocusing">
                <li class="hint" v-text="hourLabelText"></li>
                <template v-for="(hr, hIndex) in hours">
                  <li
                    v-if="!opts.hideDisabledHours || (opts.hideDisabledHours && !isDisabled('hour', hr))"
                    :key="hIndex"
                    :class="{ active: hour === hr }"
                    :disabled="isDisabled('hour', hr)"
                    :data-key="hr"
                    v-text="hr"
                    @click="select('hour', hr)"
                  ></li>
                </template>
              </ul>
              <ul v-if="column === 'minute'" :key="column" class="minutes" @scroll="keepFocusing">
                <li class="hint" v-text="minuteLabelText"></li>
                <template v-for="(m, mIndex) in minutes">
                  <li
                    v-if="!opts.hideDisabledMinutes || (opts.hideDisabledMinutes && !isDisabled('minute', m))"
                    :key="mIndex"
                    :class="{ active: minute === m }"
                    :disabled="isDisabled('minute', m)"
                    :data-key="m"
                    v-text="m"
                    @click="select('minute', m)"
                  ></li>
                </template>
              </ul>
              <ul v-if="column === 'second'" :key="column" class="seconds" @scroll="keepFocusing">
                <li class="hint" v-text="secondLabelText"></li>
                <template v-for="(s, sIndex) in seconds">
                  <li
                    v-if="!opts.hideDisabledSeconds || (opts.hideDisabledSeconds && !isDisabled('second', s))"
                    :key="sIndex"
                    :class="{ active: second === s }"
                    :disabled="isDisabled('second', s)"
                    :data-key="s"
                    v-text="s"
                    @click="select('second', s)"
                  ></li>
                </template>
              </ul>
              <ul v-if="column === 'apm'" :key="column" class="apms" @scroll="keepFocusing">
                <li class="hint" v-text="apmLabelText"></li>
                <template v-for="(a, aIndex) in apms">
                  <li
                    v-if="!opts.hideDisabledHours || (opts.hideDisabledHours && !isDisabled('apm', a))"
                    :key="aIndex"
                    :class="{ active: apm === a }"
                    :disabled="isDisabled('apm', a)"
                    :data-key="a"
                    v-text="apmDisplayText(a)"
                    @click="select('apm', a)"
                  ></li>
                </template>
              </ul>
            </template> </template><!-- / Common Keyboard Support -->

          <!--
        Advanced Keyboard Support
        Addeds hundreds of additional event lisenters
      -->
          <template v-if="advancedKeyboard">
            <template v-for="column in columnsSequence">
              <ul v-if="column === 'hour'" :key="column" class="hours" tabindex="-1" @scroll="keepFocusing">
                <li class="hint" v-text="hourLabelText" tabindex="-1"></li>
                <template v-for="(hr, hIndex) in hours">
                  <li
                    v-if="!opts.hideDisabledHours || (opts.hideDisabledHours && !isDisabled('hour', hr))"
                    :key="hIndex"
                    :class="{ active: hour === hr }"
                    :tabindex="isDisabled('hour', hr) ? -1 : tabindex"
                    :data-key="hr"
                    :disabled="isDisabled('hour', hr)"
                    v-text="hr"
                    @click="select('hour', hr)"
                    @keydown.tab="onTab('hour', hr, $event)"
                    @keydown.space.prevent="select('hour', hr)"
                    @keydown.enter.prevent="select('hour', hr)"
                    @keydown.up.prevent="prevItem('hour', hr)"
                    @keydown.down.prevent="nextItem('hour', hr)"
                    @keydown.left.prevent="toLeftColumn('hour')"
                    @keydown.right.prevent="toRightColumn('hour')"
                    @keydown.esc.exact="debounceBlur"
                    @blur="debounceBlur"
                    @focus="keepFocusing"
                  ></li>
                </template>
              </ul>
              <ul v-if="column === 'minute'" :key="column" class="minutes" tabindex="-1" @scroll="keepFocusing">
                <li class="hint" v-text="minuteLabelText" tabindex="-1"></li>
                <template v-for="(m, mIndex) in minutes">
                  <li
                    v-if="!opts.hideDisabledMinutes || (opts.hideDisabledMinutes && !isDisabled('minute', m))"
                    :key="mIndex"
                    :class="{ active: minute === m }"
                    :tabindex="isDisabled('minute', m) ? -1 : tabindex"
                    :data-key="m"
                    :disabled="isDisabled('minute', m)"
                    v-text="m"
                    @click="select('minute', m)"
                    @keydown.tab="onTab('minute', m, $event)"
                    @keydown.space.prevent="select('minute', m)"
                    @keydown.enter.prevent="select('minute', m)"
                    @keydown.up.prevent="prevItem('minute', m)"
                    @keydown.down.prevent="nextItem('minute', m)"
                    @keydown.left.prevent="toLeftColumn('minute')"
                    @keydown.right.prevent="toRightColumn('minute')"
                    @keydown.esc.exact="debounceBlur"
                    @blur="debounceBlur"
                    @focus="keepFocusing"
                  ></li>
                </template>
              </ul>
              <ul v-if="column === 'second'" :key="column" class="seconds" tabindex="-1" @scroll="keepFocusing">
                <li class="hint" v-text="secondLabelText" tabindex="-1"></li>
                <template v-for="(s, sIndex) in seconds">
                  <li
                    v-if="!opts.hideDisabledSeconds || (opts.hideDisabledSeconds && !isDisabled('second', s))"
                    :key="sIndex"
                    :class="{ active: second === s }"
                    :tabindex="isDisabled('second', s) ? -1 : tabindex"
                    :data-key="s"
                    :disabled="isDisabled('second', s)"
                    v-text="s"
                    @click="select('second', s)"
                    @keydown.tab="onTab('second', s, $event)"
                    @keydown.space.prevent="select('second', s)"
                    @keydown.enter.prevent="select('second', s)"
                    @keydown.up.prevent="prevItem('second', s)"
                    @keydown.down.prevent="nextItem('second', s)"
                    @keydown.left.prevent="toLeftColumn('second')"
                    @keydown.right.prevent="toRightColumn('second')"
                    @keydown.esc.exact="debounceBlur"
                    @blur="debounceBlur"
                    @focus="keepFocusing"
                  ></li>
                </template>
              </ul>
              <ul v-if="column === 'apm'" :key="column" class="apms" tabindex="-1" @scroll="keepFocusing">
                <li class="hint" v-text="apmLabelText" tabindex="-1"></li>
                <template v-for="(a, aIndex) in apms">
                  <li
                    v-if="!opts.hideDisabledHours || (opts.hideDisabledHours && !isDisabled('apm', a))"
                    :key="aIndex"
                    :class="{ active: apm === a }"
                    :tabindex="isDisabled('apm', a) ? -1 : tabindex"
                    :data-key="a"
                    :disabled="isDisabled('apm', a)"
                    v-text="apmDisplayText(a)"
                    @click="select('apm', a)"
                    @keydown.tab="onTab('apm', a, $event)"
                    @keydown.space.prevent="select('apm', a)"
                    @keydown.enter.prevent="select('apm', a)"
                    @keydown.up.prevent="prevItem('apm', a)"
                    @keydown.down.prevent="nextItem('apm', a)"
                    @keydown.left.prevent="toLeftColumn('apm')"
                    @keydown.right.prevent="toRightColumn('apm')"
                    @keydown.esc.exact="debounceBlur"
                    @blur="debounceBlur"
                    @focus="keepFocusing"
                  ></li>
                </template>
              </ul>
            </template> </template><!-- / Advanced Keyboard Support -->
        </div>
      </div>
    </div>
    <InputError :message="error?.replace(' id', '')" class="mt-2" />
  </div>
</template>
