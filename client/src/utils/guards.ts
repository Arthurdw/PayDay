export const isBaseApiError = (obj: any): obj is BaseApiError =>
  obj.error !== undefined && typeof obj.error === "string";

export const isUnAuthorizedApiError = (obj: any): obj is BaseApiError =>
  obj.error !== undefined &&
  typeof obj.error === "string" &&
  obj.error === "Unauthorized";

// prettier-ignore
export const isUnprocessableContentApiError = <T>(obj: any): obj is UnprocessableContentApiError<T> =>
    obj.errors !== undefined && typeof obj.errors === "object";
