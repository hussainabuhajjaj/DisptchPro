
"use client";

import { Calendar } from "@/components/ui/calendar";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Button } from "@/components/ui/button";
import { useEffect, useState } from "react";
import { useToast } from "@/hooks/use-toast";
import { LoaderCircle, CheckCircle } from "lucide-react";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { createBooking } from "@/lib/booking-service";

const timeSlots = [
  "09:00 AM", "10:00 AM", "11:00 AM", "12:00 PM",
  "01:00 PM", "02:00 PM", "03:00 PM", "04:00 PM", "05:00 PM"
];

export default function Booking() {
  const { toast } = useToast();
  const [date, setDate] = useState<Date | undefined>(new Date());
  const [time, setTime] = useState<string | undefined>(timeSlots[0]);
  const [dateTimeString, setDateTimeString] = useState('');
  const [pending, setPending] = useState(false);
  const [success, setSuccess] = useState(false);
  const [message, setMessage] = useState("");
  const [loadApproved, setLoadApproved] = useState(true);
  const [pricingTransparent, setPricingTransparent] = useState(true);
  const [noContract, setNoContract] = useState(true);
  
  useEffect(() => {
    if (date && time) {
      const [hour, minute, ampm] = time.match(/(\d+):(\d+) (AM|PM)/)!.slice(1);
      let h = parseInt(hour, 10);
      if (ampm === "PM" && h !== 12) {
        h += 12;
      }
      if (ampm === "AM" && h === 12) {
        h = 0;
      }
      const newDate = new Date(date);
      newDate.setHours(h);
      newDate.setMinutes(parseInt(minute, 10));
      setDateTimeString(newDate.toISOString());
    }
  }, [date, time]);

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setPending(true);
    setMessage("");
    try {
      const formData = new FormData(event.currentTarget);
      const name = formData.get("name") as string;
      const email = formData.get("email") as string;
      const phone = (formData.get("phone") as string) || undefined;
      const notes = (formData.get("message") as string) || undefined;

      if (!dateTimeString) {
        throw new Error("Please select a date and time.");
      }

      await createBooking({
        title: `Consultation with ${name}`,
        type: "onboarding",
        start_at: dateTimeString,
        carrier_name: name,
        phone,
        email,
        notes,
      });

      setSuccess(true);
      setMessage("Your consultation request has been received! We will contact you shortly to confirm the details.");
      toast({ title: "Success!", description: "Booking submitted." });
    } catch (error: any) {
      const errMsg =
        error?.data?.message ||
        error?.message ||
        "Unable to submit booking. Please try again.";
      setMessage(errMsg);
      toast({ title: "Error", description: errMsg, variant: "destructive" });
    } finally {
      setPending(false);
    }
  }

  if (success) {
    return (
        <section id="book" className="w-full py-16 md:py-24">
            <div className="container mx-auto flex flex-col items-center justify-center text-center gap-4 px-4 md:px-6">
                <CheckCircle className="h-16 w-16 text-green-500" />
                <h2 className="text-3xl font-bold tracking-tighter">Thank You!</h2>
                <p className="text-lg text-muted-foreground max-w-md">{message}</p>
                 <Button onClick={() => window.location.reload()}>Book Another</Button>
            </div>
      </section>
    );
  }

  return (
    <section id="book" className="w-full py-16 md:py-24">
      <div className="container mx-auto px-4 md:px-6">
        <div className="mx-auto max-w-3xl text-center mb-12">
          <h2 className="text-3xl md:text-4xl font-bold tracking-tighter">
            Ready to Boost Your Profits?
          </h2>
          <p className="mt-4 text-lg text-muted-foreground">
            Start with a 14-day free trial. No long-term commitment. If we don’t deliver value, you don’t continue.
          </p>
          <div className="flex flex-col sm:flex-row justify-center gap-3 mt-4 text-sm text-muted-foreground">
            <div className="flex items-center gap-2">
              <CheckCircle className="h-4 w-4 text-primary" /> No long-term contract required
            </div>
            <div className="flex items-center gap-2">
              <CheckCircle className="h-4 w-4 text-primary" /> You approve every load before we book
            </div>
            <div className="flex items-center gap-2">
              <CheckCircle className="h-4 w-4 text-primary" /> Transparent pricing and weekly reporting
            </div>
          </div>
        </div>

        <Card className="max-w-4xl mx-auto shadow-lg">
          <div className="grid md:grid-cols-2 md:divide-x">
            <div className="p-6 flex flex-col justify-center items-center gap-4">
              <Calendar
                mode="single"
                selected={date}
                onSelect={setDate}
                className="p-0"
                disabled={(day) => day < new Date(new Date().setDate(new Date().getDate() - 1))}
              />
               <div className="w-full max-w-xs space-y-2">
                <Label htmlFor="time">Select a Time</Label>
                <Select value={time} onValueChange={setTime}>
                  <SelectTrigger id="time">
                    <SelectValue placeholder="Select a time" />
                  </SelectTrigger>
                  <SelectContent>
                    {timeSlots.map(slot => (
                      <SelectItem key={slot} value={slot}>{slot}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
               </div>
            </div>
            <div className="p-6">
              <CardHeader className="p-0 mb-6">
                <CardTitle>Book a Consultation</CardTitle>
                <CardDescription>Fill out the form and we'll be in touch shortly.</CardDescription>
              </CardHeader>
              <CardContent className="p-0">
                <form onSubmit={handleSubmit} className="space-y-4">
                  <input type="hidden" name="date" value={dateTimeString} />
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div className="space-y-2">
                      <Label htmlFor="name">Full Name</Label>
                      <Input id="name" name="name" placeholder="John Doe" required />
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="email">Email Address</Label>
                      <Input id="email" name="email" type="email" placeholder="john.doe@example.com" required />
                    </div>
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="phone">Phone Number</Label>
                    <Input id="phone" name="phone" placeholder="+1 (555) 123-4567" />
                  </div>
                   <div className="space-y-2">
                    <Label htmlFor="message">Message (Optional)</Label>
                    <Textarea id="message" name="message" placeholder="Tell us about your business or any questions you have." />
                  </div>
                  <Button type="submit" className="w-full" disabled={pending}>
                    {pending ? (
                      <>
                        <LoaderCircle className="mr-2 h-4 w-4 animate-spin" />
                        Submitting...
                      </>
                    ) : (
                      "Book My Call"
                    )}
                  </Button>
                </form>
              </CardContent>
            </div>
          </div>
        </Card>
        <div className="mt-8 grid gap-4 sm:grid-cols-3">
          <Card>
            <CardHeader>
              <CardTitle className="text-base flex items-center gap-2">
                <CheckCircle className="h-4 w-4 text-primary" /> What happens after I submit this form?
              </CardTitle>
            </CardHeader>
            <CardContent className="text-sm text-muted-foreground">
              We’ll call or email within one business day to confirm your lanes, equipment, and start date. You stay in control of every load.
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle className="text-base flex items-center gap-2">
                <CheckCircle className="h-4 w-4 text-primary" /> Will someone call or email me?
              </CardTitle>
            </CardHeader>
            <CardContent className="text-sm text-muted-foreground">
              Yes. A dispatcher reaches out via your preferred contact to finalize details and share next steps.
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle className="text-base flex items-center gap-2">
                <CheckCircle className="h-4 w-4 text-primary" /> How soon can I start?
              </CardTitle>
            </CardHeader>
            <CardContent className="text-sm text-muted-foreground">
              Typically within 24 hours after we align on lanes, documents, and approvals.
            </CardContent>
          </Card>
        </div>
      </div>
    </section>
  );
}
