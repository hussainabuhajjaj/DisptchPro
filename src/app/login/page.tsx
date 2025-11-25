'use client';

import Link from 'next/link';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';

/**
 * The login flow has been removed per request.
 * This placeholder keeps the route alive without any auth logic or API calls.
 */
export default function LoginPage() {
  return (
    <div className="flex items-center justify-center min-h-[calc(100vh-10rem)] py-12">
      <Card className="mx-auto max-w-md">
        <CardHeader>
          <CardTitle className="text-2xl">Portal Login Disabled</CardTitle>
          <CardDescription>
            We are rebuilding the authentication experience. In the meantime, you can still engage with
            DispatchPro through the public site and booking form.
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
