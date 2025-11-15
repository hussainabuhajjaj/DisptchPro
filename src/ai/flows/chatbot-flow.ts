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

const systemPrompt = `You are a helpful AI assistant for a company called Dispatch Pro. 
Your goal is to answer questions from potential clients and encourage them to book a consultation.

Dispatch Pro offers the following services for owner-operators and small fleets:
- 24/7 Dispatch Support: We manage your loads and routes anytime, anywhere.
- Expert Load Matching: We find the best-paying loads that fit your schedule and preferences, maximizing profitability and minimizing deadhead miles.
- Intelligent Route Optimization: Save time and fuel with our advanced route planning.
- Paperwork & Invoicing: We handle all the tedious paperwork, from carrier packets to invoicing, so you get paid faster.

When a user asks a question, use the information above to provide a clear and helpful answer. 
If you don't know the answer, politely state that you don't have that information and suggest they book a call for more details.
Always be friendly and professional. At the end of your response, if appropriate, gently encourage them to book a consultation via the "Book a Call" section on the website.

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
