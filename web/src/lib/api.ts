const FALLBACK_API_URL = "http://localhost:8000";

export function normalizeBaseUrl(rawUrl?: string | null): string {
  const trimmed = rawUrl?.trim();
  if (!trimmed) {
    return FALLBACK_API_URL;
  }

  if (/^https?:\/\//i.test(trimmed)) {
    return trimmed.replace(/\/+$/, "");
  }

  return `https://${trimmed.replace(/^\/+|\/+$/g, "")}`;
}

export function buildAbsoluteUrl(path: string, rawBaseUrl?: string | null): string {
  const baseUrl = normalizeBaseUrl(rawBaseUrl ?? process.env.NEXT_PUBLIC_API_BASE_URL);
  return new URL(path, `${baseUrl}/`).toString();
}

const API = normalizeBaseUrl(process.env.NEXT_PUBLIC_API_BASE_URL);

interface RequestOptions {
  method?: string;
  body?: unknown;
  params?: Record<string, string>;
}

interface ApiResponse<T = unknown> {
  data?: T;
  user?: T;
  message?: string;
  errors?: Record<string, string[]>;
}

class ApiClient {
  private baseUrl: string;

  constructor(baseUrl: string) {
    this.baseUrl = baseUrl;
  }

  private async request<T>(path: string, opts: RequestOptions = {}): Promise<T> {
    const url = new URL(path, `${this.baseUrl}/`);
    if (opts.params) {
      Object.entries(opts.params).forEach(([k, v]) => url.searchParams.set(k, v));
    }

    const headers: Record<string, string> = {
      Accept: "application/json",
      "Content-Type": "application/json",
    };

    const res = await fetch(url.toString(), {
      method: opts.method || "GET",
      headers,
      credentials: "include",
      body: opts.body ? JSON.stringify(opts.body) : undefined,
    });

    if (res.status === 204) {
      return {} as T;
    }

    const json = await res.json();

    if (!res.ok) {
      const err = new Error(json.message || "Request failed") as Error & {
        status: number;
        errors: Record<string, string[]> | undefined;
      };
      err.status = res.status;
      err.errors = json.errors;
      throw err;
    }

    return json;
  }

  async csrf(): Promise<void> {
    await fetch(buildAbsoluteUrl("/sanctum/csrf-cookie", this.baseUrl), {
      credentials: "include",
    });
  }

  get<T>(path: string, params?: Record<string, string>): Promise<T> {
    return this.request<T>(path, { params });
  }

  post<T>(path: string, body?: unknown): Promise<T> {
    return this.request<T>(path, { method: "POST", body });
  }

  put<T>(path: string, body?: unknown): Promise<T> {
    return this.request<T>(path, { method: "PUT", body });
  }

  patch<T>(path: string, body?: unknown): Promise<T> {
    return this.request<T>(path, { method: "PATCH", body });
  }

  delete<T>(path: string): Promise<T> {
    return this.request<T>(path, { method: "DELETE" });
  }
}

export const api = new ApiClient(API);
