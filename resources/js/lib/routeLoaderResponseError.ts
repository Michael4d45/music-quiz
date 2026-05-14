/**
 * Thrown from route loaders for route error boundaries (`isRouteErrorResponse` in the UI).
 * Extends `Error` for `@typescript-eslint/only-throw-error`; adds status fields like React Router's error responses.
 */
export class RouteLoaderResponseError extends Error {
    readonly status: number;
    readonly statusText: string;
    readonly internal = false;
    readonly data: { message: string };

    constructor(status: number, data: { message: string }) {
        const statusText = httpReasonPhrase(status);
        super(`${status} ${statusText}`);
        this.name = 'RouteLoaderResponseError';
        this.status = status;
        this.statusText = statusText;
        this.data = data;
    }
}

function httpReasonPhrase(status: number): string {
    switch (status) {
        case 400:
            return 'Bad Request';
        case 401:
            return 'Unauthorized';
        case 403:
            return 'Forbidden';
        case 404:
            return 'Not Found';
        case 422:
            return 'Unprocessable Entity';
        case 500:
            return 'Internal Server Error';
        default:
            return 'Error';
    }
}
