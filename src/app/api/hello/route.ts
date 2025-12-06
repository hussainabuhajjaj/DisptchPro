import { NextResponse } from 'next/server';

export const dynamic = 'force-static';
export const revalidate = false;

/**
 * @swagger
 * /api/hello:
 *   get:
 *     description: Returns a simple hello world message
 *     responses:
 *       200:
 *         description: A JSON object with a message.
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 message:
 *                   type: string
 *                   example: Hello from the API!
 */
export async function GET() {
  return NextResponse.json({ message: 'Hello from the API!' });
}
