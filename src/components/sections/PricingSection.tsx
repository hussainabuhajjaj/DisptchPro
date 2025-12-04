import { CheckCircle2, PlayCircle } from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import Link from "next/link";

const plans = [
  {
    name: "Starter",
    price: "Starting at $199/wk",
    desc: "For owner-operators who want consistent, profitable loads without long-term contracts.",
    features: [
      "You approve every load",
      "24/7 dispatcher coverage",
      "Paperwork & invoicing handled",
      "Weekly RPM and lane report",
    ],
    cta: { label: "Book a call", href: "#book", event: "pricing-starter" },
  },
  {
    name: "Fleet",
    price: "Custom per truck",
    desc: "For small fleets (2-10 trucks) needing lane planning, driver rotation, and rate negotiation.",
    features: [
      "Dedicated team + escalation SLA",
      "Lane strategy & backhaul planning",
      "Carrier packet + compliance support",
      "Driver rotation and break coverage",
    ],
    cta: { label: "Talk to sales", href: "#book", event: "pricing-fleet" },
    highlight: true,
  },
  {
    name: "Broker Assist",
    price: "Project-based",
    desc: "For brokers/shippers needing surge coverage, fast vetting, and transparent tracking.",
    features: [
      "Same-day stand-up for surge",
      "Carrier vetting & COI collection",
      "Live tracking updates",
      "Dedicated lane playbooks",
    ],
    cta: { label: "See how it works", href: "#lead-magnet", event: "pricing-broker" },
  },
];

export default function PricingSection() {
  const demoUrl = "https://www.youtube-nocookie.com/embed/C0DPdy98e4c";

  return (
    <section id="pricing" className="w-full py-16 md:py-24 bg-[#f7f9fc]">
      <div className="container mx-auto px-4 md:px-6 space-y-12">
        <div className="text-center space-y-3 max-w-3xl mx-auto">
          <span className="text-sm font-semibold uppercase tracking-[0.12em] text-primary">Pricing & Demo</span>
          <h2 className="text-3xl md:text-4xl font-bold tracking-tighter">Transparent pricing, fast onboarding</h2>
          <p className="text-lg text-muted-foreground">
            Start with a 14-day risk-free trial. Most carriers are rolling within 24 hours of kickoff.
          </p>
        </div>

        <div className="grid gap-6 lg:grid-cols-3">
          {plans.map((plan) => (
            <Card
              key={plan.name}
              className={`h-full flex flex-col shadow-sm transition-all duration-300 hover:-translate-y-2 hover:shadow-xl ${
                plan.highlight ? "border-primary/50" : ""
              }`}
            >
              <CardHeader className="space-y-2">
                <CardTitle className="text-xl">{plan.name}</CardTitle>
                <CardDescription className="text-base">{plan.desc}</CardDescription>
              </CardHeader>
              <CardContent className="flex flex-col gap-4 flex-1">
                <p className="text-2xl font-bold text-primary">{plan.price}</p>
                <ul className="space-y-2 text-sm text-muted-foreground">
                  {plan.features.map((feature) => (
                    <li key={feature} className="flex items-start gap-2">
                      <CheckCircle2 className="h-4 w-4 text-primary mt-[2px]" />
                      <span>{feature}</span>
                    </li>
                  ))}
                </ul>
                <div className="pt-2 mt-auto">
                  <Button asChild className="w-full" data-umami-event={plan.cta.event}>
                    <Link href={plan.cta.href}>{plan.cta.label}</Link>
                  </Button>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>

        <div className="grid lg:grid-cols-[1.2fr_1fr] gap-6 items-center">
          <div className="relative w-full overflow-hidden rounded-2xl shadow-lg border bg-black">
            <iframe
              src={demoUrl}
              title="Instant dispatch demo"
              className="w-full aspect-video"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowFullScreen
            />
          </div>
          <Card className="shadow-sm">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <PlayCircle className="h-5 w-5 text-primary" />
                What’s inside the 90-second demo
              </CardTitle>
              <CardDescription>See how we approve loads, handle paperwork, and send live updates.</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3 text-sm text-muted-foreground">
              <div className="flex items-start gap-2">
                <CheckCircle2 className="h-4 w-4 text-primary mt-[2px]" />
                <span>Lane intake → load approval → carrier packet submission workflow</span>
              </div>
              <div className="flex items-start gap-2">
                <CheckCircle2 className="h-4 w-4 text-primary mt-[2px]" />
                <span>How we target RPM and track on-time delivery in the portal</span>
              </div>
              <div className="flex items-start gap-2">
                <CheckCircle2 className="h-4 w-4 text-primary mt-[2px]" />
                <span>Examples of weekly reporting and compliance handoffs</span>
              </div>
              <Button asChild variant="secondary" data-umami-event="pricing-secondary-cta">
                <Link href="#book">Book a live walkthrough</Link>
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </section>
  );
}

