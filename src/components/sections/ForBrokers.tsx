import Image from "next/image";
import { PlaceHolderImages } from "@/lib/placeholder-images";
import { CheckCircle } from "lucide-react";
import { Button } from "../ui/button";
import Link from "next/link";
import { ArrowRight } from "lucide-react";

type ForBrokersProps = {
  title?: string;
  subtitle?: string;
  bullets?: string[];
  ctaLabel?: string;
  ctaHref?: string;
};

const defaultBullets = [
  "Access to a vetted network of reliable carriers.",
  "Real-time visibility and communication.",
  "Reduced overhead in carrier management.",
  "Dedicated support for seamless coordination.",
  "Increase your capacity and service reliability.",
];

export default function ForBrokers({
  title = "Reliable Capacity, Seamless Partnership.",
  subtitle = "Partner with us to access a trusted network of carriers. We ensure your loads are covered by professionals, giving you peace of mind and happy clients.",
  bullets = defaultBullets,
  ctaLabel = "Partner With Us",
  ctaHref = "#book",
}: ForBrokersProps) {
  const forBrokersImage = PlaceHolderImages.find((img) => img.id === "for-brokers");
  return (
    <section id="for-brokers" className="w-full py-16 md:py-24">
      <div className="container mx-auto px-4 md:px-6">
        <div className="grid md:grid-cols-2 gap-12 items-center">
           <div className="relative h-80 w-full rounded-lg overflow-hidden shadow-lg order-last md:order-first">
             {forBrokersImage && (
                <Image
                    src={forBrokersImage.imageUrl}
                    alt={forBrokersImage.description}
                    fill
                    className="object-cover"
                    data-ai-hint={forBrokersImage.imageHint}
                    sizes="(max-width: 768px) 100vw, 50vw"
                />
            )}
            <div className="absolute inset-0 bg-black/20" />
          </div>
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
        </div>
      </div>
    </section>
  );
}
