"use client";

import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import Link from "next/link";

type ResourceItem = { title: string; description: string; href: string };

type ResourcesSectionProps = {
  title?: string;
  subtitle?: string;
  resources?: ResourceItem[];
};

const defaultResources: ResourceItem[] = [
  {
    title: "How to avoid deadhead and still keep your favorite lanes",
    description: "Practical tips to minimize empty miles without losing your preferred routes.",
    href: "#",
  },
  {
    title: "Reefer vs Dry Van: which is better for your current situation?",
    description: "A quick comparison to choose the equipment strategy that fits your business.",
    href: "#",
  },
  {
    title: "How to know if a dispatcher is good or just wasting your time",
    description: "Red flags, green flags, and the questions to ask before you commit.",
    href: "#",
  },
];

export default function ResourcesSection({
  title = "Resources for Carriers",
  subtitle = "Short, actionable guides to help you run profitably.",
  resources = defaultResources,
}: ResourcesSectionProps) {
  const list = resources?.length ? resources : defaultResources;
  return (
    <section className="w-full py-16 md:py-20 bg-secondary/20">
      <div className="container mx-auto px-4 md:px-6">
        <div className="mx-auto max-w-3xl text-center mb-10">
          <h2 className="text-3xl md:text-4xl font-bold tracking-tighter">{title}</h2>
          <p className="mt-4 text-lg text-muted-foreground">{subtitle}</p>
        </div>
        <div className="grid gap-6 md:grid-cols-3">
          {list.map((resource) => (
              <Card
              key={resource.title}
              className="h-full transition-all duration-300 hover:shadow-lg hover:-translate-y-1"
            >
              <CardHeader>
                <CardTitle className="text-lg">{resource.title}</CardTitle>
                <CardDescription>{resource.description}</CardDescription>
              </CardHeader>
              <CardContent>
                <Link
                  href={resource.href}
                  className="text-primary text-sm font-semibold hover:underline"
                  data-umami-event="resource-link"
                >
                  Read more
                </Link>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </section>
  );
}
