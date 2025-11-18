
'use client';

import { useState } from 'react';
import { useForm, FormProvider } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import * as z from 'zod';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import { ArrowRight, ArrowLeft, LoaderCircle, Check } from 'lucide-react';

const carrierInfoSchema = z.object({
  companyName: z.string().min(1, 'Company name is required'),
  dba: z.string().optional(),
  physicalAddress: z.string().min(1, 'Physical address is required'),
  physicalCity: z.string().min(1, 'City is required'),
  physicalState: z.string().min(1, 'State is required'),
  physicalZip: z.string().min(1, 'ZIP code is required'),
  mailingAddress: z.string().optional(),
  mailingCity: z.string().optional(),
  mailingState: z.string().optional(),
  mailingZip: z.string().optional(),
  mainContact: z.string().min(1, 'Main contact is required'),
  email: z.string().email('Invalid email address'),
  officePhone: z.string().min(1, 'Office phone is required'),
  fax: z.string().optional(),
  cellPhone: z.string().min(1, 'Cell phone is required'),
  emergencyContact: z.string().min(1, 'Emergency contact is required'),
  emergencyPhone: z.string().min(1, 'Emergency phone is required'),
  mcNumber: z.string().min(1, 'MC # is required'),
  dotNumber: z.string().min(1, 'DOT # is required'),
  einNumber: z.string().min(1, 'EIN # is required'),
  scacCode: z.string().optional(),
  twicCertified: z.string().optional(),
  hazmatCertified: z.string().optional(),
});

// We will add schemas for other parts later
const fullFormSchema = z.object({
  carrierInfo: carrierInfoSchema,
});

type FullFormValues = z.infer<typeof fullFormSchema>;

const steps = [
  { id: 'carrier-info', title: 'Carrier Information', fields: Object.keys(carrierInfoSchema.shape) },
  { id: 'equipment', title: 'Equipment' },
  { id: 'operation', title: 'Area of Operation' },
  { id: 'factoring', title: 'Factoring' },
  { id: 'insurance', title: 'Insurance' },
];

