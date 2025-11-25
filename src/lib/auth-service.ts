import { apiClient } from "./httpClient";
import {
  clearAuthToken,
  getStoredAuthToken,
  storeAuthToken,
} from "./auth-storage";

const AUTH_ENDPOINTS = {
  login: "auth/login",
  register: "auth/register",
  me: "auth/me",
  logout: "auth/logout",
};

export interface AuthUser {
  id: string;
  email: string;
  name?: string;
}

interface AuthResponse {
  token: string;
  user: AuthUser;
}

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterCredentials extends LoginCredentials {
  name?: string;
}

export async function loginWithCredentials(credentials: LoginCredentials) {
  const response = await apiClient.post<AuthResponse>(AUTH_ENDPOINTS.login, {
    body: { ...credentials },
  });
  storeAuthToken(response.token);
  return response.user;
}

export async function registerWithCredentials(
  credentials: RegisterCredentials,
) {
  const response = await apiClient.post<AuthResponse>(
    AUTH_ENDPOINTS.register,
    {
      body: { ...credentials },
    },
  );
  storeAuthToken(response.token);
  return response.user;
}

export async function fetchCurrentUser() {
  const token = getStoredAuthToken();
  if (!token) {
    return null;
  }

  return apiClient.get<AuthUser>(AUTH_ENDPOINTS.me, {
    withAuth: true,
  });
}

export async function logoutFromApi() {
  try {
    await apiClient.post(
      AUTH_ENDPOINTS.logout,
      { withAuth: true },
    );
  } catch (error) {
    // best-effort logout – swallow network/API errors
  } finally {
    clearAuthToken();
  }
}
