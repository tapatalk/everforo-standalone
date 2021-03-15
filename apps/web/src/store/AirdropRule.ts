import RequestMethods from '@/http/RequestMethods';
import {RequestOptionsInterface} from '@/helpers/Interfaces';
import {Response} from '@/http/Response';

export default {
    namespaced: true,
    state: {
        
    },
    getters: {},
    mutations: {
        
    },
    actions: {
        async create({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/airdrop/create',
                data: data,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                
                return response;
            });
        },
        async edit({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/airdrop/edit',
                data: data,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                
                return response;
            });
        },
        async fetchList({commit, rootGetters}: { commit: any, rootGetters: any }) {
            return await RequestMethods.fetch({
                route: 'api/airdrop/getlist',
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                
                return response;
            });
        },
        async pause({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/airdrop/pause',
                data: data,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                
                return response;
            });
        },
        async resume({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/airdrop/resume',
                data: data,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                
                return response;
            });
        },
        async delete({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/airdrop/delete',
                data: data,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                
                return response;
            });
        },
        async oneTimeDrop({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/onetime_airdrop/create',
                data: data,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                
                return response;
            });
        }
    },
};
