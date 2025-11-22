import type { Metadata } from "next";
import "./globals.css";
import { Inter } from "next/font/google";
import { cn } from "@/lib/utils";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import { Toaster } from "@/components/ui/toaster";
import Chatbot from "@/components/Chatbot";
import { FirebaseClientProvider } from "@/firebase";

const inter = Inter({ subsets: ["latin"], variable: "--font-sans", display: 'swap' });

export const metadata: Metadata = {
  title: "H&A Dispatch - Your Logistics Partner",
  description: "Expert dispatching services to optimize your logistics and keep your fleet moving. We offer lead capture, appointment booking, and 24/7 support.",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en" className="scroll-smooth">
       <body
        className={cn(
          "min-h-screen bg-background font-sans antialiased",
          inter.variable
        )}
      >
        <FirebaseClientProvider>
          <Header />
          <main className="flex-1">{children}</main>
          <Footer />
          <Chatbot />
          <Toaster />
        </FirebaseClientProvider>
      </body>
    </html>
  );
}
