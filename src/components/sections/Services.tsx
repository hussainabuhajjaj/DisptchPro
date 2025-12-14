import { Card, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Truck, Clock, Route, FileText } from "lucide-react";

type ServiceItem = { title: string; description: string };

type ServicesProps = {
  title?: string;
  subtitle?: string;
  items?: ServiceItem[];
};

const defaultServices: ServiceItem[] = [
  {
    title: "24/7 Dispatch Support",
    description: "Round-the-clock support to ensure you're never stranded. We manage your loads and routes anytime, anywhere.",
  },
  {
    title: "Expert Load Matching",
    description: "We find the best-paying loads that fit your schedule and preferences, maximizing your profitability and minimizing deadhead miles.",
  },
  {
    title: "Intelligent Route Optimization",
    description: "Save time and fuel with our advanced route planning, avoiding traffic, and navigating the most efficient paths.",
  },
  {
    title: "Paperwork & Invoicing",
    description: "We handle all the tedious paperwork, from carrier packets to invoicing, so you get paid faster and with less hassle.",
  },
];

const icons = [Clock, Truck, Route, FileText];

export default function Services({
  title = "Services Tailored for Your Success",
  subtitle = "We provide comprehensive dispatching solutions to keep you on the move and profitable.",
  items = defaultServices,
}: ServicesProps) {
  return (
    <section
      id="services"
      className="w-full py-20 md:py-24 bg-[#eaf3fb] text-slate-900 dark:bg-slate-900 dark:text-slate-100"
    >
      <div className="container mx-auto px-4 md:px-6">
        <div className="mx-auto max-w-3xl text-center mb-12">
          <span className="text-sm font-semibold tracking-[0.08em] uppercase text-primary">
            Services
          </span>
          <h2 className="text-3xl md:text-4xl font-bold tracking-tighter mt-2">{title}</h2>
          <p className="mt-4 text-lg text-muted-foreground dark:text-slate-300">{subtitle}</p>
        </div>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {items.map((service, index) => {
            const Icon = icons[index % icons.length];
            return (
              <Card
                key={service.title + index}
                className="flex flex-col items-start p-6 bg-card rounded-2xl shadow-lg transition-all duration-300 hover:shadow-xl hover:-translate-y-2 hover:border-primary/40 dark:bg-slate-800 dark:border-slate-700"
              >
                <div className="mb-4 inline-flex items-center justify-center rounded-full bg-primary/10 text-primary h-12 w-12">
                  <Icon className="h-6 w-6" />
                </div>
                <CardHeader className="p-0">
                  <CardTitle className="text-xl font-semibold mb-2">{service.title}</CardTitle>
                  <CardDescription className="text-base text-muted-foreground dark:text-slate-300">
                    {service.description}
                  </CardDescription>
                </CardHeader>
              </Card>
            );
          })}
        </div>
      </div>
    </section>
  );
}
