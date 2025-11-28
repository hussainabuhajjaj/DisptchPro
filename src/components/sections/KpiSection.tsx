"use client";

type Metric = { label: string; value: string };

type KpiSectionProps = {
  title?: string;
  subtitle?: string;
  metrics?: Metric[];
};

const defaultMetrics: Metric[] = [
  { label: "Avg rate per mile", value: "$2.85" },
  { label: "Loads this month", value: "38" },
  { label: "On-time delivery", value: "98%" },
];

export default function KpiSection({
  title = "Operational KPIs",
  subtitle = "We track the numbers that matter to your bottom line.",
  metrics = defaultMetrics,
}: KpiSectionProps) {
  if (!metrics || metrics.length === 0) {
    metrics = defaultMetrics;
  }

  return (
    <section id="kpis" className="w-full py-20 md:py-24 bg-[#0b2a45] text-white">
      <div className="container mx-auto px-4 md:px-6">
        <div className="mx-auto max-w-3xl text-center mb-12">
          <span className="text-sm font-semibold tracking-[0.08em] uppercase text-primary">
            Metrics
          </span>
          <h2 className="text-3xl md:text-4xl font-bold tracking-tighter mt-2">{title}</h2>
          <p className="mt-4 text-lg text-white/80">{subtitle}</p>
        </div>
        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {metrics.map((metric) => (
            <div
              key={metric.label}
              className="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-lg transition-all duration-200 hover:bg-white/10"
            >
              <p className="text-sm font-medium text-white/70">{metric.label}</p>
              <p className="mt-2 text-3xl font-bold">{metric.value}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
