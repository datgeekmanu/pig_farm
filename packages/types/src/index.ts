// Shared API contract types. #0.5 will generate these from the Laravel OpenAPI spec.
// Hand-written placeholder until then.

export type ID = string;

export interface Farm {
  id: ID;
  name: string;
  currency: "EUR" | "XOF";
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}
