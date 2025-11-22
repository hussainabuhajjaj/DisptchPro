import Link from "next/link";
import { Mail, Phone, MessageCircle, Truck, FileCode } from "lucide-react";
import { Button } from "@/components/ui/button";

const contactMethods = [
  {
    Icon: MessageCircle,
    label: "WhatsApp",
    href: "https://wa.me/1234567890", // Replace with your WhatsApp number
    value: "+1 (234) 567-890",
  },
  {
    Icon: Phone,
    label: "Phone",
    href: "tel:+1234567890", // Replace with your phone number
    value: "+1 (234) 567-890",
  },
  {
    Icon: Mail,
    label: "Email",
    href: "mailto:contact@hadispatch.com", // Replace with your email
    value: "contact@hadispatch.com",
  },
];

export default function Footer() {
  return (
    <footer className="bg-secondary/50">
      <div className="container mx-auto px-4 py-12 md:px-6">
        <div className="grid gap-12 md:grid-cols-3">
          <div className="flex flex-col gap-4">
            <Link href="/" className="flex items-center gap-2">
              <Truck className="h-7 w-7 text-primary" />
              <span className="text-xl font-bold text-foreground">
                H&A Dispatch
              </span>
            </Link>
            <p className="text-muted-foreground text-sm">
              Optimizing logistics for owner-operators and small fleets.
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
            © {new Date().getFullYear()} H&A Dispatch. All Rights Reserved.
          </p>
          <Button variant="link" asChild className="p-0 h-auto text-muted-foreground">
            <Link href="/api/docs" className="flex items-center gap-2">
              <FileCode className="h-4 w-4" />
              <span>API Docs</span>
            </Link>
          </Button>
        </div>
      </div>
    </footer>
  );
}
