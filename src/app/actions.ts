
"use server";

import { z } from "zod";

interface FormState {
  message: string;
  success?: boolean;
}

// Action for Consultation Booking
const bookingSchema = z.object({
  name: z.string().min(2, "Name is required."),
  email: z.string().email("Invalid email address."),
  phone: z.string().optional(),
  message: z.string().optional(),
  date: z.string().datetime("Please select a valid date and time."),
});

export async function bookConsultationAction(prevState: FormState, formData: FormData): Promise<FormState> {
  const validatedFields = bookingSchema.safeParse({
    name: formData.get('name'),
    email: formData.get('email'),
    phone: formData.get('phone'),
    message: formData.get('message'),
    date: formData.get('date'),
  });

  if (!validatedFields.success) {
    return {
      message: validatedFields.error.errors.map((e) => e.message).join(", "),
      success: false,
    };
  }

  // In a real application, you would save this data to a database.
  // For this demo, we'll just log it and return a success message.
  console.log("New Consultation Booking:", validatedFields.data);

  return {
    message: "Your consultation request has been received! We will contact you shortly to confirm the details.",
    success: true,
  };
}
