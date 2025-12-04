import dynamic from "next/dynamic";
import { fetchLandingContent } from "@/lib/landing-content";
import { fetchSiteSettings } from "@/lib/site-settings";
import { LandingSection } from "@/lib/landing-content";
import LeadMagnet from "@/components/sections/LeadMagnet";

const Hero = dynamic(() => import("@/components/sections/Hero"), { ssr: true });
const Services = dynamic(() => import("@/components/sections/Services"), { ssr: true });
const WhyChooseUs = dynamic(() => import("@/components/sections/WhyChooseUs"), { ssr: true });
const ForShippers = dynamic(() => import("@/components/sections/ForShippers"), { ssr: true });
const ForBrokers = dynamic(() => import("@/components/sections/ForBrokers"), { ssr: true });
const ProfitCalculator = dynamic(() => import("@/components/sections/ProfitCalculator"), { ssr: true });
const Roadmap = dynamic(() => import("@/components/sections/Roadmap"), { ssr: true });
const Testimonials = dynamic(() => import("@/components/sections/Testimonials"), { ssr: true });
const Booking = dynamic(() => import("@/components/sections/Booking"), { ssr: true });
const FaqSection = dynamic(() => import("@/components/sections/FaqSection"), { ssr: true });
const KpiSection = dynamic(() => import("@/components/sections/KpiSection"), { ssr: true });
const LoadBoardPreview = dynamic(() => import("@/components/sections/LoadBoardPreview"), { ssr: true });
const CtaSection = dynamic(() => import("@/components/sections/CtaSection"), { ssr: true });
const ResourcesSection = dynamic(() => import("@/components/sections/ResourcesSection"), { ssr: true });
const PricingSection = dynamic(() => import("@/components/sections/PricingSection"), { ssr: true });

