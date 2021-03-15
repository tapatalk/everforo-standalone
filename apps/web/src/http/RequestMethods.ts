import {defaults} from 'lodash';

import {AxiosRequestConfig} from 'axios';
import {Request} from '@/http/Request';
import {RequestOptionsInterface} from '@/helpers/Interfaces';
import {Response} from '@/http/Response';
import {ResponseError} from '@/http/ResponseError';

export enum RequestOperation {
    REQUEST_CONTINUE = 0,
    REQUEST_SKIP = 1,
}

export default abstract class RequestMethods {

    public static onRequest(): Promise<any> {
        return new Promise<any>((resolve, reject) => {
            // Don't fetch if already fetching. This prevents accidental requests
            // that sometimes occur as a result of a double-click.
            if (false) {
                return resolve(RequestOperation.REQUEST_SKIP);
            }

            return resolve(RequestOperation.REQUEST_CONTINUE);
        });
    }

    // public static onSuccess(): void {
    //
    // }
    //
    // public static onFailure(): void {
    //
    // }


    public static async request(config: AxiosRequestConfig): Promise<Response | Record<string, any> | null> {
        return new Promise((resolve, reject) => {
            return RequestMethods.onRequest()
                .then((status: RequestOperation | boolean) => {

                    switch (status) {
                        case RequestOperation.REQUEST_CONTINUE:
                            break;
                        case RequestOperation.REQUEST_SKIP:
                            return;
                    }

                    // Make the request.
                    return new Request(config)
                        .send()
                        .then((response) => {
                            // this.context.dispatch('onRequestSuccess');
                            // RequestMethods.onSuccess(response);
                            // pass the response body to next then() instead of Response object
                            resolve(response);
                        })
                        .catch((error: ResponseError) => {
                            console.error(error);
                            // todo do something
                            // RequestMethods.onFailure(error, error.response);
                            reject(error);
                        })
                        .catch(reject); // For errors that occur in `onFailure`.
                }).catch(reject);
        });
    }

    public static getUrl(options: RequestOptionsInterface) {

        let route = options.route;
        for (const param in options.param) {
            if (options.param.hasOwnProperty(param)) {
                const re = new RegExp('\{' + param + '\}');
                route = route.replace(re, options.param[param]);
            }
        }

        return '/' + route;
        // return process.env.VUE_APP_API_URL + '/' + route;
    }

    /**
     * add bear token to hedaers
     * @param options
     */
    public static bearerToken(options: RequestOptionsInterface) {
        if (localStorage.getItem('bearer')) {

            if (!options.headers) {
                options.headers = {};
            }

            options.headers['Authorization'] = 'Bearer ' + localStorage.getItem('bearer');
        }
    }

    /**
     * @param options
     */
    public static async fetch(options: RequestOptionsInterface) {

        RequestMethods.bearerToken(options);

        const config: object = defaults(options, {
            url: RequestMethods.getUrl(options),
            method: 'GET',
        });

        return await this.request(config);
    }

    /**
     *
     * @param options
     * @param rootGetters
     */
    public static async post(options: RequestOptionsInterface)
        : Promise<Response | Record<string, any> | null> {

        RequestMethods.bearerToken(options);

        const config: object = defaults(options, {
            url: RequestMethods.getUrl(options),
            method: 'POST',
        });

        return await this.request(config);
    }

    public static async crawler(url: string, params: any) {

        const config: object = {
            url: url,
            method: 'POST',
            params: params,
        };

        return await this.request(config);
    }

}
