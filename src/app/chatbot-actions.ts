'use server';

import {chat, type ChatInput} from '@/ai/flows/chatbot-flow';
import {z} from 'zod';

const MessageSchema = z.object({
  role: z.enum(['user', 'model']),
  content: z.string(),
});

const UserDetailsSchema = z.object({
  name: z.string(),
  email: z.string(),
  company: z.string().optional(),
});


const ChatRequestSchema = z.object({
  history: z.array(MessageSchema),
  message: z.string().min(1, 'Message cannot be empty.'),
  userDetails: UserDetailsSchema,
});

interface ChatState {
  response?: string;
  error?: string;
}

export async function chatAction(
  prevState: ChatState,
  formData: FormData
): Promise<ChatState> {
  let validatedFields;
  try {
    const history = JSON.parse(formData.get('history') as string);
    const userDetails = JSON.parse(formData.get('userDetails') as string);
    validatedFields = ChatRequestSchema.safeParse({
      history: history,
      message: formData.get('message'),
      userDetails: userDetails,
    });
  } catch (error) {
    return {error: 'Invalid form data format.'};
  }

  if (!validatedFields.success) {
    return {
      error: validatedFields.error.errors.map(e => e.message).join(', '),
    };
  }

  try {
    const result = await chat(validatedFields.data as ChatInput);
    return {response: result.response};
  } catch (error) {
    console.error('Chatbot Error:', error);
    return {error: 'The AI is having trouble responding. Please try again later.'};
  }
}
