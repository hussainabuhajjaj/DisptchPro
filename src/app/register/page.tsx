'use client';

import Link from 'next/link';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';

/**
 * Registration has been removed per request.
 * This page now simply directs visitors to the public booking flow.
 */
export default function RegisterPage() {
  return (
    <div className="flex items-center justify-center min-h-[calc(100vh-10rem)] py-12">
      <Card className="mx-auto max-w-md">
        <CardHeader>
          <CardTitle className="text-2xl">Self-serve sign up unavailable</CardTitle>
          <CardDescription>
            We&apos;ve paused account creation while we rebuild the portal. Please use the booking form to
            get started with DispatchPro.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
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
