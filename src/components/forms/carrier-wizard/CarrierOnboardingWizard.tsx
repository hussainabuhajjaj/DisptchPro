'use client';

import { useCallback, useEffect, useMemo, useState } from "react";
import { UseFormReturn, useForm } from "react-hook-form";
import { z } from "zod";
import { zodResolver } from "@hookform/resolvers/zod";
import { CheckCircle2, Loader2, RefreshCw, UploadCloud } from "lucide-react";
import { useRouter } from "next/navigation";

import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { useToast } from "@/hooks/use-toast";
import { cn } from "@/lib/utils";
import {
  fetchCarrierDraft,
  saveCarrierDraft,
  submitCarrierApplication,
  submitCarrierApplicationWithConsent,
  uploadCarrierDocument,
  fetchDocumentStatuses,
} from "@/lib/carrier-service";
import {
  clearDraftToken,
  getDraftToken,
  storeDraftToken,
} from "@/lib/carrier-draft-storage";

/**
 * Local bypass: default to true so the wizard works without API connectivity.
 * Set NEXT_PUBLIC_ALLOW_ONBOARDING_LOCAL="false" to re-enable strict server flow.
 */
const ALLOW_LOCAL_BYPASS =
  process.env.NEXT_PUBLIC_ALLOW_ONBOARDING_LOCAL !== "false";

const formSchema = z.object({
  company: z.object({
    legalName: z.string().min(2, "Legal name is required."),
    mcNumber: z.string().optional(),
    dotNumber: z.string().optional(),
    phone: z.string().min(5, "Phone is required."),
    email: z.string().email("Provide a valid email."),
    city: z.string().min(2, "City is required."),
    state: z.string().min(2, "State is required."),
  }),
  contacts: z.object({
    opsContact: z.string().min(2, "Operations contact is required."),
    opsPhone: z.string().min(5, "Operations phone is required."),
    billingContact: z.string().optional(),
    billingEmail: z.string().email("Billing email must be valid.").optional(),
    billingPhone: z.string().optional(),
  }),
  operations: z.object({
    preferredLanes: z.string().min(2, "Preferred lanes are required."),
    equipmentType: z.string().min(2, "Equipment type is required."),
    trucks: z.string().min(1, "Please enter truck count."),
    dispatchExperience: z.string().optional(),
    hazmat: z.boolean().default(false),
    teamDriving: z.boolean().default(false),
  }),
  insurance: z.object({
    insuranceCarrier: z.string().min(2, "Insurance carrier is required."),
    insuranceExpiration: z.string().min(2, "Expiration date is required."),
    liabilityLimit: z.string().min(1, "Liability limit is required."),
    cargoLimit: z.string().min(1, "Cargo limit is required."),
  }),
  factoring: z.object({
    factoringCompany: z.string().optional(),
    factoringContact: z.string().optional(),
    factoringEmail: z.string().email("Must be a valid email").optional(),
  }),
  consent: z.object({
    signerName: z.string().min(2, "Name is required for consent."),
    signerTitle: z.string().min(2, "Title is required for consent."),
  }),
});


type OnboardingFormValues = z.infer<typeof formSchema>;

const DEFAULT_VALUES: OnboardingFormValues = {
  company: {
    legalName: "",
    mcNumber: "",
    dotNumber: "",
    phone: "",
    email: "",
    city: "",
    state: "",
  },
  contacts: {
    opsContact: "",
    opsPhone: "",
    billingContact: "",
    billingEmail: "",
    billingPhone: "",
  },
  operations: {
    preferredLanes: "",
    equipmentType: "",
    trucks: "",
    dispatchExperience: "",
    hazmat: false,
    teamDriving: false,
  },
  insurance: {
    insuranceCarrier: "",
    insuranceExpiration: "",
    liabilityLimit: "",
    cargoLimit: "",
  },
  factoring: {
    factoringCompany: "",
    factoringContact: "",
    factoringEmail: "",
  },
  consent: {
    signerName: "",
    signerTitle: "",
  },
};

type StepId =
  | "company"
  | "contacts"
  | "operations"
  | "insurance"
  | "factoring"
  | "documents"
  | "consent"
  | "summary";

