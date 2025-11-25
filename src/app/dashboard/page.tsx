'use client';

import Link from 'next/link';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';

/**
 * The carrier dashboard has been removed per request.
 * This placeholder keeps the route alive without any auth logic or API calls.
 */
export default function DashboardPlaceholder() {
  return (
    <div className="container mx-auto py-16 px-4 md:px-6">
      <Card className="max-w-3xl mx-auto">
        <CardHeader>
          <CardTitle className="text-2xl">Portal Unavailable</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <p className="text-muted-foreground">
            The carrier dashboard has been disabled while we rebuild the experience. You can still
            browse the public site and request service using the booking form on the homepage.
          </p>
          <div className="flex gap-3">
            <Button asChild>
              <Link href="/">Return to homepage</Link>
            </Button>
            <Button asChild variant="outline">
              <Link href="/#booking">Book a call</Link>
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
