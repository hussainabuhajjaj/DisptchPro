export function trackEvent(event: string, data?: Record<string, unknown>) {
  if (typeof window === "undefined") return;
  const anyWindow = window as any;
  const umami = anyWindow?.umami;
  if (typeof umami?.track === "function") {
    umami.track(event, data);
  }
}

