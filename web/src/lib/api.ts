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
  bodyType?: "json" | "form";
  timeoutMs?: number;
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
    };
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), opts.timeoutMs ?? 15000);
    const bodyType = opts.bodyType ?? "json";
    let body: BodyInit | undefined;

    if (opts.body !== undefined) {
      if (bodyType === "form") {
        headers["Content-Type"] = "application/x-www-form-urlencoded";
        const form = new URLSearchParams();
        Object.entries(opts.body as Record<string, unknown>).forEach(([key, value]) => {
          if (value === undefined || value === null) return;
          form.append(key, String(value));
        });
        body = form.toString();
      } else {
        headers["Content-Type"] = "application/json";
        body = JSON.stringify(opts.body);
      }
    }

    let res: Response;
    try {
      res = await fetch(url.toString(), {
        method: opts.method || "GET",
        headers,
        credentials: "include",
        body,
        signal: controller.signal,
      });
    } catch (error) {
      if (error instanceof DOMException && error.name === "AbortError") {
        throw new Error("Request timed out. Please try again.");
      }
      throw error;
    } finally {
      clearTimeout(timeout);
    }

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
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 15000);
    try {
      await fetch(buildAbsoluteUrl("/sanctum/csrf-cookie", this.baseUrl), {
        credentials: "include",
        signal: controller.signal,
      });
    } catch (error) {
      if (error instanceof DOMException && error.name === "AbortError") {
        throw new Error("Session initialization timed out. Please try again.");
      }
      throw error;
    } finally {
      clearTimeout(timeout);
    }
  }

  get<T>(path: string, params?: Record<string, string>): Promise<T> {
    return this.request<T>(path, { params });
  }

  post<T>(path: string, body?: unknown, options: Omit<RequestOptions, "method" | "body" | "params"> = {}): Promise<T> {
    return this.request<T>(path, { method: "POST", body, ...options });
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
