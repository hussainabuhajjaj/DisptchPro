import { apiClient } from "./httpClient";

export type DashboardSummary = {
  loadsThisMonth: number;
  avgRatePerMile: number;
  nextSettlement: {
    amount: number;
    date: string;
    issues: string[];
  };
};

export type LoadBoardRow = {
  id: string;
  lane: string;
  equipment: string;
  rpm: number | null;
  pickup: string | null;
  status?: string;
  client?: string;
};

export async function fetchDashboardSummary(): Promise<DashboardSummary | null> {
  try {
    const data = await apiClient.get<DashboardSummary>("dashboard/summary", { withAuth: true });
    return data;
  } catch (err) {
    if (process.env.NODE_ENV === "development") {
      console.warn("[dashboard] failed to fetch summary", err);
    }
    return null;
  }
}

export async function fetchDashboardLoads(params?: { equipment?: string; minRpm?: number }) {
  try {
    type TmsLoad = {
      id: number | string;
      load_number: string;
      status?: string;
      trailer_type?: string | null;
      equipment_requirements?: string | null;
      rate_to_client?: number | null;
      fuel_surcharge?: number | null;
      distance_miles?: number | null;
      stops?: Array<{
        sequence?: number | null;
        city?: string | null;
        state?: string | null;
        date_from?: string | null;
        date_to?: string | null;
      }>;
      client?: {
        name?: string | null;
      };
    };

    const data = await apiClient.get<{ data: TmsLoad[]; meta?: Record<string, unknown> }>("tms/loads", {
      withAuth: true,
      query: { status: "posted" },
    });

    const toLane = (stops: TmsLoad["stops"]): { lane: string; pickup: string | null } => {
      const sorted = [...(stops || [])].sort((a, b) => (a.sequence ?? 0) - (b.sequence ?? 0));
      const origin = sorted[0];
      const destination = sorted[sorted.length - 1];
      const originLabel = origin ? `${origin.city || "TBD"}, ${origin.state || ""}`.trim() : "TBD";
      const destLabel = destination ? `${destination.city || "TBD"}, ${destination.state || ""}`.trim() : "TBD";
      const pickup = origin?.date_from || origin?.date_to || null;
      return { lane: `${originLabel} → ${destLabel}`, pickup };
    };

    const rows: LoadBoardRow[] = (data.data || []).map((load) => {
      const rpmBase = (load.rate_to_client || 0) + (load.fuel_surcharge || 0);
      const miles = load.distance_miles || 0;
      const rpm = miles > 0 ? rpmBase / miles : null;
      const { lane, pickup } = toLane(load.stops);

      const equipment = load.trailer_type || load.equipment_requirements || "—";
      return {
        id: String(load.id),
        lane,
        equipment,
        rpm,
        pickup,
        status: load.status,
        client: load.client?.name || undefined,
      };
    });

    return rows.filter((row) => {
      if (params?.equipment) {
        const value = params.equipment.toLowerCase();
        if (!row.equipment.toLowerCase().includes(value)) return false;
      }
      if (params?.minRpm && row.rpm !== null && row.rpm < params.minRpm) return false;
      return true;
    });
  } catch (err) {
    if (process.env.NODE_ENV === "development") {
      console.warn("[dashboard] failed to fetch loads", err);
    }
    return [];
  }
}
