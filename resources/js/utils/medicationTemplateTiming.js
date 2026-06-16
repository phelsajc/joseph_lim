/**
 * Prescription-diagnosis template timing + dose per slot.
 * Stored in API `frequency` (and favorites `default_frequency`) as JSON:
 *   [{"k":"before_breakfast","q":1},{"k":"after_dinner","q":1.5}]
 * Legacy: comma-separated keys or pipe-separated labels (qty implied 1).
 */

export const MEDICATION_TIMING_OPTIONS = [
  { value: 'before_breakfast', label: 'Before Breakfast' },
  { value: 'after_breakfast', label: 'After Breakfast' },
  { value: 'before_lunch', label: 'Before Lunch' },
  { value: 'after_lunch', label: 'After Lunch' },
  { value: 'before_dinner', label: 'Before Dinner' },
  { value: 'after_dinner', label: 'After Dinner' },
  { value: 'before_bedtime', label: 'Before Bedtime' },
];

/** Maps template timing keys to appointment prescription API fields (bf_b … bt). */
export const TIMING_KEY_TO_APPOINTMENT_FIELD = {
  before_breakfast: 'bf_b',
  after_breakfast: 'bf_a',
  before_lunch: 'l_b',
  after_lunch: 'l_a',
  before_dinner: 's_b',
  after_dinner: 's_a',
  before_bedtime: 'bt',
};

const ORDER = MEDICATION_TIMING_OPTIONS.map((o) => o.value);
const VALID = new Set(ORDER);
const LABEL_TO_VALUE = new Map(
  MEDICATION_TIMING_OPTIONS.map((o) => [o.label.toLowerCase(), o.value])
);

function sortKeys(keys) {
  const set = new Set((keys || []).filter((k) => VALID.has(k)));
  return ORDER.filter((k) => set.has(k));
}

function keyToLabel(key) {
  const o = MEDICATION_TIMING_OPTIONS.find((x) => x.value === key);
  return o ? o.label : key;
}

function mapLabelToKey(label) {
  if (label == null || label === '') {
    return null;
  }
  return LABEL_TO_VALUE.get(String(label).trim().toLowerCase()) || null;
}

/**
 * Positive finite dose for a timing slot (allows decimals e.g. 0.5, 1.25).
 * @returns {number} normalized, or NaN if invalid
 */
export function normalizeScheduleQty(q) {
  if (q === null || q === undefined || q === '') {
    return NaN;
  }
  const n = Number(q);
  if (!Number.isFinite(n) || n <= 0) {
    return NaN;
  }
  const capped = Math.min(999, n);
  return Math.round(capped * 1000) / 1000;
}

/** String for API / meal inputs (trims float noise). */
export function formatMealQtyString(q) {
  const n = normalizeScheduleQty(q);
  if (!Number.isFinite(n)) {
    return '';
  }
  if (Number.isInteger(n)) {
    return String(n);
  }
  return String(n);
}

export function emptyAppointmentMealTimingStrings() {
  return {
    bf_b: '',
    bf_a: '',
    l_b: '',
    l_a: '',
    s_b: '',
    s_a: '',
    bt: '',
  };
}

/**
 * Build bf_b … bt strings from template/favorite `frequency` / `default_frequency`.
 */
export function appointmentMealTimingStringsFromFrequency(raw) {
  const out = emptyAppointmentMealTimingStrings();
  if (raw == null || String(raw).trim() === '') {
    return out;
  }
  const s = String(raw).trim();
  if (s.startsWith('[')) {
    try {
      const arr = JSON.parse(s);
      if (Array.isArray(arr)) {
        for (const entry of arr) {
          if (!entry || typeof entry !== 'object') {
            continue;
          }
          const k = entry.k || entry.key || mapLabelToKey(entry.label);
          const q = normalizeScheduleQty(entry.q ?? entry.qty ?? 1);
          if (!k || !VALID.has(k) || !Number.isFinite(q)) {
            continue;
          }
          const field = TIMING_KEY_TO_APPOINTMENT_FIELD[k];
          if (field) {
            out[field] = formatMealQtyString(q);
          }
        }
        return out;
      }
    } catch (e) {
      /* fall through */
    }
  }
  const keys = parseTimingFromStorage(raw);
  for (const k of keys) {
    const field = TIMING_KEY_TO_APPOINTMENT_FIELD[k];
    if (field) {
      out[field] = '1';
    }
  }
  return out;
}

/**
 * @returns {Array<{ key: string, label: string, enabled: boolean, qty: number }>}
 */
export function createEmptyTimingSlots() {
  return MEDICATION_TIMING_OPTIONS.map((o) => ({
    key: o.value,
    label: o.label,
    enabled: false,
    qty: 1,
  }));
}

/**
 * Parse legacy frequency string into ordered timing keys (qty not represented).
 */
export function parseTimingFromStorage(raw) {
  if (raw == null || raw === '') {
    return [];
  }
  const s = String(raw).trim();
  if (!s) {
    return [];
  }
  if (s.startsWith('[')) {
    try {
      const arr = JSON.parse(s);
      if (Array.isArray(arr)) {
        const keys = [];
        for (const entry of arr) {
          if (entry && typeof entry === 'object') {
            const k = entry.k || entry.key || mapLabelToKey(entry.label);
            if (k && VALID.has(k)) {
              keys.push(k);
            }
          }
        }
        return sortKeys(keys);
      }
    } catch (e) {
      /* fall through */
    }
  }
  const parts = s.split(/[,|]/).map((x) => x.trim()).filter(Boolean);
  const asKeys = sortKeys(parts);
  if (asKeys.length) {
    return asKeys;
  }
  const fromPipe = s.split(/\s*\|\s*/).map((x) => x.trim()).filter(Boolean);
  const fromLabels = [];
  for (const p of fromPipe) {
    const v = LABEL_TO_VALUE.get(p.toLowerCase());
    if (v) {
      fromLabels.push(v);
    }
  }
  return sortKeys(fromLabels);
}

