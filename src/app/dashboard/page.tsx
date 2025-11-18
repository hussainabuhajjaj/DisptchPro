'use client';

import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useUser } from '@/firebase';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { LoaderCircle, Edit } from 'lucide-react';
import Link from 'next/link';

export default function DashboardPage() {
  const { user, isUserLoading } = useUser();
  const router = useRouter();

  useEffect(() => {
    if (!isUserLoading && !user) {
      router.push('/login');
    }
  }, [user, isUserLoading, router]);

  if (isUserLoading || !user) {
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

        <div className="grid gap-6 md:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>Your Information</CardTitle>
                </CardHeader>
                <CardContent>
                    <p><strong>Email:</strong> {user.email}</p>
                    <p><strong>UID:</strong> {user.uid}</p>
                </CardContent>
            </Card>

            <Card className="flex flex-col justify-center">
                <CardHeader>
                    <CardTitle>Carrier Profile</CardTitle>
                    <CardDescription>Complete your profile to help us find the best loads for you.</CardDescription>
                </CardHeader>
                <CardContent>
                    <Button asChild>
                        <Link href="/dashboard/profile">
                            <Edit className="mr-2 h-4 w-4" /> Complete Your Profile
                        </Link>
                    </Button>
                </CardContent>
            </Card>
        </div>

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
  );
}
