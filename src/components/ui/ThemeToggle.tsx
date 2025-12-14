/* eslint-disable react-hooks/exhaustive-deps */
"use client";

import { useEffect, useState } from "react";
import { Sun, Moon, Laptop } from "lucide-react";
import { cn } from "@/lib/utils";
import { Button } from "@/components/ui/button";

type Mode = "light" | "dark" | "system";
const STORAGE_KEY = "ha-dispatch-theme";

function applyTheme(mode: Mode) {
  if (typeof document === "undefined") return;
  const root = document.documentElement;
  const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
  const resolved = mode === "system" ? (prefersDark ? "dark" : "light") : mode;
  if (resolved === "dark") {
    root.classList.add("dark");
  } else {
    root.classList.remove("dark");
  }
}

export function ThemeToggle({
  defaultMode = "system",
  allowToggle = true,
  className,
}: {
  defaultMode?: Mode;
  allowToggle?: boolean;
  className?: string;
}) {
  const [mode, setMode] = useState<Mode>(defaultMode);

  useEffect(() => {
    const stored = typeof window !== "undefined" ? (localStorage.getItem(STORAGE_KEY) as Mode | null) : null;
    const next = stored || defaultMode;
    setMode(next);
    applyTheme(next);
  }, [defaultMode]);

  useEffect(() => {
    if (!allowToggle) return;
    if (typeof window === "undefined") return;
    const handler = (e: MediaQueryListEvent) => {
      const stored = localStorage.getItem(STORAGE_KEY) as Mode | null;
      if (stored === "system" || !stored) {
        applyTheme("system");
      }
    };
    const mql = window.matchMedia("(prefers-color-scheme: dark)");
    mql.addEventListener("change", handler);
    return () => mql.removeEventListener("change", handler);
  }, [allowToggle]);

  if (!allowToggle) return null;

  const cycle = () => {
    const order: Mode[] = ["light", "dark", "system"];
    const idx = order.indexOf(mode);
    const next = order[(idx + 1) % order.length];
    setMode(next);
    if (typeof window !== "undefined") {
      localStorage.setItem(STORAGE_KEY, next);
    }
    applyTheme(next);
  };

  const icon = mode === "light" ? <Sun className="h-4 w-4" /> : mode === "dark" ? <Moon className="h-4 w-4" /> : <Laptop className="h-4 w-4" />;
  const label = mode === "light" ? "Light" : mode === "dark" ? "Dark" : "System";

  return (
    <Button variant="ghost" size="icon" aria-label="Toggle theme" onClick={cycle} className={cn("rounded-full", className)}>
      {icon}
      <span className="sr-only">Theme: {label}</span>
    </Button>
  );
}
