import RequestMethods from "@/http/RequestMethods";
import {RequestOptionsInterface} from "@/helpers/Interfaces";
import {Response} from "@/http/Response";
export default {
    namespaced: true,
    state: {

    },
    getters: {

    },
    mutations: {

    },
    actions: {
        async getGroupPrivacy({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.fetch({
                route: 'api/group/get_feature_setting?group_id=' + urlParams.group_id,
                param: {},
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return (response as Response).getData();
                })
                .catch((er) => {
                    return [];
                });
        },
        async setGroupPrivacy({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.post({
                route: 'api/group/set_feature_setting',
                data: urlParams,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                })
                .catch((er) => {
                    return [];
                });
        },
        async joinRequest({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.post({
                route: 'api/group/join_request',
                data: urlParams,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                })
                .catch((er) => {
                    return [];
                });
        },
        async approveJoin({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.post({
                route: 'api/group/approve_request',
                data: urlParams,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                })
                .catch((er) => {
                    return [];
                });
        },
        async ignoreJoin({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.post({
                route: 'api/group/ignore_request',
                data: urlParams,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                })
                .catch((er) => {
                    return [];
                });
        },
        async getInviteStatus({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.fetch({
                route: 'api/group/get_invite_status',
                param: urlParams,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return (response as Response).getData();
                })
                .catch((er) => {
                    return [];
                });
        },
    }
}

