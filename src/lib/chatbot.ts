import type { ChatMessage } from "@/types/chatbot";

const USE_MOCK = process.env.NEXT_PUBLIC_CHAT_MOCK !== "false";
const MAX_REPLY_CHARS = 800;
const MAX_CONVERSATION = 40;

export async function askChatbot(
  message: string,
  history: ChatMessage[],
): Promise<string> {
  // Trim history length
  const clippedHistory =
    history.length > MAX_CONVERSATION ? history.slice(-MAX_CONVERSATION) : history;

  const payload = { message, history: clippedHistory };

  if (!USE_MOCK) {
    try {
      const response = await fetch("/api/chat", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      const text = await response.text();
      if (!response.ok) {
        throw new Error(text || `Chat API error (${response.status})`);
      }
      const data = text ? (JSON.parse(text) as { reply?: string; error?: string }) : {};
      if (data.reply) {
        const reply = data.reply.slice(0, MAX_REPLY_CHARS);
        void logChat(message);
        return reply;
      }
      throw new Error(data.error || "No reply returned from chat service.");
    } catch (error: any) {
      console.warn("Chat API request failed.", error);
      throw error;
    }
  }

  // Mock fallback for offline/dev
  await new Promise((resolve) => setTimeout(resolve, 300));
  if (!history.length) {
    return "Hi! I'm DispatchPro Assistant. Ask me about booking a call, onboarding, or dispatch support.";
  }
  return `Thanks for asking: "${message}". Our team will follow up shortly with specifics.`;
}

function logChat(message: string) {
  try {
    fetch("/api/chat/log", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ message: message.slice(0, 500), ts: Date.now() }),
    }).catch(() => {});
  } catch {
    // ignore
  }
}
