
"use client";

import * as React from "react";
import Link from "next/link";
import { Menu, Truck } from "lucide-react";
import { useUser, useAuth } from "@/firebase";
import { signOut } from "firebase/auth";
import {
  Sheet,
  SheetContent,
  SheetTrigger,
  SheetClose,
} from "@/components/ui/sheet";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import { useRouter } from "next/navigation";

const navLinks = [
  { href: "/#services", label: "Services" },
  { href: "/#why-us", label: "Why Us" },
  { href: "/#roadmap", label: "Roadmap" },
  { href: "/#testimonials", label: "Testimonials" },
  { href: "/#faq", label: "FAQ" },
  { href: "/#book", label: "Book a Call" },
];

const NavLink = ({
  href,
  label,
  className,
  onClick,
}: {
  href: string;
  label: string;
  className?: string;
  onClick?: () => void;
}) => (
  <Link
    href={href}
    onClick={onClick}
    className={cn(
      "text-sm font-medium text-foreground/80 transition-colors hover:text-foreground",
      className
    )}
  >
    {label}
  </Link>
);

export default function Header() {
  const [isScrolled, setIsScrolled] = React.useState(false);
  const { user, isUserLoading } = useUser();
  const auth = useAuth();
  const router = useRouter();

  const handleLogout = async () => {
    await signOut(auth);
    router.push("/");
  };

  React.useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 10);
    };
    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  return (
    <header
      className={cn(
        "sticky top-0 z-50 w-full border-b border-transparent transition-all duration-300",
        isScrolled ? "border-border/60 bg-background/80 backdrop-blur-lg" : ""
      )}
    >
      <div className="container mx-auto flex h-16 items-center justify-between px-4 md:px-6">
        <Link href="/" className="flex items-center gap-2">
          <Truck className="h-6 w-6 text-primary" />
          <span className="font-bold text-lg text-foreground">Dispatch Pro</span>
        </Link>

        <nav className="hidden items-center gap-4 md:flex">
          {navLinks.map((link) => (
            <NavLink key={link.href} {...link} />
          ))}
           <div className="flex items-center gap-2">
            {!isUserLoading &&
              (user ? (
                <>
                  <Button variant="ghost" asChild>
                    <Link href="/dashboard">Dashboard</Link>
                  </Button>
                  <Button variant="outline" onClick={handleLogout}>Logout</Button>
                </>
              ) : (
                <Button asChild>
                  <Link href="/register">Get Started</Link>
                </Button>
              ))}
          </div>
        </nav>

        <div className="md:hidden">
          <Sheet>
            <SheetTrigger asChild>
              <Button variant="ghost" size="icon">
                <Menu className="h-6 w-6" />
                <span className="sr-only">Toggle navigation menu</span>
              </Button>
            </SheetTrigger>
            <SheetContent side="right">
              <div className="flex flex-col gap-6 p-6">
                <Link href="/" className="flex items-center gap-2">
                   <Truck className="h-6 w-6 text-primary" />
                   <span className="font-bold">Dispatch Pro</span>
                </Link>
                <nav className="flex flex-col gap-4">
                  {navLinks.map((link) => (
                     <SheetClose asChild key={link.href}>
                        <NavLink {...link} className="text-lg" />
                     </SheetClose>
                  ))}
                   <div className="border-t pt-4 flex flex-col gap-4">
                    {!isUserLoading &&
                      (user ? (
                        <>
                          <SheetClose asChild>
                            <NavLink href="/dashboard" label="Dashboard" className="text-lg"/>
                          </SheetClose>
                           <Button variant="outline" onClick={handleLogout}>Logout</Button>
                        </>
                      ) : (
                          <SheetClose asChild>
                             <Button asChild>
                                <Link href="/register">Get Started</Link>
                             </Button>
                          </SheetClose>
                      ))}
                  </div>
                </nav>
              </div>
            </SheetContent>
          </Sheet>
        </div>
      </div>
    </header>
  );
}
