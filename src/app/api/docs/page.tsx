// src/app/api/docs/page.tsx
'use client';

import SwaggerUI from 'swagger-ui-react';
import 'swagger-ui-react/swagger-ui.css';

type Props = {
  spec: Record<string, any>;
};

function ApiDocsPage() {
  return <SwaggerUI url="/api/openapi.json" />;
}

export default ApiDocsPage;
