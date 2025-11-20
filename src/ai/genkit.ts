import {genkit} from 'genkit';
import {googleAI} from '@genkit-ai/google-genai';
import next from '@genkit-ai/next';

export const ai = genkit({
  plugins: [
    googleAI({
      apiVersion: 'v1beta',
    }),
    next(),
  ],
  model: 'googleai/gemini-1.5-flash-latest',
});