/* eslint-disable react-hooks/exhaustive-deps */
"use client";

import { useEffect, useState } from "react";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";

type Props = {
  message: string;
  ctaText?: string;
  policyUrl?: string;
  className?: string;
};

const STORAGE_KEY = "ha-dispatch-cookie-consent";

export function CookieBanner({ message, ctaText = "Accept", policyUrl, className }: Props) {
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    const stored = typeof window !== "undefined" ? localStorage.getItem(STORAGE_KEY) : null;
    if (!stored) {
      setVisible(true);
    }
  }, []);

  const accept = () => {
    if (typeof window !== "undefined") {
      localStorage.setItem(STORAGE_KEY, "accepted");
    }
    setVisible(false);
  };

  if (!visible) return null;

  return (
    <div
      className={cn(
        "fixed inset-x-0 bottom-0 z-50 border-t border-border/70 bg-background/95 backdrop-blur shadow-lg",
        className
      )}
    >
      <div className="mx-auto flex max-w-5xl items-center gap-4 px-4 py-3 text-sm">
        <span className="text-foreground/90">
          {message}{" "}
          {policyUrl && (
            <a href={policyUrl} className="underline underline-offset-4 text-primary">
              Learn more
            </a>
          )}
        </span>
        <div className="ml-auto">
          <Button size="sm" onClick={accept}>
            {ctaText}
          </Button>
        </div>
      </div>
    </div>
  );
}
