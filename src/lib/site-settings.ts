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
  try {
    const data = await apiClient.get<LandingResponse>("landing-page");
    return data.settings ?? {};
  } catch (error) {
    console.error("Failed to load site settings", error);
    return {};
  }
}
