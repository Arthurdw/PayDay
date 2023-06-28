type BaseErrorValue = string;
type UnprocessableContentErrorValue<T> = {
  [K in keyof T]: BaseErrorValue[];
};

interface BaseApiError {
  error: BaseErrorValue;
}

interface UnprocessableContentApiError<T> {
  errors: UnprocessableContentErrorValue<T>;
}

type ApiError<T> = BaseApiError | UnprocessableContentApiError<T>;
