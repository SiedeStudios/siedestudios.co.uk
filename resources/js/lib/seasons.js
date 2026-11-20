/** Which seasonal effect applies on a given (local) date, if any. */
export function seasonFor(date = new Date()) {
    const m = date.getMonth() + 1;
    const d = date.getDate();
    if ((m === 12 && d === 31) || (m === 1 && d === 1)) return 'fireworks'; // New Year
    if (m === 11 && d >= 4 && d <= 6) return 'fireworks'; // Bonfire Night
    if (m === 10 && d >= 24) return 'embers'; // Halloween week
    if (m === 12 || (m === 1 && d <= 6)) return 'snow'; // December to Twelfth Night
    return null;
}
