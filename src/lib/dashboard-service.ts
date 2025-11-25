import { apiClient } from "./httpClient";

export type DashboardSummary = {
  loadsThisMonth: number;
  avgRatePerMile: number;
  nextSettlement: {
    amount: number;
    date: string;
    issues?: string[];
  };
};

export type LoadBoardItem = {
  id: string;
  lane: string;
  equipment: string;
  rpm: number;
  pickup: string;
};

export async function fetchDashboardSummary() {
  return apiClient.get<DashboardSummary>("dashboard/summary", { withAuth: true });
}

export async function fetchLoadBoard(options?: { query?: Record<string, any>; withAuth?: boolean }) {
  return apiClient.get<{ data: LoadBoardItem[]; meta?: { total: number; page: number; per_page: number } }>(
    "loads",
    options,
  );
}
