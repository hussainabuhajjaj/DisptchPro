"use client";

import Link from "next/link";
import { Button } from "@/components/ui/button";

type CtaSectionProps = {
  title?: string;
  subtitle?: string;
  ctaPrimaryLabel?: string;
  ctaPrimaryHref?: string;
  ctaSecondaryLabel?: string;
  ctaSecondaryHref?: string;
};

export default function CtaSection({
  title = "Book a call and start moving better loads",
  subtitle = "Our team will map your preferred lanes and get you rolling within 24 hours.",
  ctaPrimaryLabel = "Book a call",
  ctaPrimaryHref = "#book",
  ctaSecondaryLabel,
  ctaSecondaryHref,
}: CtaSectionProps) {
  return (
    <section className="w-full py-16 md:py-24">
      <div className="container mx-auto px-4 md:px-6">
        <div className="rounded-2xl border bg-card p-10 shadow-lg text-center space-y-4">
          <h2 className="text-3xl md:text-4xl font-bold tracking-tighter">{title}</h2>
          <p className="text-lg text-muted-foreground">{subtitle}</p>
          <div className="flex flex-col sm:flex-row gap-3 justify-center">
            {ctaPrimaryLabel && (
              <Button asChild size="lg" data-umami-event="cta-primary">
                <Link href={ctaPrimaryHref}>{ctaPrimaryLabel}</Link>
              </Button>
            )}
            {ctaSecondaryLabel && ctaSecondaryHref && (
              <Button asChild size="lg" variant="secondary" data-umami-event="cta-secondary">
                <Link href={ctaSecondaryHref}>{ctaSecondaryLabel}</Link>
              </Button>
            )}
          </div>
        </div>
      </div>
    </section>
  );
}
