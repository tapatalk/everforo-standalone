import {Response} from '@/http/Response';
import RequestMethods from '@/http/RequestMethods';
import {RequestOptionsInterface, ThreadInterface} from '@/helpers/Interfaces';

export default {
    namespaced: true,
    state: {
        id: 0,
        title: '',
        user: {},
        category: {},
        created_at: '',
        content: '',
        posts: [],
        sort: '',
        loadedPage: [],
        is_pin: 0,
    },
    getters: {},
    mutations: {
        setSort(state: any, sort: string) {
            state.sort = sort;
        },
        setTitle(state: any, title: string) {
            state.title = title;
        },
        setPin(state: any, is_pin: number) {
            state.is_pin = is_pin;
        },
        addLoadedPage(state: any, page: number) {
            // technically, then new page either smaller the all loaded page or bigger than all of them
            // it can't in between
            // if there is no loaded page or the new page is bigger than all of the previous loaded pages
            if (!state.loadedPage.length || page > state.loadedPage[state.loadedPage.length - 1]) {
                // never is 'bottom' type
                state.loadedPage.push(page as never);
            // the new page is smaller than all of the previous loaded pages
            } else if(page < state.loadedPage[0]) {
                state.loadedPage.unshift(page);
            }
        },
        clearLoadedPage(state: any) {
            // never is 'bottom' type
            state.loadedPage = [];
        },
    },
    actions: {
        async submit({rootGetters}: { rootGetters: any }, data: FormData) {

            return await RequestMethods.post({
                route: 'api/submit_thread',
                data,
            } as RequestOptionsInterface)
                .then((response) => {

                    const data = (response as Response).getData() as any;

                    if (data && data.thread) {
                        return data.thread;
                    }

                    return response;
                });
        },
        async load({commit, rootGetters}: { commit: any, rootGetters: any },
                         parameters: { thread_id: number, sort: number, page: number, post_id: number }) {
// console.log(parameters);
            return await RequestMethods.fetch({
                route: 'api/thread/{thread_id}/{sort}/{page}/{post_id}',
                param: parameters,
            } as RequestOptionsInterface)
                .then((response: Record<string, any> | Response | null) => {
                    return response;
                });
        },
        async trending({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/thread/feed',
                data,
            } as RequestOptionsInterface)
                .then((response: Record<string, any> | Response | null) => {

                    const data: { success: number} = (response as Response).getData();

                    return data && data.success ? true : false;
                });
        },
        async loadMore({commit, rootGetters}: { commit: any, rootGetters: any },
            parameters: { post_id: number }) {

            return await RequestMethods.fetch({
                route: 'api/load_more/{sort}/{post_id}',
                param: parameters,
            } as RequestOptionsInterface)
            .then((response: Record<string, any> | Response | null) => {
                return response;
            });
        },
        async linkPreview({rootGetters}: { rootGetters: any }, data: FormData) {

            return await RequestMethods.post({
                route: 'apiflask/link_preview',
                data,
            } as RequestOptionsInterface)
                .then((response) => {
                    return response;
                });
        },
    },
};
