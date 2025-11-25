import Image from "next/image";
import Link from "next/link";
import { Button } from "@/components/ui/button";
import { PlaceHolderImages } from "@/lib/placeholder-images";
import { ArrowRight } from "lucide-react";

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
  ctaSecondaryLabel,
  ctaSecondaryHref,
  imageUrl,
}: HeroProps) {
  const heroImage = PlaceHolderImages.find((img) => img.id === "hero-background");
  const bgImage = imageUrl || heroImage?.imageUrl;
  const bgAlt = heroImage?.description || "Dispatch background";

  return (
    <section className="relative h-[80vh] min-h-[500px] w-full flex items-center justify-center text-center">
      {bgImage && (
        <Image
          src={bgImage}
          alt={bgAlt}
          fill
          className="object-cover"
          priority
          sizes="100vw"
        />
      )}
      <div className="absolute inset-0 bg-gradient-to-t from-background via-background/50 to-transparent" />
      <div className="absolute inset-0 bg-black/30" />

      <div className="relative z-10 flex flex-col items-center gap-6 px-4">
        <div className="flex flex-col items-center gap-3">
          {badge && (
            <span className="rounded-full bg-primary/20 px-4 py-1 text-sm font-semibold text-primary shadow-sm">
              {badge}
            </span>
          )}
          <h1 className="text-4xl md:text-6xl font-bold tracking-tighter text-white drop-shadow-md">
            {title}
          </h1>
          <p className="max-w-2xl text-lg text-gray-200 drop-shadow">{subtitle}</p>
        </div>
        <div className="flex flex-col sm:flex-row gap-3">
          {ctaPrimaryLabel && (
            <Button asChild size="lg" className="font-semibold">
              <Link href={ctaPrimaryHref}>
                {ctaPrimaryLabel} <ArrowRight className="ml-2 h-5 w-5" />
              </Link>
            </Button>
          )}
          {ctaSecondaryLabel && ctaSecondaryHref && (
            <Button asChild size="lg" variant="secondary" className="font-semibold">
              <Link href={ctaSecondaryHref}>{ctaSecondaryLabel}</Link>
            </Button>
          )}
        </div>
      </div>
    </section>
  );
}
