"use client";

import { useFormState, useFormStatus } from "react-dom";
import { Calendar } from "@/components/ui/calendar";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Button } from "@/components/ui/button";
import { bookConsultationAction } from "@/app/actions";
import { useEffect, useState } from "react";
import { useToast } from "@/hooks/use-toast";
import { LoaderCircle, CheckCircle } from "lucide-react";

const initialState = {
  message: "",
  success: false,
};

function SubmitButton() {
  const { pending } = useFormStatus();
  return (
    <Button type="submit" className="w-full" disabled={pending}>
      {pending ? (
        <>
          <LoaderCircle className="mr-2 h-4 w-4 animate-spin" />
          Submitting...
        </>
      ) : "Book My Call"}
    </Button>
  );
}

export default function Booking() {
  const [state, formAction] = useFormState(bookConsultationAction, initialState);
  const { toast } = useToast();
  const [date, setDate] = useState<Date | undefined>(new Date());
  
  useEffect(() => {
    if (state.message) {
      toast({
        title: state.success ? "Success!" : "Error",
        description: state.message,
        variant: state.success ? "default" : "destructive",
      });
    }
  }, [state, toast]);

  if (state.success) {
    return (
        <section id="book" className="w-full py-16 md:py-24">
            <div className="container mx-auto flex flex-col items-center justify-center text-center gap-4 px-4 md:px-6">
                <CheckCircle className="h-16 w-16 text-green-500" />
                <h2 className="text-3xl font-bold tracking-tighter">Thank You!</h2>
                <p className="text-lg text-muted-foreground max-w-md">{state.message}</p>
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
            Schedule a free, no-obligation consultation call. Let's discuss how we can help you achieve your business goals.
          </p>
        </div>

        <Card className="max-w-4xl mx-auto shadow-lg">
          <div className="grid md:grid-cols-2 md:divide-x">
            <div className="p-6 flex justify-center items-center">
              <Calendar
                mode="single"
                selected={date}
                onSelect={setDate}
                className="p-0"
                disabled={(day) => day < new Date(new Date().setDate(new Date().getDate() - 1))}
              />
            </div>
            <div className="p-6">
              <CardHeader className="p-0 mb-6">
                <CardTitle>Book a Consultation</CardTitle>
                <CardDescription>Fill out the form and we'll be in touch shortly.</CardDescription>
              </CardHeader>
              <CardContent className="p-0">
                <form action={formAction} className="space-y-4">
                  <input type="hidden" name="date" value={date?.toISOString() ?? ""} />
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
                  <SubmitButton />
                </form>
              </CardContent>
            </div>
          </div>
        </Card>
      </div>
    </section>
  );
}
