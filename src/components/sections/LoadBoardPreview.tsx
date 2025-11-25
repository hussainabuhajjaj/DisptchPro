"use client";

import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";

type LoadItem = {
  origin: string;
  destination: string;
  equipment?: string;
  rate?: string;
  rpm?: string;
  pickup?: string;
};

type LoadBoardPreviewProps = {
  title?: string;
  subtitle?: string;
  loads?: LoadItem[];
};

const defaultLoads: LoadItem[] = [
  { origin: "Dallas, TX", destination: "Atlanta, GA", equipment: "Dry Van", rate: "$2.70/mi", pickup: "Tomorrow" },
  { origin: "Chicago, IL", destination: "Columbus, OH", equipment: "Reefer", rate: "$3.10/mi", pickup: "In 2 days" },
];

export default function LoadBoardPreview({
  title = "Load board preview",
  subtitle = "See a snapshot of live loads we’re booking today.",
  loads = defaultLoads,
}: LoadBoardPreviewProps) {
  if (!loads || loads.length === 0) {
    loads = defaultLoads;
  }

  return (
    <section className="w-full py-16 md:py-20 bg-secondary/30">
      <div className="container mx-auto px-4 md:px-6">
        <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between mb-6">
          <div>
            <h2 className="text-3xl md:text-4xl font-bold tracking-tighter">{title}</h2>
            <p className="mt-2 text-lg text-muted-foreground">{subtitle}</p>
          </div>
        </div>
        <div className="grid gap-3">
          {loads.map((load, idx) => (
            <div
              key={idx}
              className="flex flex-col gap-2 rounded-lg border bg-card p-4 sm:flex-row sm:items-center sm:justify-between"
            >
              <div>
                <p className="font-semibold">
                  {load.origin} → {load.destination}
                </p>
                <p className="text-sm text-muted-foreground">
                  {load.equipment || "—"} • Pickup {load.pickup || "Soon"}
                </p>
              </div>
              <div className="flex items-center gap-3">
                <Badge variant="secondary">{load.rate || load.rpm || "TBD"}</Badge>
                <Button size="sm">Request load</Button>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
