import RequestMethods from "@/http/RequestMethods";
import {RequestOptionsInterface} from "@/helpers/Interfaces";
import {Response} from "@/http/Response";


export default {
    namespaced: true,
    state: {

    },
    getters: {

    },
    mutations: {},
    actions: {
        async getMemberList({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.fetch({
                route: 'api/group/memberList/{filter}/{page}',
                param: {page : urlParams.page ? urlParams.page : 1,filter : urlParams.filter},
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                })
                .catch((er) => {
                    return [];
                });
        },
        //get group ban num is use check member list filter
        async getBanNum({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.fetch({
                route: 'api/group/banNum',
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                })
                .catch((er) => {
                    return [];
                });
        },
        async sendMail({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/group/inviteMember',
                data: data,
            } as RequestOptionsInterface)
                .then((response) => {
                    return response;
                });
        },
    }
}
