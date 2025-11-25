const TOKEN_KEY = "dispatchpro.authToken";

const isBrowser = typeof window !== "undefined";

export function getStoredAuthToken() {
  if (!isBrowser) {
    return null;
  }
  return window.localStorage.getItem(TOKEN_KEY);
}

export function storeAuthToken(token: string) {
  if (!isBrowser) {
    return;
  }
  window.localStorage.setItem(TOKEN_KEY, token);
}

export function clearAuthToken() {
  if (!isBrowser) {
    return;
  }
  window.localStorage.removeItem(TOKEN_KEY);
}
