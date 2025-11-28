import { Card, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { PhoneCall, CalendarCheck, Settings, TrendingUp } from "lucide-react";
import { cn } from "@/lib/utils";

const roadmapSteps = [
  {
    icon: <PhoneCall className="h-10 w-10 text-primary" />,
    title: "1. Book a Free Call",
    description: "Schedule a no-obligation consultation to discuss your needs and see how we can help.",
  },
  {
    icon: <CalendarCheck className="h-10 w-10 text-primary" />,
    title: "2. Start Your Free Trial",
    description: "Experience our full range of services with a 14-day free trial. No credit card required.",
  },
  {
    icon: <Settings className="h-10 w-10 text-primary" />,
    title: "3. Seamless Onboarding",
    description: "Our team will guide you through a simple setup process to get you ready for the road.",
  },
  {
    icon: <TrendingUp className="h-10 w-10 text-primary" />,
    title: "4. Maximize Your Profit",
    description: "Go live with our dispatch experts working to find you the best loads and optimize your earnings.",
  },
];

export default function Roadmap() {
  return (
    <section id="roadmap" className="w-full py-20 md:py-24 bg-white">
      <div className="container mx-auto px-4 md:px-6">
        <div className="mx-auto max-w-3xl text-center mb-12">
          <span className="text-sm font-semibold tracking-[0.08em] uppercase text-primary">
            How it works
          </span>
          <h2 className="text-3xl md:text-4xl font-bold tracking-tighter">
            Your Roadmap to Success
          </h2>
          <p className="mt-4 text-lg text-muted-foreground">
            A simple, four-step process to get you on the road to higher profits and less stress.
          </p>
        </div>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {roadmapSteps.map((step, index) => (
            <Card
              key={index}
              className="w-full h-full rounded-2xl border border-muted/60 bg-card shadow-lg transition-all duration-300 hover:-translate-y-2 hover:shadow-xl"
            >
              <CardHeader className="flex flex-col gap-4 p-6">
                <div className="inline-flex items-center gap-3">
                  <span className="inline-flex h-10 w-10 items-center justify-center rounded-full bg-primary/15 text-primary font-bold">
                    {index + 1}
                  </span>
                  {step.icon}
                </div>
                <div className="flex-1">
                  <CardTitle className="text-xl font-semibold">{step.title}</CardTitle>
                  <CardDescription className="mt-2 text-base">
                    {step.description}
                  </CardDescription>
                </div>
              </CardHeader>
            </Card>
          ))}
        </div>
      </div>
    </section>
  );
}
