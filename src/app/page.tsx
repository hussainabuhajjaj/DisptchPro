import Hero from "@/components/sections/Hero";
import Services from "@/components/sections/Services";
import WhyChooseUs from "@/components/sections/WhyChooseUs";
import Roadmap from "@/components/sections/Roadmap";
import Testimonials from "@/components/sections/Testimonials";
import FaqSection from "@/components/sections/FaqSection";
import Booking from "@/components/sections/Booking";

export default function Home() {
  return (
    <div className="flex flex-col min-h-screen">
      <Hero />
      <Services />
      <WhyChooseUs />
      <Roadmap />
      <Testimonials />
      <FaqSection />
      <Booking />
    </div>
  );
}
