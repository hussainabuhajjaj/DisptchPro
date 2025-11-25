
'use client';

import { useEffect, useState } from "react";
import Link from "next/link";
import CarrierOnboardingWizard from "@/components/forms/carrier-wizard/CarrierOnboardingWizard";
import { getDraftToken, clearDraftToken } from "@/lib/carrier-draft-storage";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { Button } from "@/components/ui/button";

export default function StartWithUsPage() {
  const [hasDraft, setHasDraft] = useState(false);

  useEffect(() => {
    setHasDraft(!!getDraftToken());
  }, []);

  return (
    <div className="container mx-auto py-12 px-4 md:px-6">
      <div className="max-w-4xl mx-auto">
        <div className="text-center mb-8">
            <h1 className="text-3xl md:text-4xl font-bold tracking-tighter">Start With Us</h1>
            <p className="mt-4 text-lg text-muted-foreground">
                Please complete this form with your company's information. The better informed we are, the better we can assist you.
            </p>
        </div>
        {hasDraft && (
          <div className="mb-6">
            <Alert>
              <AlertTitle>Resume your draft?</AlertTitle>
              <AlertDescription className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                You have a saved application. It will load automatically. You can also clear it to start fresh.
                <div className="flex gap-2">
                  <Button size="sm" variant="secondary" asChild>
                    <Link href="#wizard">Go to form</Link>
                  </Button>
                  <Button
                    size="sm"
                    variant="outline"
                    onClick={() => {
                      clearDraftToken();
                      setHasDraft(false);
                    }}
                  >
                    Start over
                  </Button>
                </div>
              </AlertDescription>
            </Alert>
          </div>
        )}
        <div id="wizard">
          <CarrierOnboardingWizard />
        </div>
      </div>
    </div>
  );
}
