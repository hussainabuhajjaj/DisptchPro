"use client";

import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from "@/components/ui/accordion";
import { Card, CardContent } from "@/components/ui/card";

type FaqItem = { question: string; answer: string };

type FaqSectionProps = {
  title?: string;
  subtitle?: string;
  faqs?: FaqItem[];
};

const defaultFaqs: FaqItem[] = [
  {
    question: "What types of carriers do you work with?",
    answer:
      "We work with owner-operators and small to mid-sized fleets. Our services are designed to help independent carriers thrive by providing the support and resources typically available only to larger companies.",
  },
  {
    question: "What are your dispatch fees?",
    answer:
      "We offer a competitive and transparent fee structure. Typically, we charge a small percentage of the load's gross revenue. We recommend booking a free consultation to discuss the best plan for your specific needs.",
  },
  {
    question: "Can I decline a load you offer?",
    answer:
      "Absolutely. You are the boss. We present you with the best load options, but you always have the final say. We will never force you to take a load that you don't want.",
  },
];

export default function FaqSection({
  title = "Frequently Asked Questions",
  subtitle = "Find answers to common questions about our dispatch services.",
  faqs = defaultFaqs,
}: FaqSectionProps) {
  const list = faqs?.length ? faqs : defaultFaqs;

  return (
    <section id="faq" className="w-full py-16 md:py-24 bg-secondary/30">
      <div className="container mx-auto px-4 md:px-6">
        <div className="mx-auto max-w-3xl text-center mb-12">
          <h2 className="text-3xl md:text-4xl font-bold tracking-tighter">{title}</h2>
          <p className="mt-4 text-lg text-muted-foreground">{subtitle}</p>
        </div>
        <Card className="max-w-3xl mx-auto shadow-lg">
          <CardContent className="p-6">
            <Accordion type="single" collapsible className="w-full">
              {list.map((faq, index) => (
                <AccordionItem key={index} value={`item-${index}`}>
                  <AccordionTrigger className="text-lg text-left">{faq.question}</AccordionTrigger>
                  <AccordionContent className="text-base text-muted-foreground">{faq.answer}</AccordionContent>
                </AccordionItem>
              ))}
            </Accordion>
          </CardContent>
        </Card>
      </div>
    </section>
  );
}
