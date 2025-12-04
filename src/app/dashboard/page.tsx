'use client';

import Link from 'next/link';
import { useEffect, useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { fetchBookings, BookingRecord } from '@/lib/booking-service';
import { fetchDashboardSummary, fetchDashboardLoads, DashboardSummary, LoadBoardRow } from '@/lib/dashboard-service';
import { fetchLandingContent, LandingResponse } from '@/lib/landing-content';

export default function Dashboard() {
  const [bookings, setBookings] = useState<BookingRecord[]>([]);
  const [summary, setSummary] = useState<DashboardSummary | null>(null);
  const [loads, setLoads] = useState<LoadBoardRow[]>([]);
  const [landing, setLanding] = useState<LandingResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [loadFilters, setLoadFilters] = useState<{ equipment?: string; minRpm?: number }>({});

  useEffect(() => {
    async function load() {
      try {
        const [bookingData, summaryData, loadData, landingData] = await Promise.all([
          fetchBookings(),
          fetchDashboardSummary(),
          fetchDashboardLoads(loadFilters),
          fetchLandingContent(),
        ]);
        setBookings(bookingData.bookings ?? []);
        setSummary(summaryData);
        setLoads(loadData);
        setLanding(landingData);
      } catch (err: any) {
        setError(err?.message ?? 'Unable to load dashboard data. Login may be required.');
      } finally {
        setLoading(false);
      }
    }
    load();
  }, [loadFilters]);

  const hasAuth = !error || (error && !/unauthorized|401/i.test(error));

  return (
    <div className="container mx-auto py-16 px-4 md:px-6 space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">Operations Dashboard</h1>
          <p className="text-muted-foreground">Bookings, lead magnet submissions, load previews, and site SEO/marketing.</p>
        </div>
        <div className="flex flex-wrap gap-3">
          <Button asChild variant="outline">
            <Link href="/#lead-magnet">New lead magnet</Link>
          </Button>
          <Button asChild>
            <Link href="/#book">Book a call</Link>
          </Button>
        </div>
      </div>

      {/* Summary cards */}
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm text-muted-foreground">Loads this month</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-3xl font-bold">{summary?.loadsThisMonth ?? '—'}</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm text-muted-foreground">Avg rate per mile</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-3xl font-bold">
              {summary?.avgRatePerMile ? `$${summary.avgRatePerMile.toFixed(2)}` : '—'}
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm text-muted-foreground">Next settlement</CardTitle>
          </CardHeader>
          <CardContent className="space-y-1">
            <p className="text-xl font-semibold">
              {summary?.nextSettlement ? `$${summary.nextSettlement.amount.toLocaleString()}` : '—'}
            </p>
            <p className="text-muted-foreground text-sm">
              {summary?.nextSettlement?.date ? new Date(summary.nextSettlement.date).toLocaleDateString() : '—'}
            </p>
            {summary?.nextSettlement?.issues?.length ? (
              <p className="text-xs text-amber-600">Issues: {summary.nextSettlement.issues.join(', ')}</p>
            ) : null}
          </CardContent>
        </Card>
      </div>

      {loading && (
        <Card className="shadow-sm">
          <CardContent className="py-8 text-muted-foreground">Loading dashboard data...</CardContent>
        </Card>
      )}

      {!loading && error && (
        <Card className="shadow-sm">
          <CardHeader>
            <CardTitle className="text-lg">Sign in required</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4 text-muted-foreground">
            <p>{error}</p>
            <div className="flex gap-3">
              <Button asChild>
                <Link href="/login">Login</Link>
              </Button>
              <Button asChild variant="outline">
                <Link href="/#booking">Back to site</Link>
              </Button>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Bookings & leads */}
      {!loading && !error && (
        <Card className="shadow-sm">
          <CardHeader>
            <CardTitle className="text-lg">Recent submissions</CardTitle>
          </CardHeader>
          <CardContent className="overflow-x-auto">
            {bookings.length === 0 ? (
              <p className="text-muted-foreground">No submissions yet.</p>
            ) : (
              <table className="w-full text-sm">
                <thead className="text-muted-foreground">
                  <tr className="border-b">
                    <th className="py-2 pr-4 text-left">Title</th>
                    <th className="py-2 pr-4 text-left">Type</th>
                    <th className="py-2 pr-4 text-left">When</th>
                    <th className="py-2 pr-4 text-left">Contact</th>
                    <th className="py-2 pr-4 text-left">Status</th>
                  </tr>
                </thead>
                <tbody>
                  {bookings.map((b) => (
                    <tr key={b.id} className="border-b last:border-0">
                      <td className="py-3 pr-4">{b.title}</td>
                      <td className="py-3 pr-4 capitalize">{b.type}</td>
                      <td className="py-3 pr-4 text-muted-foreground">
                        {new Date(b.start_at).toLocaleString()}
                      </td>
                      <td className="py-3 pr-4 text-muted-foreground">
                        {b.email || "—"}
                        {b.phone ? ` · ${b.phone}` : ""}
                      </td>
                      <td className="py-3 pr-4">
                        <Badge variant={b.status === 'pending' ? 'secondary' : 'default'}>{b.status}</Badge>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </CardContent>
        </Card>
      )}

      {/* Load board preview */}
      {!loading && hasAuth && (
        <Card className="shadow-sm">
          <CardHeader className="flex flex-col gap-3">
            <div className="flex items-center justify-between">
              <CardTitle className="text-lg">Load board preview</CardTitle>
              <div className="flex gap-2">
                <Input
                  placeholder="Equipment (e.g., Reefer)"
                  value={loadFilters.equipment ?? ''}
                  onChange={(e) => setLoadFilters((prev) => ({ ...prev, equipment: e.target.value || undefined }))}
                  className="h-9 w-44"
                />
                <Input
                  placeholder="Min RPM"
                  type="number"
                  value={loadFilters.minRpm ?? ''}
                  onChange={(e) =>
                    setLoadFilters((prev) => ({
                      ...prev,
                      minRpm: e.target.value ? Number(e.target.value) : undefined,
                    }))
                  }
                  className="h-9 w-28"
                />
              </div>
            </div>
          </CardHeader>
          <CardContent className="overflow-x-auto">
            {loads.length === 0 ? (
              <p className="text-muted-foreground">No loads match the current filters.</p>
            ) : (
              <table className="w-full text-sm">
                <thead className="text-muted-foreground">
                  <tr className="border-b">
                    <th className="py-2 pr-4 text-left">Lane</th>
                    <th className="py-2 pr-4 text-left">Equipment</th>
                    <th className="py-2 pr-4 text-left">RPM</th>
                    <th className="py-2 pr-4 text-left">Pickup</th>
                    <th className="py-2 pr-4 text-left">Status</th>
                  </tr>
                </thead>
                <tbody>
                  {loads.map((row) => (
                    <tr key={row.id} className="border-b last:border-0">
                      <td className="py-3 pr-4">{row.lane}</td>
                      <td className="py-3 pr-4">{row.equipment}</td>
                      <td className="py-3 pr-4">
                        {row.rpm !== null ? `$${row.rpm.toFixed(2)}/mi` : '—'}
                      </td>
                      <td className="py-3 pr-4 text-muted-foreground">
                        {row.pickup ? new Date(row.pickup).toLocaleDateString() : '—'}
                      </td>
                      <td className="py-3 pr-4 capitalize text-muted-foreground">{row.status || 'posted'}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </CardContent>
        </Card>
      )}

      {/* SEO & marketing */}
      {!loading && (
        <Card className="shadow-sm">
          <CardHeader>
            <CardTitle className="text-lg">SEO & marketing snapshot</CardTitle>
          </CardHeader>
          <CardContent className="grid gap-4 md:grid-cols-3">
            <div className="space-y-2">
              <p className="text-xs font-semibold text-muted-foreground uppercase tracking-[0.08em]">Meta title</p>
              <p className="text-sm">{(landing as any)?.settings?.meta_title || '—'}</p>
              <p className="text-xs text-muted-foreground">
                {(landing as any)?.settings?.meta_description || '—'}
              </p>
            </div>
            <div className="space-y-2">
              <p className="text-xs font-semibold text-muted-foreground uppercase tracking-[0.08em]">Brand & contact</p>
              <p className="text-sm">
                {(landing as any)?.settings?.site_name || '—'} · {(landing as any)?.settings?.contact_email || '—'}
              </p>
              <p className="text-xs text-muted-foreground">{(landing as any)?.settings?.contact_phone || '—'}</p>
            </div>
            <div className="space-y-2">
              <p className="text-xs font-semibold text-muted-foreground uppercase tracking-[0.08em]">Hero badge/CTA</p>
              <p className="text-sm">
                {(landing?.sections || []).find((s) => s.slug === 'hero')?.content?.['badge'] || '—'}
              </p>
              <p className="text-xs text-muted-foreground">
                CTA: {(landing?.sections || []).find((s) => s.slug === 'hero')?.content?.['cta_primary'] || '—'}
              </p>
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