interface WizardStep {
  id: StepId;
  title: string;
  description: string;
  render: () => React.ReactNode;
  nextLabel?: string;
}

type DocumentKey = "w9" | "coi" | "insurance" | "factoringNoa";

type DocumentStatus = {
  status: "missing" | "uploading" | "pending" | "approved" | "rejected";
  reviewerNote?: string | null;
  fileName?: string;
  updatedAt?: string;
};

type DocumentsState = Record<DocumentKey, DocumentStatus>;

const DEFAULT_DOCUMENT_STATE: DocumentsState = {
  w9: { status: "missing" },
  coi: { status: "missing" },
  insurance: { status: "missing" },
  factoringNoa: { status: "missing" },
};

const STEP_ORDER: StepId[] = [
  "company",
  "contacts",
  "operations",
  "insurance",
  "factoring",
  "documents",
  "consent",
  "summary",
];

export default function CarrierOnboardingWizard() {
  const form = useForm<OnboardingFormValues>({
    resolver: zodResolver(formSchema),
    defaultValues: DEFAULT_VALUES,
    mode: "onChange",
  });

  const { toast } = useToast();
  const router = useRouter();
  const [currentStepIndex, setCurrentStepIndex] = useState(0);
  const [documents, setDocuments] = useState<DocumentsState>(DEFAULT_DOCUMENT_STATE);
  const [draftId, setDraftId] = useState<string | null>(null);
  const [isSaving, setIsSaving] = useState(false);
  const [lastSavedAt, setLastSavedAt] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [initializing, setInitializing] = useState(true);
  const [isPollingDocs, setIsPollingDocs] = useState(false);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [manualSaving, setManualSaving] = useState(false);

  const handleDocumentUpload = useCallback(
    async (key: DocumentKey, file: File) => {
      if (!draftId) {
        if (ALLOW_LOCAL_BYPASS) {
          setDocuments((prev) => ({
            ...prev,
            [key]: { status: "approved", fileName: file.name },
          }));
          toast({
            title: "Local test mode",
            description: "Marked as uploaded (no draft yet).",
          });
          return;
        } else {
          toast({
            variant: "destructive",
            title: "Save company info first",
            description:
              "Please complete earlier steps so we can attach documents to your draft.",
          });
          return;
        }
      }

      setDocuments((prev) => ({
        ...prev,
        [key]: { status: "uploading", fileName: file.name },
      }));

      try {
        const response = await uploadCarrierDocument(draftId, key, file);
        setDocuments((prev) => ({
          ...prev,
          [key]: {
            status: ALLOW_LOCAL_BYPASS ? "approved" : response.status,
            reviewerNote: response.reviewerNote,
            fileName: file.name,
          },
        }));
        if (!ALLOW_LOCAL_BYPASS) {
          await pollDocuments(draftId);
        }
        toast({
          title: "Document uploaded",
          description: `${key.toUpperCase()} is ${ALLOW_LOCAL_BYPASS ? "approved" : response.status}.`,
        });
      } catch (error) {
        console.error(error);
        setDocuments((prev) => ({
          ...prev,
          [key]: { status: "missing" },
        }));
        toast({
          variant: "destructive",
          title: "Upload failed",
          description:
            (error as any)?.message ??
            "Please try uploading the document again. Make sure it is a PDF/JPG/PNG under 5MB.",
        });
      }
    },
    [draftId, toast],
  );

  const steps = useMemo(
    () =>
      buildWizardSteps({
        form,
        documents,
        onUpload: handleDocumentUpload,
        jumpToStep: (stepId) => {
          const targetIndex = STEP_ORDER.indexOf(stepId);
          if (targetIndex >= 0) {
            setCurrentStepIndex(targetIndex);
          }
        },
      }),
    [form, documents, handleDocumentUpload],
  );
  const currentStep = steps[currentStepIndex];

  useEffect(() => {
    const token = getDraftToken();
    if (!token) {
      setInitializing(false);
      return;
    }

    (async () => {
      try {
        const draft = await fetchCarrierDraft(token);
        setDraftId(draft.draftId);
        storeDraftToken(draft.draftId);

        if (draft.data?.formValues) {
          form.reset(draft.data.formValues as OnboardingFormValues);
        }

        if (draft.data?.documents) {
          setDocuments({
            ...DEFAULT_DOCUMENT_STATE,
            ...(draft.data.documents as DocumentsState),
          });
        }
        setLastSavedAt(draft.updatedAt);
      } catch (error) {
        clearDraftToken();
        console.error("Failed to resume draft", error);
        setLoadError("We couldn't resume your draft. Start a new application to continue.");
      } finally {
        setInitializing(false);
      }
    })();
  }, [form]);

  const watchedValues = form.watch();

  const pollDocuments = useCallback(
    async (targetDraftId: string) => {
      setIsPollingDocs(true);
      try {
        const latest = await fetchDocumentStatuses(targetDraftId);
        setDocuments((prev) => ({
          ...DEFAULT_DOCUMENT_STATE,
          ...prev,
          ...latest.documents,
        }));
      } catch (error) {
        console.error("Document status refresh failed", error);
      } finally {
        setIsPollingDocs(false);
      }
    },
    [],
  );

  useEffect(() => {
    if (!draftId) return;
    const interval = setInterval(() => {
      pollDocuments(draftId);
    }, 15000);
    return () => clearInterval(interval);
  }, [draftId, pollDocuments]);

  useEffect(() => {
    if (initializing) return;
    const controller = new AbortController();
    const timer = setTimeout(async () => {
      setIsSaving(true);
      try {
        const response = await saveCarrierDraft({
          draftId: draftId ?? undefined,
          data: {
            formValues: watchedValues,
            documents,
          },
        });
        setDraftId(response.draftId);
        storeDraftToken(response.draftId);
        setLastSavedAt(response.updatedAt);
      } catch (error) {
        console.error("Autosave failed", error);
      } finally {
        if (!controller.signal.aborted) {
          setIsSaving(false);
        }
      }
    }, 1200);

    return () => {
      controller.abort();
      clearTimeout(timer);
    };
  }, [watchedValues, documents, draftId, initializing]);

  async function handleNext() {
    const stepId = steps[currentStepIndex]?.id;
    const stepFields = stepId ? stepValidationMap[stepId] : undefined;

    if (stepFields && stepFields.length > 0) {
      const valid = await form.trigger(stepFields as any, { shouldFocus: true });
      if (!valid) {
        toast({
          variant: "destructive",
          title: "Please complete required fields",
          description: "Fill the highlighted fields before continuing.",
        });
        return;
      }
    }

    if (currentStepIndex < STEP_ORDER.length - 1) {
      setCurrentStepIndex((prev) => prev + 1);
    }
  }

  function handleBack() {
    if (currentStepIndex > 0) {
      setCurrentStepIndex((prev) => prev - 1);
    }
  }

  async function handleSubmit() {
    if (!ALLOW_LOCAL_BYPASS) {
      const valid = await form.trigger(undefined, { shouldFocus: true });
      if (!valid) {
        toast({
          variant: "destructive",
          title: "Missing information",
          description: "Please fill all required fields before submitting.",
        });
        return;
      }
    }

    if (!draftId && !ALLOW_LOCAL_BYPASS) {
      toast({
        variant: "destructive",
        title: "Draft not ready",
        description: "Please wait for autosave to finish and try again.",
      });
      return;
    }

    if (!canSubmit(documents, form.getValues())) {
      toast({
        variant: "destructive",
        title: "Upload required documents",
        description: "Please upload W-9 and insurance documents before submitting.",
      });
      return;
    }

    setIsSubmitting(true);
    try {
      if (ALLOW_LOCAL_BYPASS && !draftId) {
        router.push("/start-with-us/confirmation");
        toast({
          title: "Submitted (local test mode)",
          description: "No API call made.",
        });
      } else {
        await submitCarrierApplicationWithConsent(draftId!, {
          consent: {
            signerName: form.getValues().consent.signerName,
            signerTitle: form.getValues().consent.signerTitle,
            signedAt: new Date().toISOString(),
          },
        });
        clearDraftToken();
        router.push("/start-with-us/confirmation");
        toast({
          title: "Application submitted",
          description: "Our onboarding team will follow up shortly.",
        });
      }
    } catch (error) {
      console.error(error);
      toast({
        variant: "destructive",
        title: "Submission failed",
        description: "Please try again or contact support.",
      });
    } finally {
      setIsSubmitting(false);
    }
  }

  if (initializing) {
    return (
      <div className="flex flex-col items-center justify-center py-20">
        <Loader2 className="h-8 w-8 animate-spin text-primary" />
        <p className="mt-4 text-muted-foreground">Loading your saved progress...</p>
      </div>
    );
  }

  if (loadError) {
    return (
      <div className="space-y-4 rounded-lg border bg-card p-6">
        <h2 className="text-xl font-semibold">Start a new application</h2>
        <p className="text-muted-foreground">{loadError}</p>
        <Button
          onClick={() => {
            clearDraftToken();
            setDocuments(DEFAULT_DOCUMENT_STATE);
            form.reset(DEFAULT_VALUES);
            setLoadError(null);
          }}
        >
          Start over
        </Button>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <Card>
        <CardHeader className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <CardTitle>Carrier onboarding</CardTitle>
          <div className="flex flex-col gap-1 text-sm text-muted-foreground sm:items-end">
            <div>
              {isSaving ? (
                <span className="flex items-center gap-2">
                  <Loader2 className="h-4 w-4 animate-spin" /> Saving draft...
                </span>
              ) : lastSavedAt ? (
                <>Last saved {new Date(lastSavedAt).toLocaleTimeString()}</>
              ) : (
                "Draft not saved yet"
              )}
            </div>
            <div className="flex items-center gap-2">
              <Button
                size="sm"
                variant="outline"
                disabled={manualSaving}
                onClick={async () => {
                  setManualSaving(true);
                  try {
                    const response = await saveCarrierDraft({
                      draftId: draftId ?? undefined,
                      data: { formValues: form.getValues(), documents },
                    });
                    setDraftId(response.draftId);
                    storeDraftToken(response.draftId);
                    setLastSavedAt(response.updatedAt);
                    toast({ title: "Draft saved" });
                  } catch (error) {
                    if (ALLOW_LOCAL_BYPASS) {
                      setDraftId("local-draft");
                      toast({
                        title: "Local save",
                        description: "Draft saved locally (no API).",
                      });
                    } else {
                      toast({
                        variant: "destructive",
                        title: "Save failed",
                        description: "Check API connectivity and try again.",
                      });
                    }
                  } finally {
                    setManualSaving(false);
                  }
                }}
              >
                Save draft
              </Button>
            </div>
            {ALLOW_LOCAL_BYPASS && (
              <span className="text-xs text-amber-600">
                Local test mode: submit/uploads can bypass API.
              </span>
            )}
          </div>
        </CardHeader>
        <CardContent>
          <StepIndicator steps={steps} currentStep={currentStepIndex} />
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>{currentStep.title}</CardTitle>
          <p className="text-sm text-muted-foreground">{currentStep.description}</p>
        </CardHeader>
        <CardContent>{currentStep.render()}</CardContent>
      </Card>

      <div className="flex flex-col gap-3 sm:flex-row sm:justify-between">
        <Button variant="outline" disabled={currentStepIndex === 0} onClick={handleBack}>
          Back
        </Button>
        {currentStep.id === "summary" ? (
          <Button
            onClick={handleSubmit}
            disabled={isSubmitting || !canSubmit(documents, form.getValues())}
          >
            {isSubmitting ? (
              <span className="flex items-center gap-2">
                <Loader2 className="h-4 w-4 animate-spin" /> Submitting
              </span>
            ) : (
              "Submit application"
            )}
          </Button>
        ) : (
          <Button onClick={handleNext}>
            {currentStep.nextLabel ?? "Continue"}
          </Button>
        )}
      </div>
    </div>
  );
}

function StepIndicator({ steps, currentStep }: { steps: WizardStep[]; currentStep: number }) {
  return (
    <div className="flex flex-col gap-4 sm:flex-row">
      {steps.map((step, index) => (
        <div key={step.id} className="flex items-center">
          <div
            className={cn(
              "flex h-10 w-10 items-center justify-center rounded-full border text-sm font-semibold",
              index === currentStep && "border-primary text-primary",
              index < currentStep && "border-primary bg-primary text-primary-foreground",
              index > currentStep && "border-muted text-muted-foreground",
            )}
          >
            {index < currentStep ? <CheckCircle2 className="h-5 w-5" /> : index + 1}
          </div>
          {index < steps.length - 1 && (
            <div className="mx-2 hidden h-px flex-1 bg-border sm:block" />
          )}
        </div>
      ))}
    </div>
  );
}

function CompanyStep({ form }: { form: UseFormReturn<OnboardingFormValues> }) {
  return (
    <Form {...form}>
      <div className="grid gap-4 sm:grid-cols-2">
        <FormField
          control={form.control}
          name="company.legalName"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Legal company name</FormLabel>
              <FormControl>
                <Input placeholder="H&A Logistics LLC" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="company.phone"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Primary phone</FormLabel>
              <FormControl>
                <Input placeholder="(555) 123-4567" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="company.email"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Contact email</FormLabel>
              <FormControl>
                <Input type="email" placeholder="ops@company.com" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="company.mcNumber"
          render={({ field }) => (
            <FormItem>
              <FormLabel>MC number</FormLabel>
              <FormControl>
                <Input placeholder="123456" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="company.dotNumber"
          render={({ field }) => (
            <FormItem>
              <FormLabel>DOT number</FormLabel>
              <FormControl>
                <Input placeholder="9876543" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <div className="grid gap-4 sm:grid-cols-2 sm:col-span-2">
          <FormField
            control={form.control}
            name="company.city"
            render={({ field }) => (
              <FormItem>
                <FormLabel>City</FormLabel>
                <FormControl>
                  <Input placeholder="Dallas" {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />
          <FormField
            control={form.control}
            name="company.state"
            render={({ field }) => (
              <FormItem>
                <FormLabel>State</FormLabel>
                <FormControl>
                  <Input placeholder="TX" {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />
        </div>
      </div>
    </Form>
  );
}

function ContactsStep({ form }: { form: UseFormReturn<OnboardingFormValues> }) {
  return (
    <Form {...form}>
      <div className="grid gap-4 sm:grid-cols-2">
        <FormField
          control={form.control}
          name="contacts.opsContact"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Operations contact</FormLabel>
              <FormControl>
                <Input placeholder="Jane Dispatcher" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="contacts.opsPhone"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Operations phone</FormLabel>
              <FormControl>
                <Input placeholder="(555) 111-2222" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="contacts.billingContact"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Billing contact</FormLabel>
              <FormControl>
                <Input placeholder="AP Contact (optional)" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="contacts.billingEmail"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Billing email</FormLabel>
              <FormControl>
                <Input type="email" placeholder="billing@company.com" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="contacts.billingPhone"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Billing phone</FormLabel>
              <FormControl>
                <Input placeholder="(555) 333-4444" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
      </div>
    </Form>
  );
}

function OperationsStep({ form }: { form: UseFormReturn<OnboardingFormValues> }) {
  return (
    <Form {...form}>
      <div className="grid gap-4">
        <FormField
          control={form.control}
          name="operations.equipmentType"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Primary equipment</FormLabel>
              <FormControl>
                <Input placeholder="53' Dry Van" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="operations.trucks"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Active trucks</FormLabel>
              <FormControl>
                <Input placeholder="5" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="operations.preferredLanes"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Preferred lanes</FormLabel>
              <FormControl>
                <Textarea placeholder="TX ➜ CA, Southeast regional, etc." {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="operations.dispatchExperience"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Anything else we should know?</FormLabel>
              <FormControl>
                <Textarea placeholder="Team only, avoids NYC, need hazmat loads..." {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <div className="grid gap-4 sm:grid-cols-2">
          <FormField
            control={form.control}
            name="operations.hazmat"
            render={({ field }) => (
              <FormItem className="flex items-center gap-2">
                <FormControl>
                  <input
                    type="checkbox"
                    className="h-4 w-4"
                    checked={field.value}
                    onChange={(e) => field.onChange(e.target.checked)}
                  />
                </FormControl>
                <FormLabel>Hazmat certified</FormLabel>
              </FormItem>
            )}
          />
          <FormField
            control={form.control}
            name="operations.teamDriving"
            render={({ field }) => (
              <FormItem className="flex items-center gap-2">
                <FormControl>
                  <input
                    type="checkbox"
                    className="h-4 w-4"
                    checked={field.value}
                    onChange={(e) => field.onChange(e.target.checked)}
                  />
                </FormControl>
                <FormLabel>Team driving available</FormLabel>
              </FormItem>
            )}
          />
        </div>
      </div>
    </Form>
  );
}

function InsuranceStep({ form }: { form: UseFormReturn<OnboardingFormValues> }) {
  return (
    <Form {...form}>
      <div className="grid gap-4 sm:grid-cols-2">
        <FormField
          control={form.control}
          name="insurance.insuranceCarrier"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Insurance carrier</FormLabel>
              <FormControl>
                <Input placeholder="ABC Insurance" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="insurance.insuranceExpiration"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Policy expiration</FormLabel>
              <FormControl>
                <Input placeholder="2025-12-31" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="insurance.liabilityLimit"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Auto liability limit</FormLabel>
              <FormControl>
                <Input placeholder="$1,000,000" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="insurance.cargoLimit"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Cargo limit</FormLabel>
              <FormControl>
                <Input placeholder="$100,000" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
      </div>
    </Form>
  );
}

function FactoringStep({ form }: { form: UseFormReturn<OnboardingFormValues> }) {
  return (
    <Form {...form}>
      <div className="grid gap-4 sm:grid-cols-2">
        <FormField
          control={form.control}
          name="factoring.factoringCompany"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Factoring company</FormLabel>
              <FormControl>
                <Input placeholder="XYZ Factoring (optional)" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="factoring.factoringContact"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Factoring contact</FormLabel>
              <FormControl>
                <Input placeholder="Rep name (optional)" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="factoring.factoringEmail"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Factoring email</FormLabel>
              <FormControl>
                <Input type="email" placeholder="rep@factoring.com" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
      </div>
    </Form>
  );
}

function DocumentUploadStep({
  documents,
  onUpload,
}: {
  documents: DocumentsState;
  onUpload: (key: DocumentKey, file: File) => void;
}) {
  const documentDefinitions: {
    key: DocumentKey;
    label: string;
    description: string;
    required?: boolean;
  }[] = [
    { key: "w9", label: "W-9", description: "Most recent signed copy.", required: true },
    { key: "coi", label: "Certificate of Insurance", description: "Show minimum $1M liability.", required: true },
    { key: "insurance", label: "Cargo/insurance binder", description: "Detail coverage and expiration.", required: true },
    { key: "factoringNoa", label: "Factoring NOA", description: "Assignment notice from factoring (if applicable).", required: false },
  ];

  return (
    <div className="space-y-6">
      {documentDefinitions.map(({ key, label, description, required }) => {
        const doc = documents[key];
        return (
          <div key={key} className="rounded-lg border p-4">
            <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <p className="font-medium">
                  {label}{" "}
                  <span className="text-xs text-muted-foreground">
                    {required ? "(required)" : "(optional)"}
                  </span>
                </p>
                <p className="text-sm text-muted-foreground">{description}</p>
              </div>
              <Badge
                variant={
                  doc.status === "approved"
                    ? "default"
                    : doc.status === "rejected"
                      ? "destructive"
                      : "secondary"
                }
              >
                {doc.status === "missing" && "Missing"}
                {doc.status === "uploading" && "Uploading"}
                {doc.status === "pending" && "Pending review"}
                {doc.status === "approved" && "Approved"}
                {doc.status === "rejected" && "Needs attention"}
              </Badge>
            </div>
            <div className="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center">
              <label className="flex cursor-pointer items-center gap-2 rounded-md border px-4 py-2 text-sm font-medium hover:bg-muted">
                <UploadCloud className="h-4 w-4" />
                Upload file
                <input
                  type="file"
                  className="sr-only"
                  onChange={(event) => {
                    if (event.target.files?.[0]) {
                      onUpload(key, event.target.files[0]);
                    }
                  }}
                />
              </label>
              {doc.fileName && <p className="text-sm text-muted-foreground">{doc.fileName}</p>}
              {doc.status === "pending" && (
                <div className="flex items-center gap-2 text-xs text-muted-foreground">
                  <RefreshCw className="h-3 w-3 animate-spin" /> Waiting for reviewer
                </div>
              )}
              {doc.status === "rejected" && doc.reviewerNote && (
                <p className="text-xs text-destructive">{doc.reviewerNote}</p>
              )}
            </div>
            <p className="mt-2 text-xs text-muted-foreground">
              Accepted: PDF, JPG, PNG. Max 5MB. If rejected, re-upload and address the note.
            </p>
            {doc.reviewerNote && (
              <p className="mt-2 rounded-md bg-muted p-2 text-sm">
                Reviewer note: {doc.reviewerNote}
              </p>
            )}
          </div>
        );
      })}
    </div>
  );
}

function ConsentStep({ form }: { form: UseFormReturn<OnboardingFormValues> }) {
  return (
    <Form {...form}>
      <div className="grid gap-4 sm:grid-cols-2">
        <FormField
          control={form.control}
          name="consent.signerName"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Signer name</FormLabel>
              <FormControl>
                <Input placeholder="John Doe" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
        <FormField
          control={form.control}
          name="consent.signerTitle"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Signer title</FormLabel>
              <FormControl>
                <Input placeholder="Owner / Safety Manager" {...field} />
              </FormControl>
              <FormMessage />
            </FormItem>
          )}
        />
      </div>
      <p className="mt-4 text-sm text-muted-foreground">
        By submitting, you certify that the information provided is accurate and authorize DispatchPro
        to use this data for onboarding, compliance, and load tendering.
      </p>
    </Form>
  );
}

function SummaryStep({
  values,
  documents,
  onEdit,
}: {
  values: OnboardingFormValues;
  documents: DocumentsState;
  onEdit: (step: StepId) => void;
}) {
  return (
    <div className="space-y-6">
      <SummaryCard
        title="Company"
        onEdit={() => onEdit("company")}
        items={[
          { label: "Legal name", value: values.company.legalName },
          { label: "Phone", value: values.company.phone },
          { label: "Email", value: values.company.email },
          { label: "MC", value: values.company.mcNumber },
          { label: "DOT", value: values.company.dotNumber },
          {
            label: "Location",
            value: `${values.company.city}, ${values.company.state}`.trim(),
          },
        ]}
      />
      <SummaryCard
        title="Contacts"
        onEdit={() => onEdit("contacts")}
        items={[
          { label: "Ops contact", value: values.contacts.opsContact },
          { label: "Ops phone", value: values.contacts.opsPhone },
          { label: "Billing contact", value: values.contacts.billingContact },
          { label: "Billing email", value: values.contacts.billingEmail },
          { label: "Billing phone", value: values.contacts.billingPhone },
        ]}
      />
      <SummaryCard
        title="Operations"
        onEdit={() => onEdit("operations")}
        items={[
          { label: "Equipment", value: values.operations.equipmentType },
          { label: "Trucks", value: values.operations.trucks },
          { label: "Preferred lanes", value: values.operations.preferredLanes },
          { label: "Notes", value: values.operations.dispatchExperience },
          { label: "Hazmat", value: values.operations.hazmat ? "Yes" : "No" },
          { label: "Team driving", value: values.operations.teamDriving ? "Yes" : "No" },
        ]}
      />
      <SummaryCard
        title="Insurance"
        onEdit={() => onEdit("insurance")}
        items={[
          { label: "Carrier", value: values.insurance.insuranceCarrier },
          { label: "Expiration", value: values.insurance.insuranceExpiration },
          { label: "Liability limit", value: values.insurance.liabilityLimit },
          { label: "Cargo limit", value: values.insurance.cargoLimit },
        ]}
      />
      <SummaryCard
        title="Factoring"
        onEdit={() => onEdit("factoring")}
        items={[
          { label: "Company", value: values.factoring.factoringCompany },
          { label: "Contact", value: values.factoring.factoringContact },
          { label: "Email", value: values.factoring.factoringEmail },
        ]}
      />
      <SummaryCard
        title="Documents"
        onEdit={() => onEdit("documents")}
        items={Object.entries(documents).map(([key, doc]) => ({
          label: key.toUpperCase(),
          value: doc.status === "missing" ? "Missing" : doc.status,
        }))}
      />
      <SummaryCard
        title="Consent"
        onEdit={() => onEdit("consent")}
        items={[
          { label: "Signer name", value: values.consent.signerName },
          { label: "Signer title", value: values.consent.signerTitle },
        ]}
      />
    </div>
  );
}

function SummaryCard({
  title,
  items,
  onEdit,
}: {
  title: string;
  items: { label: string; value?: string | null }[];
  onEdit: () => void;
}) {
  return (
    <div className="rounded-lg border">
      <div className="flex items-center justify-between border-b px-4 py-3">
        <p className="font-semibold">{title}</p>
        <Button variant="ghost" size="sm" onClick={onEdit}>
          Edit
        </Button>
      </div>
      <div className="divide-y">
        {items.map((item) => (
          <div key={item.label} className="flex items-center justify-between px-4 py-3 text-sm">
            <span className="text-muted-foreground">{item.label}</span>
            <span className="font-medium">{item.value || "—"}</span>
          </div>
        ))}
      </div>
    </div>
  );
}

function buildWizardSteps({
  form,
  documents,
  onUpload,
  jumpToStep,
}: {
  form: UseFormReturn<OnboardingFormValues>;
  documents: DocumentsState;
  onUpload: (key: DocumentKey, file: File) => void;
  jumpToStep: (step: StepId) => void;
}): WizardStep[] {
  return [
    {
      id: "company",
      title: "Company details",
      description: "Tell us about your business so we can personalize dispatch support.",
      render: () => <CompanyStep form={form} />,
    },
    {
      id: "contacts",
      title: "Contacts",
      description: "Who should we call for operations and billing?",
      render: () => <ContactsStep form={form} />,
    },
    {
      id: "operations",
      title: "Operations & lanes",
      description: "Help us understand your equipment mix and preferred routes.",
      render: () => <OperationsStep form={form} />,
    },
    {
      id: "insurance",
      title: "Insurance",
      description: "Share coverage details so we can book compliant loads.",
      render: () => <InsuranceStep form={form} />,
    },
    {
      id: "factoring",
      title: "Factoring & settlements",
      description: "Add factoring details so we route tenders and payments correctly.",
      render: () => <FactoringStep form={form} />,
    },
    {
      id: "documents",
      title: "Compliance documents",
      description: "Upload paperwork. We’ll review and notify you if anything needs attention.",
      render: () => <DocumentUploadStep documents={documents} onUpload={onUpload} />,
    },
    {
      id: "consent",
      title: "Consent & signature",
      description: "Confirm your information and authorize DispatchPro to dispatch on your behalf.",
      render: () => <ConsentStep form={form} />,
    },
    {
      id: "summary",
      title: "Review & submit",
      description: "Confirm everything looks correct before submitting to DispatchPro.",
      render: () => (
        <SummaryStep
          values={form.getValues()}
          documents={documents}
          onEdit={jumpToStep}
        />
      ),
      nextLabel: "Submit application",
    },
  ];
}

const stepValidationMap: Record<StepId, (keyof OnboardingFormValues | string)[]> = {
  company: [
    "company.legalName",
    "company.phone",
    "company.email",
    "company.city",
    "company.state",
  ],
  contacts: ["contacts.opsContact", "contacts.opsPhone"],
  operations: [
    "operations.equipmentType",
    "operations.trucks",
    "operations.preferredLanes",
  ],
  insurance: [
    "insurance.insuranceCarrier",
    "insurance.insuranceExpiration",
    "insurance.liabilityLimit",
    "insurance.cargoLimit",
  ],
  factoring: [],
  documents: [],
  consent: ["consent.signerName", "consent.signerTitle"],
  summary: [],
};

function canSubmit(documents: DocumentsState, values: OnboardingFormValues) {
  if (ALLOW_LOCAL_BYPASS) return true;
  const requiredDocs: DocumentKey[] = ["w9", "coi", "insurance"];
  const docsReady =
    requiredDocs.every((doc) => {
      const status = documents[doc]?.status;
      return status === "approved" || status === "pending";
    }) &&
    Object.values(documents).every((doc) => doc.status !== "rejected");
  const consentReady = !!values.consent.signerName && !!values.consent.signerTitle;
  return docsReady && consentReady;
}
