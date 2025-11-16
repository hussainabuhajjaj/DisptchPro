import { WandSparkles } from "lucide-react";
import FaqGeneratorClient from "../FaqGeneratorClient";

export default function FaqSection() {
  return (
    <section id="faq" className="w-full py-16 md:py-24 bg-secondary/30">
      <div className="container mx-auto px-4 md:px-6">
        <div className="mx-auto max-w-3xl text-center mb-12">
          <div className="inline-flex items-center gap-2 bg-primary/10 text-primary font-medium px-4 py-1 rounded-full mb-4">
            <WandSparkles className="h-5 w-5" />
            <span>AI-Powered Tool</span>
          </div>
          <h2 className="text-3xl md:text-4xl font-bold tracking-tighter">
            Generate Your Own FAQ
          </h2>
          <p className="mt-4 text-lg text-muted-foreground">
            Use our AI tool to instantly create a professional FAQ section for your dispatching service. Just provide some details and let our AI do the work.
          </p>
        </div>
        
        <div className="max-w-4xl mx-auto">
          <FaqGeneratorClient />
        </div>
      </div>
    </section>
  );
}
