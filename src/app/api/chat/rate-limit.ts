const WINDOW_MS = Number(process.env.CHAT_RATE_LIMIT_WINDOW_MS ?? 60_000);
const MAX_HITS = Number(process.env.CHAT_RATE_LIMIT_MAX ?? 60);

const buckets = new Map<string, { count: number; expires: number }>();

export function rateLimit(key: string): { allowed: boolean; retryAfter?: number } {
  const now = Date.now();
  const entry = buckets.get(key);
  if (!entry || entry.expires < now) {
    buckets.set(key, { count: 1, expires: now + WINDOW_MS });
    return { allowed: true };
  }
  if (entry.count >= MAX_HITS) {
    return { allowed: false, retryAfter: entry.expires - now };
  }
  entry.count += 1;
  return { allowed: true };
}
