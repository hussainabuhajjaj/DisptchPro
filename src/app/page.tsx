import Hero from "@/components/sections/Hero";
import Services from "@/components/sections/Services";
import WhyChooseUs from "@/components/sections/WhyChooseUs";
import ForShippers from "@/components/sections/ForShippers";
import ForBrokers from "@/components/sections/ForBrokers";
import ProfitCalculator from "@/components/sections/ProfitCalculator";
import Roadmap from "@/components/sections/Roadmap";
import Testimonials from "@/components/sections/Testimonials";
import Booking from "@/components/sections/Booking";
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from "@/components/ui/accordion";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

const faqs = [
  {
    question: "What types of carriers do you work with?",
    answer: "We work with owner-operators and small to mid-sized fleets. Our services are designed to help independent carriers thrive by providing the support and resources typically available only to larger companies."
  },
  {
    question: "What are your dispatch fees?",
    answer: "We offer a competitive and transparent fee structure. Typically, we charge a small percentage of the load's gross revenue. We recommend booking a free consultation to discuss the best plan for your specific needs."
  },
  {
    question: "How do you find and book loads?",
    answer: "Our experienced dispatchers use premium load boards, industry connections, and direct relationships with brokers and shippers to find the best-paying loads that match your preferences and equipment."
  },
  {
    question: "Can I decline a load you offer?",
    answer: "Absolutely. You are the boss. We present you with the best load options, but you always have the final say. We will never force you to take a load that you don't want."
  },
  {
    question: "What kind of support do you offer?",
    answer: "We provide 24/7 support for our drivers. Whether you have an issue on the road, a question about a load, or need assistance with paperwork, our team is always just a phone call away."
  }
];

function FaqSection() {
  return (
    <section id="faq" className="w-full py-16 md:py-24 bg-secondary/30">
      <div className="container mx-auto px-4 md:px-6">
        <div className="mx-auto max-w-3xl text-center mb-12">
          <h2 className="text-3xl md:text-4xl font-bold tracking-tighter">
            Frequently Asked Questions
          </h2>
          <p className="mt-4 text-lg text-muted-foreground">
            Find answers to common questions about our dispatch services.
          </p>
        </div>
        <Card className="max-w-3xl mx-auto shadow-lg">
          <CardContent className="p-6">
            <Accordion type="single" collapsible className="w-full">
              {faqs.map((faq, index) => (
                <AccordionItem key={index} value={`item-${index}`}>
                  <AccordionTrigger className="text-lg text-left">{faq.question}</AccordionTrigger>
                  <AccordionContent className="text-base text-muted-foreground">
                    {faq.answer}
                  </AccordionContent>
                </AccordionItem>
              ))}
            </Accordion>
          </CardContent>
        </Card>
      </div>
    </section>
  );
}


export default function Home() {
  return (
    <div className="flex flex-col min-h-screen">
      <Hero />
      <Services />
      <WhyChooseUs />
      <ForShippers />
      <ForBrokers />
      <ProfitCalculator />
      <Roadmap />
      <Testimonials />
      <FaqSection />
      <Booking />
    </div>
  );
}
