import type { Metadata } from "next";
import "./globals.css";
import { Inter } from "next/font/google";
import { cn } from "@/lib/utils";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import { Toaster } from "@/components/ui/toaster";
import { AuthProvider } from "@/components/providers/AuthProvider";
import ChatbotWidget from "@/components/chat/ChatbotWidget";
import { fetchLandingContent } from "@/lib/landing-content";
import { loadThemeVars } from "@/lib/theme";
import type { CSSProperties } from "react";

const inter = Inter({ subsets: ["latin"], variable: "--font-sans", display: "swap" });

export async function generateMetadata(): Promise<Metadata> {
  try {
    const data = await fetchLandingContent();
    const settings = (data as any)?.settings ?? {};
    const title = settings.meta_title || settings.site_title || "H&A Dispatch";
    const description =
      settings.meta_description ||
      settings.site_description ||
      "Expert dispatching services to optimize your logistics and keep your fleet moving.";
    const ogImage = settings.og_image as string | undefined;
    const twitterHandle = settings.twitter_handle as string | undefined;

    return {
      title,
      description,
      openGraph: {
        title,
        description,
        images: ogImage ? [ogImage] : undefined,
      },
      twitter: {
        card: "summary_large_image",
        site: twitterHandle,
      },
    };
  } catch {
    return {
      title: "H&A Dispatch",
      description:
        "Expert dispatching services to optimize your logistics and keep your fleet moving. We offer lead capture, appointment booking, and 24/7 support.",
    };
  }
}

export default async function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  const themeVars = await loadThemeVars();

  return (
    <html lang="en" className="scroll-smooth">
      <body
        className={cn("min-h-screen bg-background font-sans antialiased", inter.variable)}
        style={themeVars as CSSProperties}
      >
        <AuthProvider>
          <Header />
          <main className="flex-1">{children}</main>
          <Footer />
        </AuthProvider>
        <ChatbotWidget />
        <Toaster />
      </body>
    </html>
  );
}
