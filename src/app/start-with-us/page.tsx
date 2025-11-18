
'use client';

import CarrierProfileForm from '@/components/forms/CarrierProfileForm';

export default function StartWithUsPage() {

  return (
    <div className="container mx-auto py-12 px-4 md:px-6">
      <div className="max-w-4xl mx-auto">
        <div className="text-center mb-8">
            <h1 className="text-3xl md:text-4xl font-bold tracking-tighter">Start With Us</h1>
            <p className="mt-4 text-lg text-muted-foreground">
                Please complete this form with your company's information. The better informed we are, the better we can assist you.
            </p>
        </div>
        <CarrierProfileForm />
      </div>
    </div>
  );
}
