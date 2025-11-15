"use server";

import { z } from "zod";
import { generateFAQAnswers } from "@/ai/flows/generate-faq-answers";

interface FormState {
  message: string;
  success?: boolean;
}

// Action for FAQ Generation
const faqSchema = z.object({
  details: z.string().min(20, "Please provide at least 20 characters of detail."),
});

export async function generateFaqAction(prevState: FormState, formData: FormData): Promise<FormState> {
  const validatedFields = faqSchema.safeParse({
    details: formData.get('details'),
  });

  if (!validatedFields.success) {
    return {
      message: validatedFields.error.errors.map((e) => e.message).join(", "),
      success: false,
    };
  }

  try {
    const result = await generateFAQAnswers({ faqDetails: validatedFields.data.details });
    if (result.answers) {
      return { message: result.answers, success: true };
    } else {
      throw new Error("AI did not return an answer.");
    }
  } catch (error) {
    console.error("FAQ Generation Error:", error);
    return { message: 'Failed to generate FAQ. Please try again later.', success: false };
  }
}

// Action for Consultation Booking
const bookingSchema = z.object({
  name: z.string().min(2, "Name is required."),
  email: z.string().email("Invalid email address."),
  phone: z.string().optional(),
  message: z.string().optional(),
  date: z.string().optional(),
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
