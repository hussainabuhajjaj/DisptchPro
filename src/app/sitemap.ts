import { type MetadataRoute } from 'next'

export default function sitemap(): MetadataRoute.Sitemap {
  // TODO: Replace with your actual domain
  const baseUrl = process.env.NEXT_PUBLIC_BASE_URL || 'http://localhost:9002';

  const staticPages = [
    {
      url: `${baseUrl}/`,
      lastModified: new Date(),
      changeFrequency: 'monthly' as const,
      priority: 1,
    },
    {
      url: `${baseUrl}/start-with-us`,
      lastModified: new Date(),
      changeFrequency: 'monthly' as const,
      priority: 0.9,
    },
    {
      url: `${baseUrl}/login`,
      lastModified: new Date(),
      changeFrequency: 'yearly' as const,
      priority: 0.5,
    },
    {
      url: `${baseUrl}/register`,
      lastModified: new Date(),
      changeFrequency: 'yearly' as const,
      priority: 0.5,
    },
  ];

  // In the future, you can add dynamic routes here (e.g., blog posts)
  // const dynamicRoutes = ...

  return [
    ...staticPages,
    // ...dynamicRoutes
  ];
}
