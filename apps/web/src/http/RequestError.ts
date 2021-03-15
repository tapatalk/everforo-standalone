import {Response} from './Response';

export class RequestError {
    public message: string;
    public error: any;
    public response: Response;
    public stack?: string;

    constructor(error: any, response: Response) {
        this.error = error;
        this.response = response;
        this.stack = (new Error()).stack;
        this.message = error.message;
    }

    public toString(): string {
        return this.message;
    }

    public getError(): any {
        return this.error;
    }

    public getResponse(): Response {
        return this.response;
    }
}
