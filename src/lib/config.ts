const defaultBase = "http://127.0.0.1:8000/api";

export const apiConfig = {
  baseUrl: process.env.NEXT_PUBLIC_API_BASE_URL || defaultBase,
};

export function assertApiConfig() {
  if (!process.env.NEXT_PUBLIC_API_BASE_URL) {
    console.warn(
      `NEXT_PUBLIC_API_BASE_URL is not defined. Falling back to ${defaultBase}. Set NEXT_PUBLIC_API_BASE_URL in .env.local for real API calls.`,
    );
  }
}
