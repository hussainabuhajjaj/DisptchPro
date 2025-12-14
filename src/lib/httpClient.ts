import { apiConfig, assertApiConfig } from "./config";
import { getStoredAuthToken } from "./auth-storage";

type HttpMethod = "GET" | "POST" | "PUT" | "PATCH" | "DELETE";

interface RequestOptions extends Omit<RequestInit, "body"> {
  query?: Record<string, string | number | boolean | undefined>;
  body?: Record<string, unknown> | FormData;
  withAuth?: boolean;
}

export class ApiError extends Error {
  public status: number;
  public data: unknown;

  constructor(message: string, status: number, data?: unknown) {
    super(message);
    this.status = status;
    this.data = data;
  }
}

function buildUrl(path: string, query?: RequestOptions["query"]) {
  assertApiConfig();
  if (!apiConfig.baseUrl) {
    throw new Error("API base URL missing; set NEXT_PUBLIC_API_BASE_URL.");
  }

  const normalizedBase = apiConfig.baseUrl.endsWith("/") ? apiConfig.baseUrl : `${apiConfig.baseUrl}/`;
  const url = new URL(path.replace(/^\//, ""), normalizedBase);

  if (query) {
    Object.entries(query).forEach(([key, value]) => {
      if (value === undefined) return;
      url.searchParams.append(key, String(value));
    });
  }

  return url.toString();
}

function buildHeaders(
  providedHeaders?: HeadersInit,
  body?: RequestOptions["body"],
  withAuth?: boolean,
) {
  const headers = new Headers(providedHeaders);

  if (!(body instanceof FormData)) {
    headers.set("Content-Type", "application/json");
  }

  headers.set("Accept", "application/json");

  if (withAuth) {
    const token = getStoredAuthToken();
    if (token) {
      headers.set("Authorization", `Bearer ${token}`);
    }
  }

  return headers;
}

async function parseResponse(response: Response) {
  const text = await response.text();

  if (!text) {
    return null;
  }

  try {
    return JSON.parse(text);
  } catch {
    return text;
  }
}

export async function httpRequest<TResponse>(
  path: string,
  method: HttpMethod,
  options: RequestOptions = {},
): Promise<TResponse> {
  const { body, query, withAuth, headers, ...rest } = options;
  const url = buildUrl(path, query);
  const requestHeaders = buildHeaders(headers, body, withAuth);
  const payload =
    body instanceof FormData ? body : body ? JSON.stringify(body) : undefined;

  const response = await fetch(url, {
    method,
    headers: requestHeaders,
    body: payload,
    ...rest,
  });

  const data = await parseResponse(response);

  if (!response.ok) {
    throw new ApiError(
      (data as { message?: string })?.message ?? "Request failed",
      response.status,
      data,
    );
  }

  return data as TResponse;
}

export const apiClient = {
  get: <T>(path: string, options?: RequestOptions) =>
    httpRequest<T>(path, "GET", options),
  post: <T>(path: string, options?: RequestOptions) =>
    httpRequest<T>(path, "POST", options),
  put: <T>(path: string, options?: RequestOptions) =>
    httpRequest<T>(path, "PUT", options),
  patch: <T>(path: string, options?: RequestOptions) =>
    httpRequest<T>(path, "PATCH", options),
  delete: <T>(path: string, options?: RequestOptions) =>
    httpRequest<T>(path, "DELETE", options),
};
