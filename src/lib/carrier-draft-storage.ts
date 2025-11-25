const DRAFT_TOKEN_KEY = "carrier-onboarding.draftId";

const isBrowser = typeof window !== "undefined";

export function getDraftToken() {
  if (!isBrowser) return null;
  return window.localStorage.getItem(DRAFT_TOKEN_KEY);
}

export function storeDraftToken(token: string) {
  if (!isBrowser) return;
  window.localStorage.setItem(DRAFT_TOKEN_KEY, token);
}

export function clearDraftToken() {
  if (!isBrowser) return;
  window.localStorage.removeItem(DRAFT_TOKEN_KEY);
}
