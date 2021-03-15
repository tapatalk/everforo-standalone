import RequestMethods from '@/http/RequestMethods';
import {LikeInterface, PostInterface, RequestOptionsInterface, ThreadInterface} from '@/helpers/Interfaces';
import {Response} from '@/http/Response';

export default {
    namespaced: true,
    state: {
        new_post: {},
        new_like: {},
        folded: [],
    },
    getters: {},
    mutations: {
        notifyNewPost(state: any, post: PostInterface) {
            state.new_post = post;
        },
        clearNewPost(state: any) {
            state.new_post = {};
        },
        addFolded(state: any, post_id: number) {
            state.folded.push(post_id);
        },
        removeFolded(state: any, post_id: number) {
            let i = state.folded.indexOf(post_id);

            while (i !== -1) {
                state.folded.splice(i, 1);

                i = state.folded.indexOf(post_id);
            }
        },
        // notifyNewLike(state: any, like: LikeInterface) {
        //     state.new_like = like;
        // },
        // clearNewLike(state: any) {
        //     state.new_post = {};
        // },
    },
    actions: {
        async submit({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/submit_post',
                data: data,
            } as RequestOptionsInterface)
                .then((response) => {

                    const data: { post: PostInterface } = (response as Response).getData();

                    if(data && data.post) {
                        return data.post;
                    }

                    return response;
                });
        },
        async get({commit, rootGetters}: { commit: any, rootGetters: any }, post_id: number) {
            return await RequestMethods.fetch({
                route: 'api/post/{post_id}',
                param: {post_id: post_id},
            } as RequestOptionsInterface).then((response: Record<string, any> | Response | null) => {
                const data: {post: PostInterface} = (response as Response).getData();
                return data.post;
            });
        },
        async edit({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/edit_post',
                data: data,
            } as RequestOptionsInterface)
                .then((response) => {

                    const data: { post: PostInterface, thread?: ThreadInterface } = (response as Response).getData();
                    // if has error code ,show error message
                    if (data && JSON.stringify(data) !== "{}") {
                        return data;
                    }
                    return response;
                });
        },
        async delete({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/delete_post',
                data: data,
            } as RequestOptionsInterface)
                .then((response) => {

                    const data: { post: PostInterface } = (response as Response).getData();
                    if (data && data.post) {
                        return data.post;
                    }
                    return response;
                });
        },
        async like({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/like_post',
                data: data,
            } as RequestOptionsInterface)
                .then((response) => {

                    const data: { like: LikeInterface } = (response as Response).getData();
                    if (data && data.like) {
                        return data.like;
                    }
                    return response;
                });
        },
        async unlike({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/unlike_post',
                data: data,
            } as RequestOptionsInterface)
                .then((response) => {

                    const data: { like: LikeInterface } = (response as Response).getData();

                    if (data && data.like) {
                        return data.like;
                    }
                    return response;
                });
        },
        async undelete({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/un_delete_post',
                data: data,
            } as RequestOptionsInterface)
                .then((response) => {

                    const data: { post: PostInterface } = (response as Response).getData();
                    if (data && data.post) {
                        return data.post;
                    }
                    return response;
                });
        },
    },
};
