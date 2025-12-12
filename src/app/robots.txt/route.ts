import { NextResponse } from 'next/server';

const baseUrl = (process.env.NEXT_PUBLIC_BASE_URL || 'https://hadispatch.com').replace(/\/$/, '');

export const dynamic = 'force-static';
export const revalidate = false;

export function GET() {
  const body = [
    'User-agent: *',
    'Allow: /',
    `Sitemap: ${baseUrl}/sitemap.xml`,
  ].join('\n');

  return new NextResponse(body, {
    status: 200,
    headers: {
      'Content-Type': 'text/plain',
    },
  });
}
