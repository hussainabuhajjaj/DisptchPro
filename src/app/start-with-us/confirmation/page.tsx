'use client';

import Link from "next/link";
import { CheckCircle2 } from "lucide-react";

import { Button } from "@/components/ui/button";

export default function OnboardingConfirmationPage() {
  return (
    <div className="container mx-auto py-16 px-4 md:px-6">
      <div className="mx-auto max-w-2xl rounded-lg border bg-card p-8 text-center shadow-sm">
        <div className="flex justify-center">
          <CheckCircle2 className="h-12 w-12 text-primary" />
        </div>
        <h1 className="mt-6 text-3xl font-bold tracking-tight">Application submitted</h1>
        <p className="mt-3 text-muted-foreground">
          Thanks for completing onboarding. Our team is reviewing your documents and will reach out
          if anything else is needed. You can close this page or return to the home page.
        </p>
        <div className="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
          <Button asChild>
            <Link href="/">Back to home</Link>
          </Button>
          <Button variant="outline" asChild>
            <Link href="/#booking">Book a call</Link>
          </Button>
        </div>
      </div>
    </div>
  );
}
