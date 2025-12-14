"use client";

import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { cn } from "@/lib/utils";

type Props = {
  formAction: string;
  consentText?: string | null;
  title?: string;
  subtitle?: string;
  className?: string;
};

export function NewsletterSection({
  formAction,
  consentText,
  title = "Get dispatch tips and product updates",
  subtitle = "No spam. Just practical playbooks for carriers and brokers.",
  className,
}: Props) {
  const [email, setEmail] = useState("");
  const [status, setStatus] = useState<"idle" | "loading" | "success" | "error">("idle");
  const [error, setError] = useState<string | null>(null);

  const onSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setStatus("loading");
    setError(null);
    try {
      const resp = await fetch(formAction, {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify({ email }),
      });
      if (!resp.ok) {
        const body = await resp.json().catch(() => null);
        throw new Error(body?.message || "Unable to subscribe right now.");
      }
      setStatus("success");
    } catch (err: any) {
      setError(err?.message || "Unable to subscribe right now.");
      setStatus("error");
    }
  };

  return (
    <section className={cn("w-full rounded-2xl border border-border/60 bg-card/60 p-6 md:p-8 shadow-sm", className)}>
      <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div className="space-y-2">
          <h3 className="text-xl font-semibold">{title}</h3>
          <p className="text-sm text-foreground/70">{subtitle}</p>
          {consentText && <p className="text-xs text-foreground/60">{consentText}</p>}
        </div>
        <form onSubmit={onSubmit} className="flex w-full flex-col gap-2 md:w-96 md:flex-row">
          <Input
            type="email"
            required
            placeholder="you@example.com"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            disabled={status === "loading" || status === "success"}
          />
          <Button type="submit" disabled={status === "loading" || status === "success"}>
            {status === "success" ? "Subscribed" : status === "loading" ? "Subscribing..." : "Subscribe"}
          </Button>
        </form>
      </div>
      {error && <p className="mt-2 text-xs text-red-500">{error}</p>}
      {status === "success" && <p className="mt-2 text-xs text-green-600">Thanks! Check your inbox.</p>}
    </section>
  );
}
