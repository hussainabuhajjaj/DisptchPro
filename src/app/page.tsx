import dynamic from "next/dynamic";
import { fetchLandingContent } from "@/lib/landing-content";
import { fetchSiteSettings } from "@/lib/site-settings";
import { LandingSection } from "@/lib/landing-content";

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

export default async function Home() {
  // Load landing content to align metadata/theming and future rendering
  const [landing] = await Promise.all([fetchLandingContent(), fetchSiteSettings()]);

  const sectionsBySlug: Record<string, LandingSection> = {};
  landing.sections.forEach((s) => {
    sectionsBySlug[s.slug] = s;
  });

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
      />
      <Services
        title={(sectionsBySlug["features"]?.title as string) || undefined}
        subtitle={(sectionsBySlug["features"]?.subtitle as string) || undefined}
        items={((featuresContent as any)?.items as { title: string; description: string }[]) || undefined}
      />
      <WhyChooseUs
        title={(sectionsBySlug["why-us"]?.title as string) || undefined}
        subtitle={(sectionsBySlug["why-us"]?.subtitle as string) || undefined}
        bullets={normalizeBullets((whyUsContent as any)?.items)}
      />
      <ForShippers
        title={(sectionsBySlug["for-shippers"]?.title as string) || undefined}
        subtitle={(sectionsBySlug["for-shippers"]?.subtitle as string) || undefined}
        bullets={normalizeBullets((forShippersContent as any)?.bullets)}
      />
      <ForBrokers
        title={(sectionsBySlug["for-brokers"]?.title as string) || undefined}
        subtitle={(sectionsBySlug["for-brokers"]?.subtitle as string) || undefined}
        bullets={normalizeBullets((forBrokersContent as any)?.bullets)}
      />
      <ProfitCalculator />
      <Roadmap />
      <Testimonials
        title={(sectionsBySlug["testimonials"]?.title as string) || undefined}
        subtitle={(sectionsBySlug["testimonials"]?.subtitle as string) || undefined}
        quotes={((testimonialsContent as any)?.quotes as any[]) || undefined}
      />
      <KpiSection
        title={(sectionsBySlug["kpis"]?.title as string) || undefined}
        subtitle={(sectionsBySlug["kpis"]?.subtitle as string) || undefined}
        metrics={((kpiContent as any)?.metrics as any[]) || undefined}
      />
      <LoadBoardPreview
        title={(sectionsBySlug["load-board"]?.title as string) || undefined}
        subtitle={(sectionsBySlug["load-board"]?.subtitle as string) || undefined}
        loads={((loadBoardContent as any)?.loads as any[]) || undefined}
      />
      <FaqSection
        title={(sectionsBySlug["faq"]?.title as string) || undefined}
        subtitle={(sectionsBySlug["faq"]?.subtitle as string) || undefined}
        faqs={((faqContent as any)?.faqs as any[]) || undefined}
      />
      <ResourcesSection
        title={(sectionsBySlug["resources"]?.title as string) || undefined}
        subtitle={(sectionsBySlug["resources"]?.subtitle as string) || undefined}
        resources={((resourcesContent as any)?.resources as any[]) || undefined}
      />
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
