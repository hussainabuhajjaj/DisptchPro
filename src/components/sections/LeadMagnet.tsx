"use client";

import { useMemo, useState, useEffect } from "react";
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { CheckCircle, Download, Sparkles } from "lucide-react";
import { useToast } from "@/hooks/use-toast";
import { trackEvent } from "@/lib/analytics";
import { createBooking } from "@/lib/booking-service";

type Errors = Partial<Record<"name" | "email" | "role", string>>;

const leadMagnetHref = "/docs/route-optimization-checklist.pdf";

export default function LeadMagnet() {
  const { toast } = useToast();
  const [submitted, setSubmitted] = useState(false);
  const [errors, setErrors] = useState<Errors>({});
  const [role, setRole] = useState("Owner-Operator");
  const [email, setEmail] = useState("");
  const [name, setName] = useState("");
  const [notes, setNotes] = useState("");
  const [showSlideIn, setShowSlideIn] = useState(false);
  const [pending, setPending] = useState(false);

  const ctaCopy = useMemo(
    () => ({
      title: "Route Optimization Checklist",
      subtitle: "Cut deadhead, stack better loads, and keep your preferred lanes.",
      bullets: ["Deadhead-reduction playbook", "Preferred lanes coverage map", "Starter pricing scenarios"],
    }),
    []
  );

  useEffect(() => {
    const onScroll = () => {
      const scrolled = window.scrollY / (document.body.scrollHeight - window.innerHeight);
      if (scrolled > 0.45) setShowSlideIn(true);
    };
    const onExit = (e: MouseEvent) => {
      if (e.clientY < 60) setShowSlideIn(true);
    };
    window.addEventListener("scroll", onScroll, { passive: true });
    window.addEventListener("mouseleave", onExit);
    return () => {
      window.removeEventListener("scroll", onScroll);
      window.removeEventListener("mouseleave", onExit);
    };
  }, []);

  const validate = () => {
    const next: Errors = {};
    if (!name.trim()) next.name = "Tell us who to send it to.";
    if (!email.match(/^[\w-.]+@([\w-]+\.)+[\w-]{2,}$/)) next.email = "Enter a valid work email.";
    if (!role.trim()) next.role = "Select a role.";
    setErrors(next);
    return Object.keys(next).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    if (!validate()) return;
    try {
      setPending(true);
      const now = new Date().toISOString();
      await createBooking({
        title: `Lead Magnet: ${ctaCopy.title} - ${name}`,
        type: "demo",
        start_at: now,
        carrier_name: name,
        email,
        phone: undefined,
        notes: `Role: ${role}${notes ? ` | Notes: ${notes}` : ""}`,
      });
      setSubmitted(true);
      trackEvent("lead-magnet-submit", { role, notes: notes ? "provided" : "empty" });
      toast({ title: "Checklist unlocked", description: "Download link is ready below." });
    } catch (err: any) {
      const message = err?.data?.message || err?.message || "Could not save your request. Please try again.";
      toast({ title: "Error", description: message, variant: "destructive" });
      trackEvent("lead-magnet-submit-error");
    } finally {
      setPending(false);
    }
  };

  const DownloadButton = () => (
    <Button
      asChild
      className="w-full"
      data-umami-event="lead-magnet-download"
      variant="secondary"
    >
      <a href={leadMagnetHref} target="_blank" rel="noopener noreferrer">
        <Download className="h-4 w-4 mr-2" />
        Download the checklist
      </a>
    </Button>
  );

  return (
    <section id="lead-magnet" className="w-full py-16 md:py-24 bg-secondary/30">
      <div className="container mx-auto px-4 md:px-6">
        <div className="mx-auto max-w-4xl text-center mb-10 space-y-3">
          <div className="inline-flex items-center gap-2 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-primary">
            <Sparkles className="h-4 w-4" />
            Free download
          </div>
          <h2 className="text-3xl md:text-4xl font-bold tracking-tighter">{ctaCopy.title}</h2>
          <p className="text-lg text-muted-foreground">{ctaCopy.subtitle}</p>
          <div className="flex flex-wrap gap-2 justify-center text-sm text-foreground/80">
            {ctaCopy.bullets.map((item) => (
              <span key={item} className="rounded-full border border-primary/30 bg-white px-3 py-1 shadow-sm">
                {item}
              </span>
            ))}
          </div>
        </div>
        <Card className="max-w-5xl mx-auto shadow-lg">
          <div className="grid md:grid-cols-2">
            <div className="p-6 border-b md:border-b-0 md:border-r">
              <CardHeader className="p-0 space-y-3">
                <CardTitle>Get the checklist</CardTitle>
                <CardDescription>
                  Drop your details to unlock the PDF and get a follow-up with lane-specific advice.
                </CardDescription>
              </CardHeader>
              <CardContent className="p-0 mt-6">
                <form className="space-y-4" onSubmit={handleSubmit}>
                  <div className="space-y-2">
                    <Label htmlFor="lm-name">Name</Label>
                    <Input
                      id="lm-name"
                      name="name"
                      value={name}
                      onChange={(e) => setName(e.target.value)}
                      placeholder="Alex Carter"
                      aria-invalid={!!errors.name}
                    />
                    {errors.name && <p className="text-sm text-destructive">{errors.name}</p>}
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="lm-email">Work Email</Label>
                    <Input
                      id="lm-email"
                      name="email"
                      type="email"
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      placeholder="you@fleetco.com"
                      aria-invalid={!!errors.email}
                    />
                    {errors.email && <p className="text-sm text-destructive">{errors.email}</p>}
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="lm-role">Role</Label>
                    <Input
                      id="lm-role"
                      name="role"
                      value={role}
                      onChange={(e) => setRole(e.target.value)}
                      list="role-options"
                      aria-invalid={!!errors.role}
                    />
                    <datalist id="role-options">
                      <option>Owner-Operator</option>
                      <option>Small Fleet (2-10 trucks)</option>
                      <option>Broker</option>
                      <option>Shipper</option>
                    </datalist>
                    {errors.role && <p className="text-sm text-destructive">{errors.role}</p>}
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="lm-notes">Lanes or equipment (optional)</Label>
                    <Textarea
                      id="lm-notes"
                      name="notes"
                      value={notes}
                      onChange={(e) => setNotes(e.target.value)}
                      placeholder="e.g., TX → GA, reefer, avoid NYC"
                    />
                  </div>
                  <div className="text-sm text-muted-foreground">
                    No spam. We’ll only use this to send the PDF and one follow-up with tailored lanes.
                  </div>
                  <Button type="submit" className="w-full" data-umami-event="lead-magnet-submit" disabled={pending}>
                    {pending ? "Submitting..." : "Unlock the checklist"}
                  </Button>
                </form>
              </CardContent>
            </div>
            <div className="p-6 flex flex-col justify-center gap-4 bg-secondary/40">
              <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <CheckCircle className="h-4 w-4 text-primary" />
                Instant download—no waiting for emails.
              </div>
              <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <CheckCircle className="h-4 w-4 text-primary" />
                Includes pricing scenarios and RPM benchmarks.
              </div>
              <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <CheckCircle className="h-4 w-4 text-primary" />
                Optional: book a 15-min review of your lanes.
              </div>
              {submitted ? (
                <div className="space-y-3 rounded-xl border border-primary/30 bg-white p-4 shadow-sm">
                  <p className="font-semibold">Ready to download</p>
                  <DownloadButton />
                </div>
              ) : (
                <div className="rounded-xl border border-dashed border-border bg-white/80 p-4 text-sm text-muted-foreground">
                  Complete the form to unlock the PDF.
                </div>
              )}
            </div>
          </div>
        </Card>
      </div>

      {showSlideIn && !submitted && (
        <div className="fixed bottom-4 right-4 z-50 w-[320px] rounded-2xl border bg-card shadow-2xl">
          <div className="flex items-center justify-between p-3 border-b">
            <div className="text-sm font-semibold">Free checklist</div>
            <button
              className="text-xs text-muted-foreground hover:text-foreground"
              onClick={() => setShowSlideIn(false)}
            >
              Dismiss
            </button>
          </div>
          <div className="p-4 space-y-3">
            <p className="text-sm text-muted-foreground">
              Grab the route optimization checklist—reduce deadhead on your next dispatch cycle.
            </p>
            <Button
              className="w-full"
              variant="secondary"
              data-umami-event="lead-magnet-slidein"
              onClick={() => {
                setShowSlideIn(false);
                const target = document.getElementById("lead-magnet");
                if (target) target.scrollIntoView({ behavior: "smooth" });
              }}
            >
              Get the checklist
            </Button>
          </div>
        </div>
      )}
    </section>
  );
}
