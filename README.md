# Firebase Studio

This is a NextJS starter in Firebase Studio.

To get started, take a look at src/app/page.tsx.

## AI Chatbot

The floating “Chat with us” widget renders globally from `src/components/chat/ChatbotWidget.tsx` and uses the placeholder helper in `src/lib/chatbot.ts`. Replace `askChatbot` with a call to your backend or LLM provider and return a text response. The widget already manages user/assistant messages, loading states, and basic error handling.
