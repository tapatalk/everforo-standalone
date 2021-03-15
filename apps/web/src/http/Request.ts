import {Response} from './Response';
import {RequestError} from '@/http/RequestError';
import axios, {AxiosRequestConfig, AxiosResponse, AxiosError} from 'axios';

export class Request {
    public config: AxiosRequestConfig;

    constructor(config: AxiosRequestConfig) {
        this.config = config;
    }

    /**
     * @returns {Promise}
     */
    public send(): Promise<Response> {
        return axios.request(this.config)
            .then((response: AxiosResponse) => {
                return new Response(response);
            }).catch((error: AxiosError) => {
                throw new RequestError(error, new Response(error.response));
            });
    }
}
