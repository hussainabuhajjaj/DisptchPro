'use server';

/**
 * @fileOverview Generates answers to frequently asked questions about dispatching services using AI.
 *
 * - generateFAQAnswers - A function that generates answers to FAQs.
 * - GenerateFAQAnswersInput - The input type for the generateFAQAnswers function.
 * - GenerateFAQAnswersOutput - The return type for the generateFAQAnswers function.
 */

import {ai} from '@/ai/genkit';
import {z} from 'genkit';

const GenerateFAQAnswersInputSchema = z.object({
  faqDetails: z
    .string()
    .describe(
      'Details about the dispatching service and common questions from potential clients.'
    ),
});
export type GenerateFAQAnswersInput = z.infer<typeof GenerateFAQAnswersInputSchema>;

const GenerateFAQAnswersOutputSchema = z.object({
  answers: z
    .string()
    .describe(
      'Answers to the frequently asked questions, tailored for potential clients.'
    ),
});
export type GenerateFAQAnswersOutput = z.infer<typeof GenerateFAQAnswersOutputSchema>;

export async function generateFAQAnswers(
  input: GenerateFAQAnswersInput
): Promise<GenerateFAQAnswersOutput> {
  return generateFAQAnswersFlow(input);
}

const prompt = ai.definePrompt({
  name: 'generateFAQAnswersPrompt',
  input: {schema: GenerateFAQAnswersInputSchema},
  output: {schema: GenerateFAQAnswersOutputSchema},
  prompt: `You are an AI assistant specializing in generating clear and concise answers to frequently asked questions about dispatching services.

  Based on the following details, provide answers to common questions that potential clients might have. The answers should be geared toward attracting and informing prospective clients.
  Details: {{{faqDetails}}} `,
});

const generateFAQAnswersFlow = ai.defineFlow(
  {
    name: 'generateFAQAnswersFlow',
    inputSchema: GenerateFAQAnswersInputSchema,
    outputSchema: GenerateFAQAnswersOutputSchema,
  },
  async input => {
    const {output} = await prompt(input);
    return output!;
  }
);
