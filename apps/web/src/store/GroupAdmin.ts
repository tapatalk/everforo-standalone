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
        async getGroupMember({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.fetch({
                route: 'api/group/admin_setting/group_member',
                param: {},
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                })
                .catch((er) => {
                    return [];
                });
        },
        async addGroupMember({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.post({
                route: 'api/group/admin_setting/add_admin',
                data: {'admin_id': urlParams.admin_id},
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                })
                .catch((er) => {
                    return [];
                });
        },
        async delGroupMember({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.post({
                route: 'api/group/admin_setting/del_admin',
                data: {'admin_id': urlParams.admin_id},
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                })
                .catch((er) => {
                    return [];
                });
        },
        async addGroupModerator({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.post({
                route: 'api/group/admin_setting/add_moderator',
                data: {'admin_id': urlParams.admin_id},
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                })
                .catch((er) => {
                    return [];
                });
        },
        async delGroupModerator({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.post({
                route: 'api/group/admin_setting/del_moderator',
                data: {'admin_id': urlParams.admin_id},
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                })
                .catch((er) => {
                    return [];
                });
        },
        async changeOwner({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.post({
                route: 'api/group/admin_setting/change_owner',
                data: {'admin_id': urlParams.admin_id},
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                })
                .catch((er) => {
                    return [];
                });
        },
        async selectGroupMember({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.fetch({
                route: 'api/group/admin_setting/select_member?name=' + urlParams.name,
                param: {'name': urlParams.name},
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                })
                .catch((er) => {
                    return [];
                });
        },
        async selectGroupAdmin({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.fetch({
                route: 'api/group/admin_setting/select_admin?name=' + urlParams.name,
                param: {'name': urlParams.name},
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                })
                .catch((er) => {
                    return [];
                });
        },
    }
}

