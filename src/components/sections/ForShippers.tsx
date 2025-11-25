import Image from "next/image";
import { PlaceHolderImages } from "@/lib/placeholder-images";
import { CheckCircle } from "lucide-react";
import { Button } from "../ui/button";
import Link from "next/link";
import { ArrowRight } from "lucide-react";

type ForShippersProps = {
  title?: string;
  subtitle?: string;
  bullets?: string[];
  ctaLabel?: string;
  ctaHref?: string;
};

const defaultBullets = [
  "Competitive pricing and transparent quotes.",
  "Guaranteed capacity and on-time delivery.",
  "End-to-end visibility with real-time tracking.",
  "Streamlined booking and freight management.",
  "Dedicated support team for your shipments.",
];

export default function ForShippers({
  title = "Your Freight, Delivered On-Time, Every Time.",
  subtitle = "Focus on your business, we'll handle the logistics. We provide reliable, efficient, and cost-effective shipping solutions tailored to your needs.",
  bullets = defaultBullets,
  ctaLabel = "Get a Quote",
  ctaHref = "#book",
}: ForShippersProps) {
  const forShippersImage = PlaceHolderImages.find((img) => img.id === "for-shippers");
  return (
    <section id="for-shippers" className="w-full py-16 md:py-24 bg-secondary/30">
      <div className="container mx-auto px-4 md:px-6">
        <div className="grid md:grid-cols-2 gap-12 items-center">
          <div className="flex flex-col gap-6">
            <h2 className="text-3xl md:text-4xl font-bold tracking-tighter">
              {title}
            </h2>
            <p className="text-lg text-muted-foreground">
              {subtitle}
            </p>
            <ul className="grid gap-3 text-lg">
              {bullets.map((benefit, index) => (
                <li key={index} className="flex items-center gap-3">
                  <CheckCircle className="h-6 w-6 text-primary" />
                  <span>{benefit}</span>
                </li>
              ))}
            </ul>
            <div className="mt-4">
              {ctaLabel && (
                <Button asChild size="lg" className="font-semibold">
                  <Link href={ctaHref}>
                    {ctaLabel} <ArrowRight className="ml-2 h-5 w-5" />
                  </Link>
                </Button>
              )}
            </div>
          </div>
           <div className="relative h-80 w-full rounded-lg overflow-hidden shadow-lg">
             {forShippersImage && (
                <Image
                    src={forShippersImage.imageUrl}
                    alt={forShippersImage.description}
                    fill
                    className="object-cover"
                    data-ai-hint={forShippersImage.imageHint}
                    sizes="(max-width: 768px) 100vw, 50vw"
                />
            )}
            <div className="absolute inset-0 bg-black/20" />
           </div>
        </div>
      </div>
    </section>
  );
}
