"use client";

import * as React from "react";
import Link from "next/link";
import { ChevronDown, Menu, Truck, User } from "lucide-react";
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
import { useRouter } from "next/navigation";

// This is a placeholder for a real user session management hook
const useUser = () => {
  const [user, setUser] = React.useState<{ email: string } | null>(null);
  const [isLoading, setIsLoading] = React.useState(true);

  React.useEffect(() => {
    // TODO: Replace with a real session check against your Laravel API
    const timer = setTimeout(() => {
       // To test the "logged out" state, set this to null
      setUser({ email: 'carrier@example.com' });
      setIsLoading(false);
    }, 500);
    return () => clearTimeout(timer);
  }, []);

  return { user, isLoading };
};


const mainNavLinks = [
  { href: "/#services", label: "Services" },
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
      "text-sm font-medium text-foreground/80 transition-colors hover:text-primary",
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
  const { user, isLoading } = useUser();
  const router = useRouter();

  const handleLogout = async () => {
    // TODO: Implement logout with Laravel API
    console.log("Logging out...");
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
          <span className="font-bold text-lg text-foreground">H&A Dispatch</span>
        </Link>

        <nav className="hidden items-center gap-6 md:flex">
          {mainNavLinks.map((link) =>
            "items" in link ? (
              <NavDropdown key={link.label} {...link} />
            ) : (
              <NavLink key={link.href} {...link} />
            )
          )}
        </nav>
        
        <div className="flex items-center gap-2">
          <div className="hidden md:flex items-center gap-2">
            {!isLoading && (
              user ? (
                <>
                  <Button variant="ghost" asChild>
                    <Link href="/dashboard">Dashboard</Link>
                  </Button>
                  <Button variant="outline" onClick={handleLogout}>Logout</Button>
                </>
              ) : (
                 <Button asChild>
                    <Link href="/login">Carrier Login</Link>
                  </Button>
              )
            )}
          </div>
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
                    {!isLoading && (
                      user ? (
                        <>
                          <SheetClose asChild>
                            <NavLink href="/dashboard" label="Dashboard" className="text-lg"/>
                          </SheetClose>
                           <Button variant="outline" onClick={handleLogout}>Logout</Button>
                        </>
                      ) : (
                         <SheetClose asChild>
                           <Button asChild className="w-full">
                              <Link href="/login">Carrier Login</Link>
                            </Button>
                         </SheetClose>
                      )
                    )}
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
