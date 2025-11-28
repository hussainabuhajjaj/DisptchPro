const isProd = process.env.NODE_ENV === "production";
const defaultBase = isProd ? "" : "http://127.0.0.1:8000/api";

export const apiConfig = {
  baseUrl: process.env.NEXT_PUBLIC_API_BASE_URL || defaultBase,
};

export function assertApiConfig() {
  if (!process.env.NEXT_PUBLIC_API_BASE_URL && isProd) {
    throw new Error(
      "NEXT_PUBLIC_API_BASE_URL is not set. Configure it for production so the frontend hits the real API.",
    );
  }

  if (!process.env.NEXT_PUBLIC_API_BASE_URL && !isProd) {
    console.warn(
      `NEXT_PUBLIC_API_BASE_URL is not defined. Falling back to ${defaultBase}. Set NEXT_PUBLIC_API_BASE_URL in .env.local for real API calls.`,
    );
  }
}
