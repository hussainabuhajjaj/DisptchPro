"use client";

import * as React from "react";
import Link from "next/link";
import { ChevronDown, Mail, Menu, Phone, Truck } from "lucide-react";
import {
  Sheet,
  SheetContent,
  SheetTrigger,
  SheetClose,
  SheetTitle,
  SheetDescription,
} from "@/components/ui/sheet";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import { useEffect, useState } from "react";
import { fetchSiteSettings, SiteSettings } from "@/lib/site-settings";

type NavEntry = { href: string; label: string } | { label: string; items: { href: string; label: string }[] };

const mainNavLinks: NavEntry[] = [
  { href: "/#services", label: "Services" },
  { href: "/#pricing", label: "Pricing" },
  {
    label: "Who We Serve",
    items: [
      { href: "/#why-us", label: "For Carriers" },
      { href: "/#for-shippers", label: "For Shippers" },
      { href: "/#for-brokers", label: "For Brokers" },
    ],
  },
  {
    label: "About",
    items: [
      { href: "/#roadmap", label: "Roadmap" },
      { href: "/#testimonials", label: "Testimonials" },
    ],
  },
  { href: "/#faq", label: "FAQ" },
  { href: "/#lead-magnet", label: "Resources" },
  { href: "/#book", label: "Book a Call" },
];

const NavLink = ({
  href,
  label,
  className,
  active,
  onClick,
}: {
  href: string;
  label: string;
  className?: string;
  active?: boolean;
  onClick?: () => void;
}) => (
  <Link
    href={href}
    onClick={onClick}
    className={cn(
      "text-sm font-medium transition-colors",
      active ? "text-primary" : "text-foreground/80 hover:text-primary",
      className
    )}
  >
    {label}
  </Link>
);

const NavDropdown = ({
  label,
  items,
}: {
  label: string;
  items: { href: string; label: string }[];
}) => (
  <DropdownMenu>
    <DropdownMenuTrigger asChild>
      <Button
        variant="ghost"
        className="text-sm font-medium text-foreground/80 transition-colors hover:text-primary hover:bg-transparent p-0"
      >
        {label}
        <ChevronDown className="ml-1 h-4 w-4" />
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="start">
      {items.map((item) => (
        <DropdownMenuItem key={item.href} asChild>
          <Link href={item.href}>{item.label}</Link>
        </DropdownMenuItem>
      ))}
    </DropdownMenuContent>
  </DropdownMenu>
);

export default function Header() {
  const [isScrolled, setIsScrolled] = React.useState(false);
  const [settings, setSettings] = useState<SiteSettings>({});
  const [activeId, setActiveId] = useState<string | null>(null);

  React.useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 12);
    };
    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  useEffect(() => {
    fetchSiteSettings().then(setSettings).catch(() => {});
  }, []);

  useEffect(() => {
    const sectionIds = [
      "services",
      "pricing",
      "why-us",
      "for-shippers",
      "for-brokers",
      "kpis",
      "load-board",
      "faq",
      "lead-magnet",
      "book",
    ];
    const observers: IntersectionObserver[] = [];
    sectionIds.forEach((id) => {
      const el = document.getElementById(id);
      if (!el) return;
      const observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              setActiveId(id);
            }
          });
        },
        { rootMargin: "-40% 0px -40% 0px", threshold: [0, 0.3, 0.6, 1] },
      );
      observer.observe(el);
      observers.push(observer);
    });
    return () => observers.forEach((obs) => obs.disconnect());
  }, []);

  const brand = settings.site_name || "H&A Dispatch";
  const contactPhone = settings.contact_phone || "+1 (234) 567-890";
  const contactEmail = settings.contact_email || "contact@hadispatch.com";
  const supportText = settings.topbar_text || "24/7 Dispatch Support";

  return (
    <header className="sticky top-0 z-50 w-full">
      <div className="hidden md:block bg-[#0f3750] text-white">
        <div className="container mx-auto flex h-9 items-center justify-between px-4 md:px-6 text-sm">
          <span className="font-semibold tracking-tight">{supportText}</span>
          <div className="flex items-center gap-5">
            <a href={`tel:${contactPhone}`} className="flex items-center gap-1 hover:underline">
              <Phone className="h-4 w-4" />
              <span>{contactPhone}</span>
            </a>
            <a href={`mailto:${contactEmail}`} className="flex items-center gap-1 hover:underline">
              <Mail className="h-4 w-4" />
              <span>{contactEmail}</span>
            </a>
          </div>
        </div>
      </div>
      <div
        className={cn(
          "border-b border-transparent transition-all duration-300 bg-background/90 backdrop-blur-xl",
          isScrolled ? "border-border/80 shadow-sm" : ""
        )}
      >
        <div className="container mx-auto flex h-16 items-center justify-between px-4 md:px-6">
          <Link href="/" className="flex items-center gap-2">
            <Truck className="h-6 w-6 text-primary" />
            <span className="font-bold text-lg text-foreground">{brand}</span>
          </Link>

          <nav className="hidden items-center gap-6 md:flex">
            {mainNavLinks.map((link) =>
              "items" in link ? (
                <NavDropdown key={link.label} {...link} />
              ) : (
                <NavLink
                  key={link.href}
                  {...link}
                  active={activeId ? link.href.endsWith(`#${activeId}`) : false}
                />
              )
            )}
          </nav>

          <div className="hidden md:flex items-center gap-3">
            <Button asChild variant="outline" className="border-primary/40 text-foreground hover:text-primary">
              <Link href="/login">Carrier Login</Link>
            </Button>
            <Button asChild className="shadow-md">
              <Link href="/#book">Book a Call</Link>
            </Button>
          </div>

          <div className="md:hidden">
            <Sheet>
              <SheetTrigger asChild>
                <Button variant="ghost" size="icon">
                  <Menu className="h-6 w-6" />
                  <span className="sr-only">Toggle navigation menu</span>
                </Button>
              </SheetTrigger>
              <SheetContent side="right">
                  <SheetTitle className="sr-only">Menu</SheetTitle>
                  <SheetDescription className="sr-only">Main navigation menu for H&A Dispatch</SheetDescription>
                <div className="flex flex-col gap-6 p-6">
                  <Link href="/" className="flex items-center gap-2">
                     <Truck className="h-6 w-6 text-primary" />
                     <span className="font-bold">H&A Dispatch</span>
                  </Link>
                  <nav className="flex flex-col gap-4">
                    {mainNavLinks.map((link) => {
                      if ("items" in link) {
                        return (
                          <div key={link.label}>
                            <h3 className="text-lg font-semibold mb-2">{link.label}</h3>
                            <div className="flex flex-col gap-3 pl-2 border-l">
                              {link.items.map(item => (
                                 <SheetClose asChild key={item.href}>
                                    <NavLink {...item} className="text-base" />
                                 </SheetClose>
                              ))}
                            </div>
                          </div>
                        )
                      }
                      return (
                       <SheetClose asChild key={link.href}>
                          <NavLink {...link} className="text-lg" />
                       </SheetClose>
                      )
                    })}
                     <div className="border-t pt-4 flex flex-col gap-4">
                       <SheetClose asChild>
                         <Button asChild className="w-full">
                            <Link href="/#book">Book a Call</Link>
                          </Button>
                       </SheetClose>
                       <SheetClose asChild>
                         <Button asChild variant="outline" className="w-full border-primary/40 text-foreground">
                            <Link href="/login">Carrier Login</Link>
                         </Button>
                       </SheetClose>
                    </div>
                  </nav>
                </div>
              </SheetContent>
            </Sheet>
          </div>
        </div>
      </div>
    </header>
  );
}
