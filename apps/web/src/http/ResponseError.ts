import {Response} from '@/http/Response';

export class ResponseError {
    public message: string;
    public response?: Response;
    public stack?: string;

    constructor(message: string, response?: Response) {
        this.message = message;
        this.response = response!;
        this.stack = (new Error()).stack;
    }

    public toString(): string {
        return this.message;
    }

    public getResponse(): Response | undefined {
        return this.response;
    }
}