/**
 * Merge stored value into a fresh slot list (for form rows).
 */
export function parseTimingScheduleFromStorage(raw) {
  const slots = createEmptyTimingSlots();
  if (raw == null || String(raw).trim() === '') {
    return slots;
  }
  const s = String(raw).trim();
  if (s.startsWith('[')) {
    try {
      const arr = JSON.parse(s);
      if (Array.isArray(arr)) {
        for (const entry of arr) {
          if (!entry || typeof entry !== 'object') {
            continue;
          }
          const k = entry.k || entry.key || mapLabelToKey(entry.label);
          const qRaw = entry.q ?? entry.qty;
          if (!k || !VALID.has(k)) {
            continue;
          }
          const slot = slots.find((x) => x.key === k);
          if (!slot) {
            continue;
          }
          let q = normalizeScheduleQty(qRaw != null && qRaw !== '' ? qRaw : 1);
          if (!Number.isFinite(q)) {
            q = 1;
          }
          slot.enabled = true;
          slot.qty = q;
        }
        return slots;
      }
    } catch (e) {
      /* legacy string */
    }
  }
  const keys = parseTimingFromStorage(raw);
  keys.forEach((k) => {
    const slot = slots.find((x) => x.key === k);
    if (slot) {
      slot.enabled = true;
      slot.qty = 1;
    }
  });
  return slots;
}

/**
 * @param {Array<{ key: string, enabled: boolean, qty: number }>} slots
 * @returns {string} JSON for `frequency` / `default_frequency`
 */
export function serializeTimingScheduleSlots(slots) {
  const active = (slots || [])
    .filter((s) => s && s.enabled && Number.isFinite(normalizeScheduleQty(s.qty)))
    .map((s) => ({ k: s.key, q: normalizeScheduleQty(s.qty) }));
  return active.length ? JSON.stringify(active) : '';
}

/**
 * Build template timing slots from appointment prescription meal fields (bf_b … bt).
 * @param {Record<string, string|number>} meal
 */
export function timingScheduleSlotsFromAppointmentMealStrings(meal) {
  const slots = createEmptyTimingSlots();
  if (!meal) {
    return slots;
  }
  for (const slot of slots) {
    const field = TIMING_KEY_TO_APPOINTMENT_FIELD[slot.key];
    const q = normalizeScheduleQty(meal[field]);
    if (Number.isFinite(q)) {
      slot.enabled = true;
      slot.qty = q;
    }
  }
  return slots;
}

/** Serialize bf_b … bt into template `frequency` JSON. */
export function serializeTimingFromAppointmentMealStrings(meal) {
  return serializeTimingScheduleSlots(timingScheduleSlotsFromAppointmentMealStrings(meal));
}

/** Preferred shape for APIs / previews: [{ label, qty }, ...] */
export function timingScheduleToLabelQtyArray(slots) {
  return (slots || [])
    .filter((s) => s && s.enabled && Number.isFinite(normalizeScheduleQty(s.qty)))
    .map((s) => ({
      label: s.label || keyToLabel(s.key),
      qty: normalizeScheduleQty(s.qty),
    }));
}

export function serializeTimingKeys(keys) {
  const sorted = sortKeys(keys);
  return sorted.length ? sorted.join(',') : '';
}

export function timingLabelsForKeys(keys) {
  const sorted = sortKeys(keys);
  return sorted
    .map((k) => keyToLabel(k))
    .join(', ');
}

export function formatTimingLabelsFromStorage(raw) {
  return timingLabelsForKeys(parseTimingFromStorage(raw));
}

/**
 * Human-readable line (favorites column / legacy); not used for appointment meal fields.
 */
export function displayStoredTimingOrLegacy(raw) {
  if (raw == null || String(raw).trim() === '') {
    return '';
  }
  const s = String(raw).trim();
  if (s.startsWith('[')) {
    try {
      const arr = JSON.parse(s);
      if (Array.isArray(arr) && arr.length) {
        const parts = [];
        for (const entry of arr) {
          if (!entry || typeof entry !== 'object') {
            continue;
          }
          const k = entry.k || entry.key || mapLabelToKey(entry.label);
          const qNum = normalizeScheduleQty(entry.q ?? entry.qty ?? 1);
          const qDisp = Number.isFinite(qNum) ? formatMealQtyString(qNum) : '1';
          if (k && VALID.has(k)) {
            parts.push(`${keyToLabel(k)} × ${qDisp}`);
          } else if (entry.label && Number(entry.qty) > 0) {
            const q2 = normalizeScheduleQty(entry.qty);
            parts.push(`${entry.label} × ${Number.isFinite(q2) ? formatMealQtyString(q2) : '1'}`);
          }
        }
        if (parts.length) {
          return parts.join(', ');
        }
      }
    } catch (e) {
      /* fall through */
    }
  }
  const keys = parseTimingFromStorage(raw);
  if (keys.length) {
    return keys.map((k) => `${keyToLabel(k)} × 1`).join(', ');
  }
  if (s) {
    return s;
  }
  return '';
}
