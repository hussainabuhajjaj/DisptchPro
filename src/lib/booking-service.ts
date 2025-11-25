import { apiClient } from "./httpClient";

export type BookingPayload = {
  title: string;
  type: "call" | "onboarding" | "demo";
  start_at: string;
  end_at?: string;
  carrier_name?: string;
  phone?: string;
  email?: string;
  notes?: string;
};

export type BookingRecord = {
  id: number;
  title: string;
  type: string;
  status: string;
  start_at: string;
  end_at?: string | null;
  carrier_name?: string | null;
  phone?: string | null;
  email?: string | null;
  notes?: string | null;
};

export async function createBooking(payload: BookingPayload) {
  return apiClient.post<{ success: boolean; booking: BookingRecord }>("bookings", {
    body: payload,
  });
}

export async function fetchBookings(options?: { query?: Record<string, any> }) {
  return apiClient.get<{ bookings: BookingRecord[]; meta?: { total: number; page: number; per_page: number } }>(
    "bookings",
    { withAuth: true, ...options },
  );
}
