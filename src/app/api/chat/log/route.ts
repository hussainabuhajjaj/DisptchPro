/**
 * Lightweight chat logging endpoint.
 * Writes anonymized question + timestamp to server console.
 * In a real setup, swap this to persist logs to a database or logging sink.
 */
import { NextResponse } from "next/server";
import { mkdir, appendFile } from "fs/promises";
import { join } from "path";

export async function POST(req: Request) {
  try {
    const { message, ts } = await req.json();
    // Minimal logging without PII
    const logEntry = {
      message: String(message || "").slice(0, 500),
      ts: ts ?? Date.now(),
    };
    console.info("[chat-log]", logEntry);

    try {
      const logDir = join(process.cwd(), "logs");
      await mkdir(logDir, { recursive: true });
      await appendFile(
        join(logDir, "chat.log"),
        JSON.stringify(logEntry) + "\n",
        "utf8",
      );
    } catch (fileError) {
      console.warn("[chat-log] file append skipped", fileError);
    }
  } catch (error) {
    console.warn("[chat-log] failed", error);
  }
  return NextResponse.json({ ok: true });
}
