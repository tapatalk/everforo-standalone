import RequestMethods from '@/http/RequestMethods';
import {GroupFeatureInterface, RequestOptionsInterface} from '@/helpers/Interfaces';
import {Response} from '@/http/Response';

export default {
    namespaced: true,
    state: {
        features: []
    },
    getters: {
        getAirdropStatus: (state: any) => {
            for (let i in state.features) {
                if (state.features[i].feature_name == 'airdrop') {
                    return parseInt(state.features[i].status);
                }
            }

            return 0;
        },
        getAttachmentsStatus: (state: any) => {
            for (let i in state.features) {
                if (state.features[i].feature_name == 'attached_files') {
                    return parseInt(state.features[i].status);
                }
            }

            return 0;
        },
        getFeatureStatus: (state: any) =>(feature_name: string):number => {
            for (let i in state.features) {
                if (state.features[i].feature_name == feature_name) {
                    return parseInt(state.features[i].status);
                }
            }

            return 0;
        }
    },
    mutations: {
        setFeature (state: any, features: GroupFeatureInterface) {
            state.features.push(features);
        },
        clearFeature (state: any) {
            state.features = [];
        },
        setAirdropStatus (state: any, status: string) {
            for (let i in state.features) {
                
                if (state.features[i].feature_name == 'airdrop') {
                    state.features[i].status = status;
                }
            }
        },
        setFeatureStatus (state: any, data: any) {
            for (let i in state.features) {
                if (state.features[i].id == data.id) {
                    state.features[i].status =  parseInt(data.status);
                }
            }
        },
    },
    actions: {
        async fetchExtensionList({commit, rootGetters}: { commit: any, rootGetters: any }) {
            return await RequestMethods.fetch({
                route: 'api/features/getlist',
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                
                return response;
            });
        },
        async enableERC20Token({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/features/enable',
                data: data,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                });
        },
    },
};
