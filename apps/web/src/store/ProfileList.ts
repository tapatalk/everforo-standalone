import {Response} from '@/http/Response';
import RequestMethods from '@/http/RequestMethods';
import {RequestOptionsInterface, ProfileInterface} from '@/helpers/Interfaces';

export default {
    namespaced: true,
    state: {
        profiles: {},
    },
    getters: {
        
    },
    mutations: {
        addProfile(state: any, profiles: ProfileInterface[]) {
            if (profiles){
                for (let i = 0; i < profiles.length; i++) {
                    state.profiles[profiles[i].user_id] = profiles[i];
                }
            }
        },
    },
    actions: {
        async getProfileList({commit}: { commit: any }, user_id_list: number[]) {
            /**
             * fetch user profile data
             */
            const data = new FormData();

            for (let i = 0; i < user_id_list.length; i++) {
                data.append('user_id_list[]', user_id_list[i] + '');
            }

            commit('addProfile', await RequestMethods.post({
                route: 'api/profile/list',
                data: data,
            } as RequestOptionsInterface).then((response: Record<string, any> | Response | null) => {
                const data: {profiles: ProfileInterface[]} = (response as Response).getData();

                return data.profiles;
            }));
        },
    },
};
