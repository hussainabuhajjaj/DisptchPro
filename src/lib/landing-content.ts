import { apiClient } from "./httpClient";

export type LandingSection = {
  slug: string;
  title: string;
  subtitle?: string | null;
  content: Record<string, unknown>;
  position: number;
  is_active: boolean;
};

export type LandingResponse = {
  sections: LandingSection[];
  settings: Record<string, unknown>;
};

export async function fetchLandingContent(): Promise<LandingResponse> {
  return apiClient.get<LandingResponse>("landing-page");
}
