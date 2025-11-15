import { Card, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Truck, Clock, Route, FileText } from "lucide-react";

const services = [
  {
    icon: <Clock className="h-8 w-8 text-primary" />,
    title: "24/7 Dispatch Support",
    description: "Round-the-clock support to ensure you're never stranded. We manage your loads and routes anytime, anywhere.",
  },
  {
    icon: <Truck className="h-8 w-8 text-primary" />,
    title: "Expert Load Matching",
    description: "We find the best-paying loads that fit your schedule and preferences, maximizing your profitability and minimizing deadhead miles.",
  },
  {
    icon: <Route className="h-8 w-8 text-primary" />,
    title: "Intelligent Route Optimization",
    description: "Save time and fuel with our advanced route planning, avoiding traffic, and navigating the most efficient paths.",
  },
  {
    icon: <FileText className="h-8 w-8 text-primary" />,
    title: "Paperwork & Invoicing",
    description: "We handle all the tedious paperwork, from carrier packets to invoicing, so you get paid faster and with less hassle.",
  },
];

export default function Services() {
  return (
    <section id="services" className="w-full py-16 md:py-24 bg-secondary/30">
      <div className="container mx-auto px-4 md:px-6">
        <div className="mx-auto max-w-3xl text-center mb-12">
          <h2 className="text-3xl md:text-4xl font-bold tracking-tighter">
            Services Tailored for Your Success
          </h2>
          <p className="mt-4 text-lg text-muted-foreground">
            We provide comprehensive dispatching solutions to keep you on the move and profitable.
          </p>
        </div>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {services.map((service, index) => (
            <Card key={index} className="flex flex-col items-start p-6 bg-card hover:shadow-lg transition-shadow duration-300">
              <div className="mb-4">{service.icon}</div>
              <CardHeader className="p-0">
                <CardTitle className="text-xl font-semibold mb-2">{service.title}</CardTitle>
                <CardDescription className="text-base text-muted-foreground">{service.description}</CardDescription>
              </CardHeader>
            </Card>
          ))}
        </div>
      </div>
    </section>
  );
}
