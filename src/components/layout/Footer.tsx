 "use client";

import Link from "next/link";
import { Mail, Phone, MessageCircle, Truck, FileCode } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useEffect, useMemo, useState } from "react";
import { fetchSiteSettings, SiteSettings } from "@/lib/site-settings";
import { ShieldCheck, FileBadge } from "lucide-react";

const fallbackContact = [
  {
    Icon: MessageCircle,
    label: "WhatsApp",
    href: "https://wa.me/1234567890",
    value: "+1 (234) 567-890",
  },
  {
    Icon: Phone,
    label: "Phone",
    href: "tel:+1234567890",
    value: "+1 (234) 567-890",
  },
  {
    Icon: Mail,
    label: "Email",
    href: "mailto:contact@hadispatch.com",
    value: "contact@hadispatch.com",
  },
];

export default function Footer() {
  const [settings, setSettings] = useState<SiteSettings>({});

  useEffect(() => {
    fetchSiteSettings().then(setSettings).catch(() => {});
  }, []);

  const contactMethods = useMemo(() => {
    const phone = settings.contact_phone;
    const email = settings.contact_email;

    const methods = [];
    if (phone) {
      methods.push({
        Icon: Phone,
        label: "Phone",
        href: `tel:${phone}`,
        value: phone,
      });
    }
    if (email) {
      methods.push({
        Icon: Mail,
        label: "Email",
        href: `mailto:${email}`,
        value: email,
      });
    }
    return methods.length ? methods : fallbackContact;
  }, [settings]);

  const brand = settings.site_name || "H&A Dispatch";
  const footerText =
    settings.footer_text ||
    `© ${new Date().getFullYear()} ${brand}. All Rights Reserved.`;
  const footerLinks = settings.links ?? [];

  const compliance = [
    { label: "Broker/Carrier Packet Ready", Icon: FileBadge },
    { label: "COI Provided On Request", Icon: ShieldCheck },
    { label: "W-9 Available", Icon: FileBadge },
    { label: "Carrier Agreement Ready to Sign", Icon: ShieldCheck },
  ];

  return (
    <footer className="bg-secondary/50">
      <div className="border-b bg-background/80">
        <div className="container mx-auto px-4 py-3 md:px-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          {compliance.map(({ label, Icon }) => (
            <div key={label} className="flex items-center gap-2 text-sm text-muted-foreground">
              <Icon className="h-4 w-4 text-primary" />
              <span>{label}</span>
            </div>
          ))}
        </div>
      </div>
      <div className="container mx-auto px-4 py-12 md:px-6">
        <div className="grid gap-12 md:grid-cols-3">
          <div className="flex flex-col gap-4">
            <Link href="/" className="flex items-center gap-2">
              <Truck className="h-7 w-7 text-primary" />
              <span className="text-xl font-bold text-foreground">
                {brand}
              </span>
            </Link>
            <p className="text-muted-foreground text-sm">
              {settings.site_description ||
                "Optimizing logistics for owner-operators and small fleets."}
            </p>
          </div>

          <div className="md:col-span-2">
            <h3 className="text-lg font-semibold text-foreground mb-6">
              Get in Touch
            </h3>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              {contactMethods.map(({ Icon, label, href, value }) => (
                <div key={label} className="flex flex-col gap-2">
                  <p className="text-sm font-medium text-muted-foreground">{label}</p>
                   <Button variant="link" asChild className="p-0 h-auto justify-start text-foreground">
                     <a href={href} className="flex items-center gap-2">
                        <Icon className="h-4 w-4 text-primary" />
                        <span>{value}</span>
                     </a>
                   </Button>
                </div>
              ))}
            </div>
          </div>
        </div>

        <div className="mt-12 border-t pt-8 flex flex-col sm:flex-row justify-between items-center gap-4">
          <p className="text-sm text-muted-foreground text-center sm:text-left">
            {footerText}
          </p>
          <div className="flex flex-wrap gap-3">
            {footerLinks.length > 0 ? (
              footerLinks.map((link) => (
                <Button
                  key={link.url}
                  variant="link"
                  asChild
                  className="p-0 h-auto text-muted-foreground"
                >
                  <Link href={link.url}>{link.label}</Link>
                </Button>
              ))
            ) : (
              <Button variant="link" asChild className="p-0 h-auto text-muted-foreground">
                <Link href="/api/docs" className="flex items-center gap-2">
                  <FileCode className="h-4 w-4" />
                  <span>API Docs</span>
                </Link>
              </Button>
            )}
          </div>
        </div>
      </div>
    </footer>
  );
}
