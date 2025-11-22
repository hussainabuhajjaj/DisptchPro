'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { LoaderCircle } from 'lucide-react';

// This is a placeholder for a real user session management hook
const useUser = () => {
  const [user, setUser] = useState<{ email: string; uid: string } | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    // TODO: Replace with a real session check against your Laravel API
    // For now, we simulate a logged-in user after a short delay.
    const timer = setTimeout(() => {
      // To test the "logged out" state, set this to null
      setUser({ email: 'carrier@example.com', uid: 'user-id-from-laravel' });
      setIsLoading(false);
    }, 500);

    return () => clearTimeout(timer);
  }, []);

  return { user, isLoading };
};

export default function DashboardPage() {
  const { user, isLoading } = useUser();
  const router = useRouter();

  useEffect(() => {
    if (!isLoading && !user) {
      router.push('/login');
    }
  }, [user, isLoading, router]);

  if (isLoading || !user) {
    return (
      <div className="flex items-center justify-center h-screen">
        <LoaderCircle className="h-8 w-8 animate-spin text-primary" />
      </div>
    );
  }

  return (
    <div className="container mx-auto py-12 px-4 md:px-6">
      <div className="space-y-8">
        <div className="text-center">
            <h1 className="text-3xl md:text-4xl font-bold tracking-tighter">Welcome to Your Dashboard</h1>
            <p className="mt-4 text-lg text-muted-foreground">
                This is your carrier portal. Manage your profile and view your information.
            </p>
        </div>

        <div className="grid gap-6">
            <Card>
                <CardHeader>
                    <CardTitle>Your Information</CardTitle>
                </CardHeader>
                <CardContent>
                    <p><strong>Email:</strong> {user.email}</p>
                    <p><strong>User ID:</strong> {user.uid}</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Blog Section</CardTitle>
                </CardHeader>
                <CardContent>
                    <p>The blog will be implemented here. You'll be able to see the latest industry news and insights.</p>
                </CardContent>
            </Card>
        </div>
      </div>
    </div>
  );
}
