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
  media: {
    hero_image_url: string | null;
    why_choose_us_image_url: string | null;
    for_shippers_image_url: string | null;
    for_brokers_image_url: string | null;
    testimonial_avatar_1_url: string | null;
    testimonial_avatar_2_url: string | null;
    testimonial_avatar_3_url: string | null;
    meta?: Record<string, unknown>;
  };
};

export async function fetchLandingContent(): Promise<LandingResponse> {
  const hasApiBase =
    typeof process !== "undefined" &&
    !!process.env.NEXT_PUBLIC_API_BASE_URL &&
    process.env.NEXT_PUBLIC_API_BASE_URL.trim().length > 0;

  // Avoid failing when no API base is configured (server or client).
  if (!hasApiBase) {
    if (process.env.NODE_ENV === "development") {
      console.warn("[landing-content] NEXT_PUBLIC_API_BASE_URL missing; returning defaults.");
    }
    return { sections: [], settings: {}, media: { hero_image_url: null, why_choose_us_image_url: null, for_shippers_image_url: null, for_brokers_image_url: null, testimonial_avatar_1_url: null, testimonial_avatar_2_url: null, testimonial_avatar_3_url: null } };
  }

  try {
    if (process.env.NODE_ENV === "development") {
      console.log("[landing-content] fetching landing-page from", process.env.NEXT_PUBLIC_API_BASE_URL);
    }
    const response = await apiClient.get<any>("landing-page");
    const payload = (response as any)?.data ?? response;
    return payload as LandingResponse;
  } catch (error) {
    if (process.env.NODE_ENV === "development") {
      console.error("[landing-content] Failed to load landing content; returning defaults.", error);
    }
    return { sections: [], settings: {}, media: { hero_image_url: null, why_choose_us_image_url: null, for_shippers_image_url: null, for_brokers_image_url: null, testimonial_avatar_1_url: null, testimonial_avatar_2_url: null, testimonial_avatar_3_url: null } };
  }
}
