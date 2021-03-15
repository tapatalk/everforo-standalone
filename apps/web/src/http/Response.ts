import {get} from 'lodash';
import {AxiosResponse} from 'axios';

export class Response {
    public response?: AxiosResponse;

    constructor(response?: AxiosResponse) {
        this.response = response!;
    }

    public getData(): any | null {
        
        let data = get(this.response!.data, 'data', null);

        if (data) {
            return data;
        }

        data = get(this.response, 'data', null);

        return data;
    }
    /**
     * get http status code, normally 200, sometimes 422 when request validation failed
     */
    public getStatus(): number {
        return get(this.response!, 'status');
    }

    public getHeaders(): any {
        return get(this.response, 'headers', {});
    }

    public getValidationErrors(): Record<string, any> | null {
        return get(this.response!.data, 'errors', null);
    }

    public getDescription(): string {
        return get(this.response!.data, 'description', '');
    }
    /**
     * customized code, when parameters wrong/not found etc.
     */
    public getCode(): string {
        return get(this.response!.data, 'code', '');
    }

}
