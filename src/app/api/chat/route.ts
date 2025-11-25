import { NextResponse } from "next/server";
import { rateLimit } from "./rate-limit";
import { faqContext } from "@/lib/chatbot-prompts";

const SYSTEM_PROMPT =
  process.env.GENKIT_SYSTEM_PROMPT ||
  "You are H&A Dispatch's assistant. Be concise, helpful, and defer operations, legal, or pricing specifics to a human. Do not invent data.";
const MAX_HISTORY = 12;
const MAX_INPUT_CHARS = 2000;

/**
 * Genkit-ready chat endpoint.
 * - Set ENABLE_GENKIT="true" and GENKIT_CHAT_ENDPOINT (and optionally GENKIT_API_KEY)
 *   to forward messages to your Genkit service.
 * - Otherwise returns a mock response so local/dev works offline.
 */
export async function POST(req: Request) {
  const ip = req.headers.get("x-forwarded-for")?.split(",")[0]?.trim() || "anon";
  const limit = rateLimit(ip);
  if (!limit.allowed) {
    return NextResponse.json(
      { reply: "Rate limit reached. Please try again in a moment." },
      { status: 429, headers: { "Retry-After": String(Math.ceil((limit.retryAfter ?? 1000) / 1000)) } },
    );
  }

  const { message, history = [] } = await req.json();
  const clippedMessage = String(message || "").slice(0, MAX_INPUT_CHARS);
  const clippedHistory = Array.isArray(history)
    ? history.slice(-MAX_HISTORY).map((m: any) => ({
        role: m?.role === "assistant" ? "assistant" : "user",
        content: String(m?.content || "").slice(0, MAX_INPUT_CHARS),
      }))
    : [];

  const enableGenkit = process.env.ENABLE_GENKIT === "true";
  const endpoint = process.env.GENKIT_CHAT_ENDPOINT ?? "http://localhost:8081/api/chat";
  const apiKey = process.env.GENKIT_API_KEY ?? "";

  if (!enableGenkit) {
    return NextResponse.json({
      reply:
        "Genkit chat is in mock mode. Set ENABLE_GENKIT=true and GENKIT_CHAT_ENDPOINT to enable live answers.",
    });
  }

  try {
    // If endpoint is Gemini, adapt payload and headers.
    const isGemini = endpoint.includes("generativelanguage.googleapis.com");
    const headers: Record<string, string> = {
      "Content-Type": "application/json",
    };
    if (isGemini) {
      headers["X-goog-api-key"] = apiKey;
    } else if (apiKey) {
      headers["Authorization"] = `Bearer ${apiKey}`;
    }

    const body = isGemini
      ? {
          contents: [
            {
              parts: [
                { text: `System: ${SYSTEM_PROMPT}\n\nContext:\n- ${faqContext.join("\n- ")}` },
                ...clippedHistory.map((m) => ({
                  text: `${m.role === "assistant" ? "Assistant" : "User"}: ${m.content}`,
                })),
                { text: `User: ${clippedMessage}` },
              ],
            },
          ],
        }
      : {
          message: clippedMessage,
          history: clippedHistory,
          system: `${SYSTEM_PROMPT}\n\nContext:\n- ${faqContext.join("\n- ")}`,
        };

    const res = await fetch(endpoint, {
      method: "POST",
      headers,
      body: JSON.stringify(body),
    });

    if (!res.ok) {
      const text = await res.text();
      return NextResponse.json(
        { reply: "Chat is temporarily unavailable. Please try again.", error: text },
        { status: 502 },
      );
    }

    const data = await res.json();
    let reply: string | undefined;
    if (isGemini) {
      reply = data?.candidates?.[0]?.content?.parts?.[0]?.text;
    } else {
      reply = data.reply ?? data.output;
    }
    return NextResponse.json({
      reply: reply ?? "Thanks for reaching out. We'll get back to you soon.",
    });
  } catch (error: any) {
    return NextResponse.json(
      {
        reply: "Chat is temporarily unavailable. Please try again.",
        error: error?.message ?? "Unknown error",
      },
      { status: 502 },
    );
  }
}
