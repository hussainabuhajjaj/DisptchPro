'use client';

import { useCallback, useEffect, useRef, useState } from "react";
import { MessageCircle, Send, X, RotateCw, Copy } from "lucide-react";

import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import type { ChatMessage } from "@/types/chatbot";
import { askChatbot } from "@/lib/chatbot";
import { cn } from "@/lib/utils";
import { quickPrompts, resourceLinks } from "@/lib/chatbot-prompts";

function createMessage(role: ChatMessage["role"], content: string): ChatMessage {
  return {
    id: `${role}-${Date.now()}-${Math.random().toString(36).slice(2)}`,
    role,
    content,
    timestamp: Date.now(),
  };
}

export default function ChatbotWidget() {
  const [isOpen, setIsOpen] = useState(false);
  const [inputValue, setInputValue] = useState("");
  const [messages, setMessages] = useState<ChatMessage[]>(() => {
    if (typeof window === "undefined") return [
      createMessage("assistant", "Hi! I'm DispatchPro Assistant. How can I help you today?"),
    ];
    const stored = localStorage.getItem("chat_session");
    if (stored) return JSON.parse(stored) as ChatMessage[];
    return [createMessage("assistant", "Hi! I'm DispatchPro Assistant. How can I help you today?")];
  });
  const [isLoading, setIsLoading] = useState(false);
  const [isTyping, setIsTyping] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [errorCount, setErrorCount] = useState(0);
  const scrollRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (scrollRef.current) {
      scrollRef.current.scrollTop = scrollRef.current.scrollHeight;
    }
    if (typeof window !== "undefined") {
      localStorage.setItem("chat_session", JSON.stringify(messages));
    }
  }, [messages, isOpen]);

  const handleSubmit = useCallback(
    async (event?: React.FormEvent) => {
      event?.preventDefault();
      const trimmed = inputValue.trim();
      if (!trimmed || isLoading) return;

      const userMessage = createMessage("user", trimmed);
      setMessages((prev) => [...prev, userMessage]);
      setInputValue("");
      setIsLoading(true);
      setIsTyping(true);
      setErrorMessage(null);

      try {
        const reply = await askChatbot(trimmed, [...messages, userMessage]);
        const assistantMessage = createMessage("assistant", reply);
        setMessages((prev) => [...prev, assistantMessage]);
      } catch (error) {
        setMessages((prev) => [
          ...prev,
          createMessage(
            "assistant",
            "Sorry, I couldn't process that. Please try again.",
          ),
        ]);
        console.error("Chatbot error", error);
        setErrorMessage(
          error instanceof Error
            ? error.message
            : "Chat is temporarily unavailable. Please try again or email dispatch@dispatchpro.com.",
        );
        setErrorCount((c) => c + 1);
      } finally {
        setIsLoading(false);
        setIsTyping(false);
      }
    },
    [inputValue, isLoading, messages],
  );

  const toggleOpen = () => setIsOpen((prev) => !prev);

  const handleReset = () => {
    setMessages([
      createMessage(
        "assistant",
        "Hi! I'm DispatchPro Assistant. How can I help you today?",
      ),
    ]);
    setInputValue("");
    setIsTyping(false);
    setErrorCount(0);
    if (typeof window !== "undefined") {
      localStorage.removeItem("chat_session");
    }
  };

  return (
    <div className="fixed right-4 bottom-4 z-50 hidden">
      {isOpen && (
        <div className="mb-3 w-80 sm:w-96 rounded-xl border bg-background shadow-2xl">
            <div className="flex items-center justify-between border-b px-4 py-3">
              <div>
                <p className="text-sm font-semibold">DispatchPro Assistant</p>
                <p className="text-xs text-muted-foreground">
                  Ask anything about our services.
                </p>
                <span className="mt-1 inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] text-muted-foreground">
                  {process.env.NEXT_PUBLIC_CHAT_MOCK === "false" ? (
                    <>
                      <span className="h-2 w-2 rounded-full bg-emerald-500" /> Live
                    </>
                  ) : (
                    <>
                      <span className="h-2 w-2 rounded-full bg-amber-500" /> Mock
                    </>
                  )}
                </span>
              </div>
            <div className="flex items-center gap-2">
              <button
                type="button"
                className="rounded-full p-1 text-muted-foreground hover:bg-muted"
                onClick={handleReset}
                aria-label="Restart conversation"
              >
                <RotateCw className="h-4 w-4" />
              </button>
              <button
                type="button"
                className="rounded-full p-1 text-muted-foreground hover:bg-muted"
                onClick={toggleOpen}
                aria-label="Close chat"
              >
                <X className="h-4 w-4" />
              </button>
            </div>
          </div>

          <div className="h-72 overflow-y-auto px-4 py-3" ref={scrollRef}>
            <div className="space-y-3">
              {messages.map((message) => (
                <div
                  key={message.id}
                  className={cn(
                    "max-w-[85%] rounded-lg px-3 py-2 text-sm",
                    message.role === "assistant"
                      ? "bg-muted text-foreground"
                      : "ml-auto bg-primary text-primary-foreground",
                  )}
                >
                  <div className="flex items-start gap-2">
                    <span className="flex-1 whitespace-pre-wrap">{message.content}</span>
                    {message.role === "assistant" && (
                      <button
                        type="button"
                        className="text-xs text-muted-foreground hover:text-foreground"
                        onClick={() => {
                          if (navigator?.clipboard) {
                            navigator.clipboard.writeText(message.content).catch(() => {});
                          }
                        }}
                        aria-label="Copy reply"
                      >
                        <Copy className="h-3 w-3" />
                      </button>
                    )}
                  </div>
                </div>
              ))}
            </div>
            </div>

            {quickPrompts.length > 0 && (
              <div className="mt-2 flex flex-wrap gap-2">
                {quickPrompts.map((prompt) => (
                  <button
                    key={prompt}
                    type="button"
                    className="rounded-full border px-3 py-1 text-xs hover:bg-muted"
                    onClick={() => setInputValue(prompt)}
                  >
                    {prompt}
                  </button>
                ))}
              </div>
            )}

          <form className="border-t p-3" onSubmit={handleSubmit}>
            <div className="flex items-center gap-2">
              <Input
                value={inputValue}
                onChange={(event) => setInputValue(event.target.value)}
                placeholder="Type your question..."
                disabled={isLoading}
              />
              <Button
                type="submit"
                size="icon"
                disabled={isLoading || !inputValue.trim()}
              >
                <Send className="h-4 w-4" />
              </Button>
            </div>
            {isTyping && (
              <p className="mt-2 text-xs text-muted-foreground">Assistant is typing…</p>
            )}
            {errorMessage && (
              <p className="mt-2 text-xs text-destructive">{errorMessage}</p>
            )}
            <p className="mt-2 text-xs text-muted-foreground">
              Responses are AI-generated. For specific load or contract
              questions, contact our team.
            </p>
            <p className="mt-1 text-xs text-muted-foreground">
              Prefer email?{" "}
              <a href="mailto:dispatch@dispatchpro.com" className="underline">
                dispatch@dispatchpro.com
              </a>
            </p>
            {errorCount >= 2 && (
              <p className="mt-1 text-xs text-amber-600">
                Having trouble? Call or email us and we’ll respond quickly.
              </p>
            )}
            {resourceLinks.length > 0 && (
              <div className="mt-3 flex flex-wrap gap-2">
                {resourceLinks.map((link) => (
                  <a
                    key={link.href}
                    href={link.href}
                    className="rounded-full border px-3 py-1 text-xs hover:bg-muted"
                  >
                    {link.label}
                  </a>
                ))}
              </div>
            )}
          </form>
        </div>
      )}

      <Button
        size="lg"
        className="rounded-full shadow-xl"
        onClick={toggleOpen}
        aria-label="Open chat assistant"
      >
        <MessageCircle className="mr-2 h-4 w-4" />
        Chat with us
      </Button>
    </div>
  );
}
