import Image from "next/image";
import Link from "next/link";
import { Button } from "@/components/ui/button";
import { PlaceHolderImages } from "@/lib/placeholder-images";
import { ArrowRight, ShieldCheck, Timer, Truck } from "lucide-react";

type HeroProps = {
  title?: string;
  subtitle?: string;
  badge?: string;
  ctaPrimaryLabel?: string;
  ctaPrimaryHref?: string;
  ctaSecondaryLabel?: string;
  ctaSecondaryHref?: string;
  imageUrl?: string;
};

export default function Hero({
  title = "Keep Your Trucks Moving, Profitably.",
  subtitle = "Expert dispatch services for owner-operators and small fleets. We handle the logistics, so you can focus on the road.",
  badge,
  ctaPrimaryLabel = "Book a Free Consultation",
  ctaPrimaryHref = "#book",
  ctaSecondaryLabel = "Download the Route Optimization Checklist",
  ctaSecondaryHref = "#lead-magnet",
  imageUrl,
}: HeroProps) {
  const heroImage = PlaceHolderImages.find((img) => img.id === "hero-background");
  const bgImage = imageUrl || heroImage?.imageUrl;
  const bgAlt = heroImage?.description || "Dispatch background";
  const trust = [
    { label: "24/7 operations", Icon: Timer },
    { label: "99% on-time delivery", Icon: ShieldCheck },
    { label: "Nationwide coverage", Icon: Truck },
  ];
  const proof = [
    { label: "Avg RPM last 30 days", value: "$2.85" },
    { label: "Owner-ops onboarded", value: "140+" },
    { label: "Avg response time", value: " < 3 min" },
  ];

  return (
    <section className="relative h-[72vh] min-h-[560px] w-full flex items-center">
      {bgImage && (
        <Image
          src={bgImage}
          alt={bgAlt}
          fill
          className="object-cover"
          priority
          sizes="100vw"
          unoptimized
        />
      )}
      <div className="absolute inset-0 bg-gradient-to-r from-[#0b2a45]/85 via-[#0b2a45]/70 to-[#0b2a45]/20" />
      <div className="absolute inset-0 bg-gradient-to-t from-background/10 via-transparent to-transparent" />

      <div className="relative z-10 w-full">
        <div className="container mx-auto px-4 md:px-6">
          <div className="max-w-3xl flex flex-col gap-6 text-left text-white">
            {badge && (
              <span className="inline-flex items-center w-fit rounded-full bg-white/10 px-4 py-2 text-sm font-semibold tracking-wide shadow-sm backdrop-blur">
                {badge}
              </span>
            )}
            <div className="flex flex-col gap-4">
              <h1 className="text-4xl md:text-6xl font-bold leading-tight drop-shadow-md">
                {title}
              </h1>
              <p className="max-w-2xl text-lg text-white/90 drop-shadow">
                {subtitle}
              </p>
            </div>
            <div className="flex flex-col sm:flex-row gap-3">
              {ctaPrimaryLabel && (
                <Button asChild size="lg" className="font-semibold shadow-lg">
                  <Link href={ctaPrimaryHref} data-umami-event="hero-primary-cta">
                    {ctaPrimaryLabel} <ArrowRight className="ml-2 h-5 w-5" />
                  </Link>
                </Button>
              )}
              {ctaSecondaryLabel && ctaSecondaryHref && (
                <Button
                  asChild
                  size="lg"
                  variant="outline"
                  className="font-semibold border-white/70 text-white hover:text-white hover:bg-white/10"
                >
                  <Link href={ctaSecondaryHref} data-umami-event="hero-secondary-cta">
                    {ctaSecondaryLabel}
                  </Link>
                </Button>
              )}
            </div>
            <div className="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
              {trust.map(({ label, Icon }) => (
                <div
                  key={label}
                  className="flex items-center gap-3 rounded-xl bg-white/10 px-4 py-3 text-sm font-semibold shadow-sm backdrop-blur"
                >
                  <Icon className="h-5 w-5 text-primary" />
                  <span>{label}</span>
                </div>
              ))}
            </div>
            <div className="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
              {proof.map((item) => (
                <div
                  key={item.label}
                  className="rounded-xl bg-white/10 px-4 py-3 shadow-sm backdrop-blur border border-white/15 flex items-center justify-between"
                >
                  <span className="text-white/80">{item.label}</span>
                  <span className="font-semibold text-white">{item.value}</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
