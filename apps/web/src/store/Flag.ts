import RequestMethods from '@/http/RequestMethods';
import {FlagInterface, RequestOptionsInterface} from '@/helpers/Interfaces';
import {Response} from '@/http/Response';

export default {
    namespaced: true,
    state: {
        flagPostId: 0,
        flagPostListId: 0,
        blockUser: undefined,
    },
    getters: {},
    mutations: {
        setFlagPostId(state: any, post_id: number) {
            state.flagPostId = post_id;
        },
        setFlagPostListId(state: any, post_id: number) {
            state.flagPostListId = post_id;
        },
        setBlockUser(state: any, flag_with_poster: FlagInterface | undefined) {
            state.blockUser = flag_with_poster;
        }
    },
    actions: {
        async submit({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/flag/post',
                data: data,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    const data: {flag: FlagInterface} = (response as Response).getData();

                    if (data && data.flag) {
                        return data.flag;
                    }
                    return response;
                });
        },
        async flagList({rootGetters}: { rootGetters: any }, post_id: number) {
            return await RequestMethods.fetch({
                route: 'api/flag/post/list/{post_id}',
                param: {post_id: post_id}
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    const data: {flag_list: FlagInterface[]} = (response as Response).getData();

                    return data.flag_list;
                });
        },
        async blockUser({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/block/user',
                data: data,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    const data: {blocked_users: number[]} = (response as Response).getData();

                    return data.blocked_users;
                });
        },
        async unblockUser({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/unblock/user',
                data: data,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    const data: {blocked_users: number[]} = (response as Response).getData();

                    return data.blocked_users;
                });
        },
    },
};
