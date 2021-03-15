import RequestMethods from '@/http/RequestMethods';
import {LikeInterface, RequestOptionsInterface} from '@/helpers/Interfaces';
import {Response} from '@/http/Response';

export default {
    namespaced: true,
    state: {
        likeListPostId: 0,
    },
    getters: {},
    mutations: {
        setLikeListPostId(state: any, post_id: number) {
            state.likeListPostId = post_id;
        },
    },
    actions: {
        async likeList({commit, rootGetters}: { commit: any, rootGetters: any }, post_id: number) {
            return await RequestMethods.fetch({
                route: 'api/like/list/{post_id}',
                param: {post_id: post_id},
            } as RequestOptionsInterface).then((response: Record<string, any> | Response | null) => {
                const data: {like_list: LikeInterface[]} = (response as Response).getData();
                return data.like_list;
            });
        },
    },
};
