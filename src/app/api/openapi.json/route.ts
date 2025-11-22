// src/app/api/openapi.json/route.ts
import { NextResponse } from 'next/server';
import swaggerJsdoc from 'swagger-jsdoc';

export async function GET() {
  const options = {
    definition: {
      openapi: '3.0.0',
      info: {
        title: 'H&A Dispatch API',
        version: '1.0.0',
        description: 'API documentation for the H&A Dispatch application.',
      },
      servers: [
        {
          url: '/',
        },
      ],
    },
    apis: ['./src/app/api/**/*.ts'], // Path to the API docs
  };

  const openapiSpecification = swaggerJsdoc(options);

  return NextResponse.json(openapiSpecification);
}
