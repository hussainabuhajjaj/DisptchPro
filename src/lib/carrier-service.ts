import { apiClient } from "./httpClient";

/**
 * Local/offline mode keeps the wizard functional without a backend.
 * Set NEXT_PUBLIC_ALLOW_ONBOARDING_LOCAL="false" to switch to live API calls.
 */
const ALLOW_LOCAL_BYPASS =
  process.env.NEXT_PUBLIC_ALLOW_ONBOARDING_LOCAL === "true";

const LOCAL_DRAFT_KEY = "carrier_draft_local";
const LOCAL_DOCS_KEY = "carrier_draft_docs";

export interface CarrierDraftPayload {
  draftId?: string;
  data: Record<string, unknown>;
}

export interface CarrierDraftResponse {
  draftId: string;
  updatedAt: string;
  data: Record<string, unknown>;
}

export interface DocumentUploadResponse {
  id: string;
  status: "pending" | "approved" | "rejected";
  reviewerNote?: string | null;
  url?: string;
}

export interface DocumentStatusResponse {
  documents: Record<
    string,
    {
      status: "pending" | "approved" | "rejected" | "missing";
      reviewerNote?: string | null;
      fileName?: string;
    }
  >;
}

export async function saveCarrierDraft(payload: CarrierDraftPayload) {
  if (!ALLOW_LOCAL_BYPASS) {
    return apiClient.post<CarrierDraftResponse>("carrier-profiles/draft", {
      body: {
        draftId: payload.draftId,
        data: payload.data,
      },
    });
  }

  const draft: CarrierDraftResponse = {
    draftId: payload.draftId ?? "local",
    updatedAt: new Date().toISOString(),
    data: payload.data,
  };
  if (typeof window !== "undefined") {
    localStorage.setItem(LOCAL_DRAFT_KEY, JSON.stringify(draft));
  }
  return draft;
}

export async function fetchCarrierDraft(draftId: string) {
  if (!ALLOW_LOCAL_BYPASS) {
    return apiClient.get<CarrierDraftResponse>(`carrier-profiles/draft/${draftId}`, {
    });
  }

  if (typeof window === "undefined") {
    return {
      draftId,
      updatedAt: new Date().toISOString(),
      data: {},
    };
  }
  const stored = localStorage.getItem(LOCAL_DRAFT_KEY);
  if (!stored) {
    return {
      draftId,
      updatedAt: new Date().toISOString(),
      data: {},
    };
  }
  return JSON.parse(stored) as CarrierDraftResponse;
}

export async function submitCarrierApplication(draftId: string) {
  if (!ALLOW_LOCAL_BYPASS) {
    return apiClient.post<{ success: boolean }>(
      `carrier-profiles/draft/${draftId}/submit`,
    );
  }
  return { success: true };
}

export async function submitCarrierApplicationWithConsent(
  draftId: string,
  payload: { consent: { signerName: string; signerTitle: string; signedAt: string } },
) {
  if (!ALLOW_LOCAL_BYPASS) {
    return apiClient.post<{ success: boolean }>(
      `carrier-profiles/draft/${draftId}/submit`,
      {
        body: payload,
      },
    );
  }

  void payload;
  return { success: true };
}

export async function uploadCarrierDocument(
  draftId: string,
  documentType: string,
  file: File,
) {
  if (!ALLOW_LOCAL_BYPASS) {
    const formData = new FormData();
    formData.append("file", file);
    formData.append("type", documentType);

    return apiClient.post<DocumentUploadResponse>(
      `carrier-profiles/draft/${draftId}/documents`,
      {
        body: formData,
      },
    );
  }

  void draftId;
  const upload: DocumentUploadResponse = {
    id: `${documentType}-local`,
    status: "pending",
    reviewerNote: "Stored locally only",
    url: undefined,
  };
  if (typeof window !== "undefined") {
    const existing = localStorage.getItem(LOCAL_DOCS_KEY);
    const docs = existing ? (JSON.parse(existing) as Record<string, any>) : {};
    docs[documentType] = { fileName: file.name, status: "pending", reviewerNote: "Stored locally" };
    localStorage.setItem(LOCAL_DOCS_KEY, JSON.stringify(docs));
  }
  return upload;
}

export async function fetchDocumentStatuses(draftId: string) {
  if (!ALLOW_LOCAL_BYPASS) {
    return apiClient.get<DocumentStatusResponse>(
      `carrier-profiles/draft/${draftId}/documents`,
    );
  }

  void draftId;
  if (typeof window === "undefined") {
    return { documents: {} };
  }
  const existing = localStorage.getItem(LOCAL_DOCS_KEY);
  const docs = existing ? (JSON.parse(existing) as Record<string, any>) : {};
  return {
    documents: Object.fromEntries(
      Object.entries(docs).map(([key, value]) => [
        key,
        {
          status: value.status ?? "pending",
          reviewerNote: value.reviewerNote,
          fileName: value.fileName,
        },
      ]),
    ),
  };
}
