import { apiClient } from "./httpClient";

type LandingResponse = {
  settings: Record<string, unknown>;
};

const FALLBACK_COLORS = {
  "--color-primary": "#f59e0b",
  "--color-secondary": "#0f172a",
  "--color-accent": "#2563eb",
  "--color-text": "#0f172a",
};

export async function loadThemeVars() {
  try {
    const data = await apiClient.get<LandingResponse>("landing-page");
    const settings = data.settings || {};
    const vars: Record<string, string> = { ...FALLBACK_COLORS };

    if (settings.theme_primary_color) vars["--color-primary"] = settings.theme_primary_color as string;
    if (settings.theme_secondary_color) vars["--color-secondary"] = settings.theme_secondary_color as string;
    if (settings.theme_accent_color) vars["--color-accent"] = settings.theme_accent_color as string;
    if (settings.theme_text_color) vars["--color-text"] = settings.theme_text_color as string;

    return vars;
  } catch (error) {
    console.error("Failed to load theme vars", error);
    return FALLBACK_COLORS;
  }
}
