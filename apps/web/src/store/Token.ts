import RequestMethods from '@/http/RequestMethods';
import {RequestOptionsInterface} from '@/helpers/Interfaces';
import {Response} from '@/http/Response';

export default {
    namespaced: true,
    state: {
        createCountDown: 90,
    },
    getters: {},
    mutations: {
        setCreateCountDown(state: any, num: number) {
            state.createCountDown = num;
        }
    },
    actions: {
        async fetchImportList({commit, rootGetters}: { commit: any, rootGetters: any }) {
            return await RequestMethods.fetch({
                route: 'api/erc20token/getimportlist/0',
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                
                return response;
            });
        },
        async import({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/erc20token/createimport',
                data: data,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                });
        },
        async fetchAssetList({commit, rootGetters}: { commit: any, rootGetters: any }) {
            return await RequestMethods.fetch({
                route: 'api/erc20token/getmyassets',
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                
                return response;
            });
        },
        async fetchWindrawDetail({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/withdraw/getdetail',
                data: data,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                });
        },
        async createWindrawRequest({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/withdraw/create',
                data: data,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                });
        },
        async cancelWindrawRequest({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/order/cancel',
                data: data,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                });
        },
        async fetchCreateTokenPrice({commit, rootGetters}: { commit: any, rootGetters: any }) {
            return await RequestMethods.fetch({
                route: 'api/get_price/create_token',
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                
                return response;
            });
        }
    },
};
