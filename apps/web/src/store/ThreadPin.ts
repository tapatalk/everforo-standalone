import RequestMethods from "@/http/RequestMethods";
import {PostInterface, RequestOptionsInterface} from "@/helpers/Interfaces";
import {Response} from "@/http/Response";


export default {
    namespaced: true,
    state: {
        pin: [], // temporary data, lost after refresh
        unpin: [],
        pin_user:'',
    },
    getters: {
        // is pin
        isPin: (state: any) => (thread_id: number, is_pin: number): boolean => {
            if (is_pin) {
                return state.unpin.indexOf(thread_id) === -1;
            } else {
                return state.pin.indexOf(thread_id) !== -1;
            }
        },
        pinUser: (state: any) => (thread_id: number): boolean => {
            return state.pin.indexOf(thread_id) !== -1;
        },
    },
    mutations: {
        //sert pin status
        setPin(state: any, thread_id: number) {
            state.pin.push(thread_id);
            const ind = state.unpin.indexOf(thread_id);
            if (ind > -1) {
                state.unpin.splice(ind, 1);
            }
        },
        setUnpin(state: any, thread_id: number) {
            state.unpin.push(thread_id);
            const ind = state.pin.indexOf(thread_id);
            if (ind > -1) {
                state.pin.splice(ind, 1);
            }

        },
        setPinUser(state: any, user: string) {
            state.pin_user = user;
        },
    },
    actions: {
        //admin pin thread
        async pin({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.post({
                route: 'api/group/pin/' + urlParams.thread_id,
                param: {thread_id : urlParams.thread_id},
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                });
        },
        async unpin({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.post({
                route: 'api/group/unpin/' + urlParams.thread_id,
                param: {thread_id : urlParams.thread_id},
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                });
        },
        async pinStatus({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.post({
                route: 'api/group/pin_status/' + urlParams.thread_id,
                param: {thread_id : urlParams.thread_id},
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                });
        },
    }
}
