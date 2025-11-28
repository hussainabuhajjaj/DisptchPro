import { apiClient } from "./httpClient";

export type SiteSettings = Record<string, unknown>;

type LandingResponse = {
  sections: unknown[];
  settings: SiteSettings;
};

/**
 * Fetch settings from /landing-page. Returns {} on failure.
 */
export async function fetchSiteSettings(): Promise<SiteSettings> {
  const hasApiBase =
    typeof process !== "undefined" &&
    !!process.env.NEXT_PUBLIC_API_BASE_URL &&
    process.env.NEXT_PUBLIC_API_BASE_URL.trim().length > 0;

  // No API base configured: bail out on both server and client to avoid console errors.
  if (!hasApiBase) {
    return {};
  }

  try {
    const data = await apiClient.get<LandingResponse>("landing-page");
    return data.settings ?? {};
  } catch (error) {
    if (process.env.NODE_ENV === "development") {
      console.warn("Failed to load site settings; using defaults.", error);
    }
    return {};
  }
}
