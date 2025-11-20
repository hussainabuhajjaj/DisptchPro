import { config } from 'dotenv';
config();

// Import your flows here
// e.g. import './flows/my-flow.ts';
import '@/ai/flows/generate-faq-answers.ts';
import '@/ai/flows/chatbot-flow.ts';
