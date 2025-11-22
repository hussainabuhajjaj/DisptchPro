import {genkit} from 'genkit';
import {googleAI} from '@genkit-ai/google-genai';
import next from '@genkit-ai/next';

export const ai = genkit({
  plugins: [
    googleAI({
      apiVersion: 'v1beta',
    }),
    next({
      // This forces the plugin to operate in a server-only mode,
      // preventing it from trying to access browser APIs like localStorage.
      context: 'server',
    }),
  ],
  model: 'googleai/gemini-1.5-flash-latest',
});
