import Image from "next/image";
import Link from "next/link";
import { Button } from "@/components/ui/button";
import { PlaceHolderImages } from "@/lib/placeholder-images";
import { ArrowRight } from "lucide-react";

export default function Hero() {
  const heroImage = PlaceHolderImages.find((img) => img.id === "hero-background");

  return (
    <section className="relative h-[80vh] min-h-[500px] w-full flex items-center justify-center text-center">
      {heroImage && (
        <Image
          src={heroImage.imageUrl}
          alt={heroImage.description}
          fill
          className="object-cover"
          priority
          data-ai-hint={heroImage.imageHint}
        />
      )}
      <div className="absolute inset-0 bg-gradient-to-t from-background via-background/50 to-transparent" />
      <div className="absolute inset-0 bg-black/30" />

      <div className="relative z-10 flex flex-col items-center gap-6 px-4">
        <div className="flex flex-col items-center gap-4">
          <h1 className="text-4xl md:text-6xl font-bold tracking-tighter text-white drop-shadow-md">
            Keep Your Trucks Moving, Profitably.
          </h1>
          <p className="max-w-2xl text-lg text-gray-200 drop-shadow">
            Expert dispatch services for owner-operators and small fleets. We handle the logistics, so you can focus on the road.
          </p>
        </div>
        <Button asChild size="lg" className="font-semibold">
          <Link href="#book">
            Book a Free Consultation <ArrowRight className="ml-2 h-5 w-5" />
          </Link>
        </Button>
      </div>
    </section>
  );
}
