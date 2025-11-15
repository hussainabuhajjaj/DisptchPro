import { Card, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { PhoneCall, CalendarCheck, Settings, TrendingUp } from "lucide-react";

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
    <section id="roadmap" className="w-full py-16 md:py-24">
      <div className="container mx-auto px-4 md:px-6">
        <div className="mx-auto max-w-3xl text-center mb-12">
          <h2 className="text-3xl md:text-4xl font-bold tracking-tighter">
            Your Roadmap to Success
          </h2>
          <p className="mt-4 text-lg text-muted-foreground">
            A simple, four-step process to get you on the road to higher profits and less stress.
          </p>
        </div>
        <div className="relative">
          <div className="absolute left-1/2 top-0 bottom-0 w-px bg-border -translate-x-1/2 hidden md:block" />
          <div className="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
            {roadmapSteps.map((step, index) => (
              <div key={index} className={`flex items-center gap-6 ${index % 2 === 1 ? "md:flex-row-reverse md:text-right" : "text-left"}`}>
                <div className="hidden md:block relative">
                   <div className="w-4 h-4 bg-primary rounded-full absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-10" />
                </div>
                <Card className={`w-full hover:shadow-lg transition-shadow duration-300 ${index % 2 === 1 ? "md:text-right" : "md:text-left"}`}>
                   <CardHeader className={`flex flex-col items-center gap-4 p-6 sm:flex-row ${index % 2 === 1 ? "md:flex-row-reverse" : ""}`}>
                    {step.icon}
                    <div className="flex-1">
                      <CardTitle className="text-xl font-semibold">{step.title}</CardTitle>
                      <CardDescription className="mt-2 text-base">
                        {step.description}
                      </CardDescription>
                    </div>
                  </CardHeader>
                </Card>
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}
