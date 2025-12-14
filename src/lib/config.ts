const isProd = process.env.NODE_ENV === "production";
const devFallbackBase = "http://127.0.0.1:8000/api";

export const apiConfig = {
  baseUrl: process.env.NEXT_PUBLIC_API_BASE_URL?.trim() || (isProd ? "" : devFallbackBase),
};

export function assertApiConfig() {
  if (!apiConfig.baseUrl) {
    const message =
      "NEXT_PUBLIC_API_BASE_URL is required; set it to your API origin (e.g., https://api.example.com/api).";
    if (isProd) {
      throw new Error(message);
    }
    console.warn(message + " Falling back to dev local for now.");
  }
}
