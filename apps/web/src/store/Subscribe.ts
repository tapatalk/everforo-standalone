import RequestMethods from "@/http/RequestMethods";
import {PostInterface, RequestOptionsInterface} from "@/helpers/Interfaces";
import {Response} from "@/http/Response";


export default {
    namespaced: true,
    state: {
        subscribe: [], // temporary data, lost after refresh
        unsubscript: [],
    },
    getters: {
        // is subscribe
        isSubscribe: (state: any) => (thread_id: number, is_subscribe: number): boolean => {
            if (is_subscribe) {
                return state.unsubscript.indexOf(thread_id) === -1;
            } else {
                return state.subscribe.indexOf(thread_id) !== -1;
            }
        },
    },
    mutations: {
        //sert subscribe status
        setSubscribe(state: any, thread_id: number) {
            state.subscribe.push(thread_id);
            const ind = state.unsubscript.indexOf(thread_id);
            if (ind > -1) {
                state.unsubscript.splice(ind, 1);
            }
        },
        setUnSubscribe(state: any, thread_id: number) {
            state.unsubscript.push(thread_id);
            const ind = state.subscribe.indexOf(thread_id);
            if (ind > -1) {
                state.subscribe.splice(ind, 1);
            }

        }
    },
    actions: {
        //user subscribe thread
        async subscribeThread({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.post({
                route: 'api/group/subscribeThread/' + urlParams.thread_id,
                param: {thread_id : urlParams.thread_id},
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                });
        },
        async unsubscribeThread({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.post({
                route: 'api/group/unsubscribeThread/' + urlParams.thread_id + '/' + urlParams.user_id,
                param: {thread_id : urlParams.thread_id, user_id : urlParams.user_id},
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                });
        },
    }
}