export default async function Home() {
  // Load landing content to align metadata/theming and future rendering
  const [landing] = await Promise.all([fetchLandingContent(), fetchSiteSettings()]);

  const landingData = landing ?? { sections: [], settings: {}, media: {} as any };

  if (process.env.NODE_ENV === "development") {
    // Debug: inspect API payload on the server side
    console.log("[landing] sections count:", (landingData.sections ?? []).length);
    console.log("[landing] sections:", (landingData.sections ?? []).map((s) => [s.content,s.slug,s.title,s.position,s.is_active]));
    console.log("[landing] media:", landingData.media);
    console.log("[landing] settings keys:", Object.keys((landingData as any)?.settings ?? {}));
  }

  const sectionsBySlug: Record<string, LandingSection> = {};
  (landingData.sections ?? []).forEach((s) => {
    sectionsBySlug[s.slug] = s;
  });

  const settings = (landingData as any)?.settings || {};
  const media = (landingData as any)?.media || {};
  const mediaImages = {
    hero: media.hero_image_url as string | undefined,
    whyChooseUs: media.why_choose_us_image_url as string | undefined,
    forShippers: media.for_shippers_image_url as string | undefined,
    forBrokers: media.for_brokers_image_url as string | undefined,
    testimonialAvatar1: media.testimonial_avatar_1_url as string | undefined,
    testimonialAvatar2: media.testimonial_avatar_2_url as string | undefined,
    testimonialAvatar3: media.testimonial_avatar_3_url as string | undefined,
  };

  const heroContent = sectionsBySlug["hero"]?.content || {};
  const featuresContent = sectionsBySlug["features"]?.content || {};
  const testimonialsContent = sectionsBySlug["testimonials"]?.content || {};
  const kpiContent = sectionsBySlug["kpis"]?.content || {};
  const loadBoardContent = sectionsBySlug["load-board"]?.content || {};
  const ctaContent = sectionsBySlug["cta"]?.content || {};
  const whyUsContent = sectionsBySlug["why-us"]?.content || {};
  const forShippersContent = sectionsBySlug["for-shippers"]?.content || {};
  const forBrokersContent = sectionsBySlug["for-brokers"]?.content || {};
  const faqContent = sectionsBySlug["faq"]?.content || {};
  const resourcesContent = sectionsBySlug["resources"]?.content || {};

  const normalizeBullets = (val: any): string[] | undefined => {
    if (!Array.isArray(val)) return undefined;
    const mapped = val
      .map((item) => {
        if (typeof item === "string") return item;
        if (item?.title) return item.title as string;
        if (item?.text) return item.text as string;
        return null;
      })
      .filter(Boolean) as string[];
    return mapped.length ? mapped : undefined;
  };

  const servicesItems =
    Array.isArray((featuresContent as any)?.items) && (featuresContent as any).items.length
      ? (featuresContent as any).items.map((item: any) => ({
          title: item?.title ?? "",
          description: item?.description ?? "",
        }))
      : undefined;

  const whyUsBullets = normalizeBullets((whyUsContent as any)?.items ?? (whyUsContent as any)?.bullets);

  const forShippersBullets = normalizeBullets((forShippersContent as any)?.bullets);
  const forBrokersBullets = normalizeBullets((forBrokersContent as any)?.bullets);

  const kpiMetrics =
    Array.isArray((kpiContent as any)?.metrics) && (kpiContent as any).metrics.length
      ? (kpiContent as any).metrics.map((m: any) => ({
          label: m?.label ?? "",
          value: m?.value ?? "",
        }))
      : undefined;

  const loadBoardLoads =
    Array.isArray((loadBoardContent as any)?.loads) && (loadBoardContent as any).loads.length
      ? (loadBoardContent as any).loads.map((l: any) => ({
          origin: l?.origin ?? "",
          destination: l?.destination ?? "",
          equipment: l?.equipment ?? "",
          rate: l?.rate ?? l?.rpm ?? "",
          pickup: l?.pickup ?? "",
        }))
      : undefined;

  const testimonialsQuotes =
    Array.isArray((testimonialsContent as any)?.quotes) && (testimonialsContent as any).quotes.length
      ? (testimonialsContent as any).quotes.map((q: any) => ({
          name: q?.name ?? "",
          text: q?.text ?? "",
          role: q?.role ?? "",
        }))
      : undefined;

  const resourcesList =
    Array.isArray((resourcesContent as any)?.resources) && (resourcesContent as any).resources.length
      ? (resourcesContent as any).resources.map((r: any) => ({
          title: r?.title ?? "",
          description: r?.description ?? "",
          href: r?.href ?? "#",
        }))
      : undefined;

  return (
    <div className="flex flex-col min-h-screen">
      <Hero
        title={(heroContent as any)?.headline as string}
        subtitle={(heroContent as any)?.subtitle as string}
        badge={(heroContent as any)?.badge as string}
        ctaPrimaryLabel={(heroContent as any)?.cta_primary as string}
        ctaPrimaryHref="#book"
        ctaSecondaryLabel={(heroContent as any)?.cta_secondary as string}
        ctaSecondaryHref="#services"
        imageUrl={mediaImages.hero}
      />
      <Services
        title={(sectionsBySlug["features"]?.title as string) || undefined}
        subtitle={(sectionsBySlug["features"]?.subtitle as string) || undefined}
        items={servicesItems}
      />
      <WhyChooseUs
        title={(sectionsBySlug["why-us"]?.title as string) || undefined}
        subtitle={(sectionsBySlug["why-us"]?.subtitle as string) || undefined}
        bullets={whyUsBullets}
        imageUrl={mediaImages.whyChooseUs}
      />
      <ForShippers
        title={(sectionsBySlug["for-shippers"]?.title as string) || undefined}
        subtitle={(sectionsBySlug["for-shippers"]?.subtitle as string) || undefined}
        bullets={forShippersBullets}
        imageUrl={mediaImages.forShippers}
      />
      <ForBrokers
        title={(sectionsBySlug["for-brokers"]?.title as string) || undefined}
        subtitle={(sectionsBySlug["for-brokers"]?.subtitle as string) || undefined}
        bullets={forBrokersBullets}
        imageUrl={mediaImages.forBrokers}
      />
      <PricingSection />
      <ProfitCalculator />
      <Roadmap />
      <Testimonials
        title={(sectionsBySlug["testimonials"]?.title as string) || undefined}
        subtitle={(sectionsBySlug["testimonials"]?.subtitle as string) || undefined}
        quotes={testimonialsQuotes}
        imageOverrides={{
          "testimonial-avatar-1": mediaImages.testimonialAvatar1,
          "testimonial-avatar-2": mediaImages.testimonialAvatar2,
          "testimonial-avatar-3": mediaImages.testimonialAvatar3,
        }}
      />
      <KpiSection
        title={(sectionsBySlug["kpis"]?.title as string) || undefined}
        subtitle={(sectionsBySlug["kpis"]?.subtitle as string) || undefined}
        metrics={kpiMetrics}
      />
      <LoadBoardPreview
        title={(sectionsBySlug["load-board"]?.title as string) || undefined}
        subtitle={(sectionsBySlug["load-board"]?.subtitle as string) || undefined}
        loads={loadBoardLoads}
      />
      <FaqSection
        title={(sectionsBySlug["faq"]?.title as string) || undefined}
        subtitle={(sectionsBySlug["faq"]?.subtitle as string) || undefined}
        faqs={((faqContent as any)?.faqs as any[]) || undefined}
      />
      <ResourcesSection
        title={(sectionsBySlug["resources"]?.title as string) || undefined}
        subtitle={(sectionsBySlug["resources"]?.subtitle as string) || undefined}
        resources={resourcesList}
      />
      <LeadMagnet />
      <CtaSection
        title={(sectionsBySlug["cta"]?.title as string) || undefined}
        subtitle={(sectionsBySlug["cta"]?.subtitle as string) || undefined}
        ctaPrimaryLabel={(ctaContent as any)?.cta_primary as string}
        ctaPrimaryHref={(ctaContent as any)?.cta_primary_href as string || "#book"}
        ctaSecondaryLabel={(ctaContent as any)?.cta_secondary as string}
        ctaSecondaryHref={(ctaContent as any)?.cta_secondary_href as string}
      />
      <Booking />
    </div>
  );
}
