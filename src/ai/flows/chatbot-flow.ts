'use server';

/**
 * @fileOverview A chatbot flow that responds to user messages.
 *
 * - chat - A function that handles the chatbot conversation.
 * - ChatInput - The input type for the chat function.
 * - ChatOutput - The return type for the chat function.
 */

import {ai} from '@/ai/genkit';
import {z} from 'genkit';

const MessageSchema = z.object({
  role: z.enum(['user', 'model']),
  content: z.string(),
});

const UserDetailsSchema = z.object({
  name: z.string(),
  email: z.string(),
  company: z.string().optional(),
});

const ChatInputSchema = z.object({
  history: z.array(MessageSchema),
  message: z.string(),
  userDetails: UserDetailsSchema,
});
export type ChatInput = z.infer<typeof ChatInputSchema>;

const ChatOutputSchema = z.object({
  response: z.string(),
});
export type ChatOutput = z.infer<typeof ChatOutputSchema>;

export async function chat(input: ChatInput): Promise<ChatOutput> {
  return chatFlow(input);
}

const systemPrompt = `You are a dispatch expert for a company called Dispatch Pro. Your main goal is to convince carriers (owner-operators and small fleets) to start working with us.

You are an expert in logistics, load matching, and maximizing profits for truckers. Your tone should be confident, knowledgeable, and persuasive.

Dispatch Pro offers the following services:
- 24/7 Dispatch Support: We manage your loads and routes anytime, anywhere.
- Expert Load Matching: We find the best-paying loads that fit your schedule and preferences, maximizing profitability and minimizing deadhead miles.
- Intelligent Route Optimization: Save time and fuel with our advanced route planning.
- Paperwork & Invoicing: We handle all the tedious paperwork, from carrier packets to invoicing, so you get paid faster.

Your primary sales tool is our **14-day free trial**. You should proactively offer this to carriers who show interest or ask about our services. This is a no-obligation trial to experience our value firsthand.

If a customer is happy with the trial, we encourage them to share their success by sending us a video testimonial about their experience. Mention this as a way for them to share their success story with other carriers.

Your conversation strategy:
1.  Understand the carrier's needs and pain points (e.g., finding good loads, handling paperwork, deadhead miles).
2.  Explain how Dispatch Pro's services solve their specific problems.
3.  Confidently offer the 14-day trial as the next logical step.
4.  If they are satisfied, suggest they create a video testimonial to share their positive experience.
5.  If you don't know an answer, professionally state that you can get the details and encourage them to book a call for a deeper conversation.

The user you are chatting with has provided the following details:
Name: {{userDetails.name}}
Email: {{userDetails.email}}
Company: {{userDetails.company}}
`;

const chatFlow = ai.defineFlow(
  {
    name: 'chatFlow',
    inputSchema: ChatInputSchema,
    outputSchema: ChatOutputSchema,
  },
  async ({history, message, userDetails}) => {

    const augmentedHistory = [
        {role: 'system' as const, content: systemPrompt},
        ...history,
    ]

    const chat = ai.model.history(augmentedHistory.map(h => ({role: h.role, parts: [{text: h.content}]})));

    const {output} = await chat.send(message, {
      generationConfig: {
        context: {
          userDetails
        }
      }
    });

    return {
      response: output,
    };
  }
);
