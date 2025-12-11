const isProd = process.env.NODE_ENV === "production";
// Default API base: in prod point to the API subdomain, in dev fallback to local backend.
const defaultBase = isProd ? "https://api.hadispatch.com/api" : "http://127.0.0.1:8000/api";

export const apiConfig = {
  baseUrl: process.env.NEXT_PUBLIC_API_BASE_URL || defaultBase,
};

export function assertApiConfig() {
  if (!process.env.NEXT_PUBLIC_API_BASE_URL && isProd) {
    // Fall back to defaultBase for production, but warn in logs so it can be explicitly set.
    console.warn(
      `NEXT_PUBLIC_API_BASE_URL is not set. Falling back to ${defaultBase}. Set it in your environment to override.`,
    );
  }

  if (!process.env.NEXT_PUBLIC_API_BASE_URL && !isProd) {
    console.warn(
      `NEXT_PUBLIC_API_BASE_URL is not defined. Falling back to ${defaultBase}. Set NEXT_PUBLIC_API_BASE_URL in .env.local for real API calls.`,
    );
  }
}
