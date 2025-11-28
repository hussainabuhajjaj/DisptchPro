import Image from "next/image";
import { PlaceHolderImages } from "@/lib/placeholder-images";
import { CheckCircle } from "lucide-react";

type WhyUsProps = {
  title?: string;
  subtitle?: string;
  bullets?: string[];
  imageUrl?: string;
};

const defaultBullets = [
  "Increase your weekly revenue",
  "Reduce deadhead miles significantly",
  "Eliminate paperwork headaches",
  "Gain a dedicated 24/7 support partner",
  "Access to top-paying loads from our network",
];

export default function WhyChooseUs({
  title = "Why Partner with H&A Dispatch?",
  subtitle = "We are more than just a dispatch service; we are your strategic partner in success. We focus on your profitability and efficiency so you can focus on driving.",
  bullets = defaultBullets,
  imageUrl,
}: WhyUsProps) {
  const whyChooseUsImage = imageUrl
    ? { imageUrl, description: "Why choose us" }
    : PlaceHolderImages.find((img) => img.id === "why-choose-us");
  return (
    <section id="why-us" className="w-full py-16 md:py-24">
      <div className="container mx-auto px-4 md:px-6">
        <div className="grid md:grid-cols-2 gap-12 items-center">
          <div className="flex flex-col gap-6">
            <h2 className="text-3xl md:text-4xl font-bold tracking-tighter">{title}</h2>
            <p className="text-lg text-muted-foreground">{subtitle}</p>
            <ul className="grid gap-3 text-lg">
              {bullets.map((text, index) => (
                <li key={index} className="flex items-center gap-3">
                  <CheckCircle className="h-6 w-6 text-primary" />
                  <span>{text}</span>
                </li>
              ))}
            </ul>
          </div>
          <div className="relative h-80 w-full rounded-lg overflow-hidden shadow-lg">
            {whyChooseUsImage && (
              <Image
                src={whyChooseUsImage.imageUrl}
                alt={whyChooseUsImage.description}
                fill
                className="object-cover"
                data-ai-hint={whyChooseUsImage.imageHint}
                sizes="(max-width: 768px) 100vw, 50vw"
                unoptimized
              />
            )}
            <div className="absolute inset-0 bg-black/20" />
          </div>
        </div>
      </div>
    </section>
  );
}