function CarrierInfoForm() {
    return (
        <div className="space-y-6">
            <h3 className="text-lg font-medium">Company Details</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormField name="carrierInfo.companyName" render={({ field }) => (
                    <FormItem>
                        <FormLabel>Company Name</FormLabel>
                        <FormControl><Input {...field} /></FormControl>
                        <FormMessage />
                    </FormItem>
                )} />
                <FormField name="carrierInfo.dba" render={({ field }) => (
                    <FormItem>
                        <FormLabel>DBA (If any)</FormLabel>
                        <FormControl><Input {...field} /></FormControl>
                        <FormMessage />
                    </FormItem>
                )} />
            </div>

            <Separator />
            <h3 className="text-lg font-medium">Physical Address</h3>
             <div className="space-y-4">
                <FormField name="carrierInfo.physicalAddress" render={({ field }) => (
                    <FormItem><FormLabel>Address</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormField name="carrierInfo.physicalCity" render={({ field }) => (
                        <FormItem><FormLabel>City</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                    )} />
                    <FormField name="carrierInfo.physicalState" render={({ field }) => (
                        <FormItem><FormLabel>State</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                    )} />
                    <FormField name="carrierInfo.physicalZip" render={({ field }) => (
                        <FormItem><FormLabel>Zip Code</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                    )} />
                </div>
            </div>

            <Separator />
            <h3 className="text-lg font-medium">Mailing Address (if different)</h3>
             <div className="space-y-4">
                <FormField name="carrierInfo.mailingAddress" render={({ field }) => (
                    <FormItem><FormLabel>Address</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <FormField name="carrierInfo.mailingCity" render={({ field }) => (
                        <FormItem><FormLabel>City</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                    )} />
                    <FormField name="carrierInfo.mailingState" render={({ field }) => (
                        <FormItem><FormLabel>State</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                    )} />
                    <FormField name="carrierInfo.mailingZip" render={({ field }) => (
                        <FormItem><FormLabel>Zip Code</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                    )} />
                </div>
            </div>
            
            <Separator />
            <h3 className="text-lg font-medium">Contact Information</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                 <FormField name="carrierInfo.mainContact" render={({ field }) => (
                    <FormItem><FormLabel>Main Contact</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="carrierInfo.email" render={({ field }) => (
                    <FormItem><FormLabel>Email</FormLabel><FormControl><Input type="email" {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="carrierInfo.officePhone" render={({ field }) => (
                    <FormItem><FormLabel>Office Phone</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="carrierInfo.fax" render={({ field }) => (
                    <FormItem><FormLabel>Fax</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="carrierInfo.cellPhone" render={({ field }) => (
                    <FormItem><FormLabel>Cell Phone</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
            </div>
             <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                 <FormField name="carrierInfo.emergencyContact" render={({ field }) => (
                    <FormItem><FormLabel>Emergency Contact</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="carrierInfo.emergencyPhone" render={({ field }) => (
                    <FormItem><FormLabel>Emergency Phone</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
            </div>

            <Separator />
            <h3 className="text-lg font-medium">Authority & Certification</h3>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                 <FormField name="carrierInfo.mcNumber" render={({ field }) => (
                    <FormItem><FormLabel>MC #</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="carrierInfo.dotNumber" render={({ field }) => (
                    <FormItem><FormLabel>DOT #</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="carrierInfo.einNumber" render={({ field }) => (
                    <FormItem><FormLabel>EIN #</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="carrierInfo.scacCode" render={({ field }) => (
                    <FormItem><FormLabel>SCAC Code</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="carrierInfo.twicCertified" render={({ field }) => (
                    <FormItem><FormLabel>TWIC Certified</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
                 <FormField name="carrierInfo.hazmatCertified" render={({ field }) => (
                    <FormItem><FormLabel>Hazmat Certified</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>
                )} />
            </div>
        </div>
    );
}


export default function CarrierProfileForm() {
  const [currentStep, setCurrentStep] = useState(0);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [formCompleted, setFormCompleted] = useState(false);

  const methods = useForm<FullFormValues>({
    resolver: zodResolver(fullFormSchema),
    defaultValues: {
      carrierInfo: {},
    },
    mode: 'onChange',
  });

  const { handleSubmit, trigger, formState } = methods;

  const processForm = async (data: FullFormValues) => {
    setIsSubmitting(true);
    console.log('Form data:', data);
    // Here you would typically save the data to Firestore
    await new Promise(resolve => setTimeout(resolve, 2000));
    setIsSubmitting(false);
    setFormCompleted(true);
  };
  
  const nextStep = async () => {
    const fields = steps[currentStep].fields;
    const output = await trigger(fields as any, { shouldFocus: true });
    
    if (!output) return;
    
    if (currentStep < steps.length - 1) {
      setCurrentStep(step => step + 1);
    }
  };

  const prevStep = () => {
    if (currentStep > 0) {
      setCurrentStep(step => step + 1);
    }
  };

  if (isSubmitting) {
    return (
        <div className="flex flex-col items-center justify-center text-center gap-4 py-24">
            <LoaderCircle className="h-16 w-16 animate-spin text-primary" />
            <h2 className="text-2xl font-bold tracking-tighter">Submitting Your Profile</h2>
            <p className="text-lg text-muted-foreground">Please wait a moment...</p>
        </div>
    );
  }

  if (formCompleted) {
    return (
        <div className="flex flex-col items-center justify-center text-center gap-4 py-24">
            <Check className="h-16 w-16 text-green-500" />
            <h2 className="text-2xl font-bold tracking-tighter">Profile Submitted!</h2>
            <p className="text-lg text-muted-foreground">Thank you for completing your profile. We will review it shortly.</p>
        </div>
    );
  }

  return (
    <Card className="w-full">
        <CardHeader>
            <div className="flex items-start justify-center p-4">
              <ol className="flex items-center w-full max-w-2xl">
                {steps.map((step, index) => (
                  <li key={step.id} className={cn(
                      "relative flex w-full items-center",
                      index < steps.length - 1 ? "after:content-[''] after:w-full after:h-1 after:border-b after:border-4 after:inline-block" : "",
                      index <= currentStep ? "after:border-primary" : "after:border-muted",
                  )}>
                    <div className="flex flex-col items-center">
                        <div className={cn(
                            "flex items-center justify-center w-10 h-10 rounded-full shrink-0",
                            index <= currentStep ? "bg-primary text-primary-foreground" : "bg-muted text-muted-foreground"
                        )}>
                            {index < currentStep ? <Check className="w-6 h-6"/> : <span className="font-bold text-lg">{index + 1}</span>}
                        </div>
                        <p className={cn(
                            "text-xs text-center mt-2 w-20 md:w-auto",
                             index <= currentStep ? "font-bold text-primary" : "text-muted-foreground",
                             "hidden md:block"
                        )}>{step.title}</p>
                    </div>
                  </li>
                ))}
              </ol>
            </div>
            <Separator />
        </CardHeader>
      <CardContent className="p-4 md:p-6">
        <FormProvider {...methods}>
          <form onSubmit={handleSubmit(processForm)} className="space-y-8">
            {currentStep === 0 && <CarrierInfoForm />}
            {/* Other steps will be rendered here */}
            {currentStep === 1 && <div className="text-center p-8">Equipment Information Form (To be built)</div>}
            {currentStep === 2 && <div className="text-center p-8">Area of Operation Form (To be built)</div>}
            {currentStep === 3 && <div className="text-center p-8">Factoring Form (To be built)</div>}
            {currentStep === 4 && <div className="text-center p-8">Insurance Form (To be built)</div>}
          </form>
        </FormProvider>
      </CardContent>
      <div className="p-6 flex justify-between border-t">
        <Button type="button" variant="outline" onClick={prevStep} disabled={currentStep === 0}>
           <ArrowLeft className="mr-2 h-4 w-4"/> Previous
        </Button>
        {currentStep < steps.length - 1 ? (
          <Button type="button" onClick={nextStep}>
            Next <ArrowRight className="ml-2 h-4 w-4"/>
          </Button>
        ) : (
          <Button type="submit" onClick={handleSubmit(processForm)}>
            Submit Profile
          </Button>
        )}
      </div>
    </Card>
  );
}
