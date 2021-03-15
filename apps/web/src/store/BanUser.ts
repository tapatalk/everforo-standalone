import RequestMethods from "@/http/RequestMethods";
import {PostInterface, RequestOptionsInterface} from "@/helpers/Interfaces";
import {Response} from "@/http/Response";


export default {
    namespaced: true,
    state: {
        banned_users: [], // temporary data, lost after refresh
        unbanned_users: [],
    },
    getters: {
        // is ban
        isBan: (state: any) => (user_id: number, is_ban: number): boolean => {
            // if (state.ban_users || state.un_ban_users) {
            //     if (state.ban_users[group_id]){
            //         let group_data = new Set(state.ban_users[group_id]);
            //         if (group_data.has(user_id)) {
            //             is_ban = 1;
            //         }
            //     }
            //     if (state.un_ban_users[group_id]){
            //         let group_un_data = new Set(state.un_ban_users[group_id]);
            //         if (group_un_data.has(user_id)) {
            //             is_ban = 0;
            //         }
            //     }
            // }
            // console.log(group_id);

            // if (is_ban > 0) {
            //     return true;
            // }
            // return false;

            if (is_ban) {
                return state.unbanned_users.indexOf(user_id) === -1;
            } else {
                return state.banned_users.indexOf(user_id) !== -1;
            }
        },
    },
    mutations: {
        setBanList(state: any, user_id: number) {
            // set profile ban status
            // console.log(state.ban_users);
            // console.log(state.un_ban_users);
            // if(state.un_ban_users[params.group_id]) {
            //     let group_un_data = new Set(state.un_ban_users[params.group_id]);
            //     if (group_un_data.has(params.user_id)) {
            //         group_un_data.delete(params.user_id);
            //         state.un_ban_users[params.group_id] = group_un_data;
            //     }
            // }
            // if(state.ban_users[params.group_id]) {
            //     var group_data = new Set(state.ban_users[params.group_id]);
            // }else {
            //     var group_data = new Set();
            // }
            // group_data.add(params.user_id);
            // state.ban_users[params.group_id] = group_data;

            state.banned_users.push(user_id);
            
            const ind = state.unbanned_users.indexOf(user_id);
            if (ind > -1) {
                state.unbanned_users.splice(ind, 1);
            }
        },
        setUnBanList(state: any, user_id: number) {
            // console.log(state.ban_users);
            // console.log(state.un_ban_users);
            // // set profile un ban status.
            // if(state.ban_users[params.group_id]) {
            //     let group_data = new Set(state.ban_users[params.group_id]);
            //     if (group_data.has(params.user_id)) {
            //         group_data.delete(params.user_id);
            //         state.ban_users[params.group_id] = group_data;
            //     }
            // }
            // if(state.un_ban_users[params.group_id]) {
            //     var group_un_data = new Set(state.un_ban_users[params.group_id]);
            // }else {
            //     var group_un_data = new Set();
            // }
            // group_un_data.add(params.user_id);
            // state.un_ban_users[params.group_id] = group_un_data;
            state.unbanned_users.push(user_id);
            
            const ind = state.banned_users.indexOf(user_id);
            if (ind > -1) {
                state.banned_users.splice(ind, 1);
            }

        },
        clearBanList(state: any) {
            state.banned_users = [];
            state.unbanned_users = [];
        }
    },
    actions: {
        async banUser({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.post({
                route: 'api/group/banUser/' + urlParams.user_id,
                param: {user_id : urlParams.user_id},
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                })
                .catch((er) => {
                    return [];
                });
        },
        async unBanUser({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.post({
                route: 'api/group/unBanUser/' + urlParams.user_id,
                param: {user_id : urlParams.user_id},
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
