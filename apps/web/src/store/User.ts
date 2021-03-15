import {Response} from '@/http/Response';
import RequestMethods from '@/http/RequestMethods';
import {GroupInterface, RequestOptionsInterface, UserInterface} from '@/helpers/Interfaces';
import {cloneDeep} from 'lodash';
import {SORT_BY_GROUP} from "@/helpers/Utils";

export default {
    namespaced: true,
    state: {
        id: 0,
        name: 'guest',
        email: '',
        photo_url: '',
        created_at: '',
        updated_at: '',
        joinStatus: 0,
        activate: null,
        groups: [],
        blocked_users: [],
        super_admin: 0,
        ban_group: [],
        settings: {},
        group_invite: {},
    },
    getters: {
        // is user admin is a group
        isAdmin: (state: any) => (group: GroupInterface): boolean => {

            if (state.super_admin) {
                return true;
            }

            // we need user group info first
            if (!group || !group.id || !state.groups.length) {
                return false;
            }

            if (group.owner === state.id){
                return true;
            }

            for (let i = 0; i < state.groups.length; i++) {
                if (state.groups[i].id === group.id && state.groups[i].is_admin) {
                    return true;
                }
            }

            return false;
        },
        // is user admin is a group
        isSuperAdmin: (state: any) => (): boolean => {
            if (state.super_admin) {
                return true;
            }
            return false;
        },
        isGroupAdmin: (state: any) => (group: GroupInterface, level: number, setting: number): boolean => {
            // we need user group info first
            if (!group || !group.id || !group.group_admin.length) {
                return false;
            }

            if (!setting && (level == 2 || level == 3)) {
                return false;
            }

            for (let i = 0; i < group.group_admin.length; i++) {
                if (group.group_admin[i].user_id === state.id && group.group_admin[i].level === level) {
                    return true;
                }
            }
            return false;
        },
        //is user be banned in this group
        isBanned: (state: any) => (group_id: number): boolean => {
            if (state.ban_group.length && state.ban_group.indexOf(group_id) !== -1) {
                return false;
            }
            return true;
        },
        // is user followed a group
        isFollow: (state: any) => (group_id: number): boolean => {
            // we need user group info first
            if (!group_id || !state.groups.length) {
                return false;
            }

            for (let i = 0; i < state.groups.length; i++) {
                if (state.groups[i].id === group_id) {
                    return true;
                }
            }

            return false;
        },
        isBlocked: (state: any) => (user_id: number): boolean => {
            // we need user group info first
            if (state.id == 0 || !user_id || !state.blocked_users.length) {
                return false;
            }

            return state.blocked_users.indexOf(user_id) !== -1
        },
        //get default sort by group name
        getSort: (state: any) => (groupName: string): string => {
            return SORT_BY_GROUP[1];
            
        },
        darkMode: (state: any) => {
            return state.settings ? !!state.settings.dark_mode : false;
        }
    },
    mutations: {
        setCurrentUser(state: any, data: any): void {
            Object.assign(state, data);
        },
        addGroup(state: any, group: any): void {
            let notExists = true;
            for (let i = 0; i < state.groups.length; i++) {
                if (state.groups[i].id == group.id) {
                    notExists = false;
                }
            }
            if (notExists) {
                // deep copy and sort list, to prevent some mistery issue 
                // which causing duplicate groups in home page group list
                state.groups.push(group);
                const groups = cloneDeep(state.groups);
                groups.sort((a: GroupInterface, b: GroupInterface) => (a.id > b.id) ? 1: -1);
                state.groups = groups;
            }
        },
        updateGroup(state: any, group: any): void {
            for (let i = 0; i < state.groups.length; i++) {
                if (state.groups[i].id == group.id) {
                    Object.assign(state.groups[i], group);
                }
            }
        },
        deleteGroup(state: any, group_id: any): void {
            for (let i = 0; i < state.groups.length; i++) {
                if (state.groups[i].id == group_id) {
                    state.groups.splice(i,1);
                    break;
                }
            }
        },
        updateBlockedUser: (state: any, blocked_users: number[]): void => {
            state.blocked_users = blocked_users;
        },
        setDarkMode: (state: any, dark_mode: number | string): void => {
            if (!state.settings) {
                state.settings = {};
            }

            const dm_v = parseInt(dark_mode as string);

            document.cookie = "dm=" + dm_v + ";path=/";

            state.settings.dark_mode = dm_v;
        },
        setLanguage: (state: any, language: string): void => {
            if (!state.settings) {
                state.settings = {};
            }
            state.settings.language = language;
        }
    },
    actions: {
        async getMe({commit, rootGetters}: { commit: any, rootGetters: any }) {

            commit('setCurrentUser', await RequestMethods.fetch({
                route: 'api/me',
            } as RequestOptionsInterface).then((response: Record<string, any> | Response | null) => {
                const data: {user: UserInterface} = (response as Response).getData();
                if (data.user.refreshedToken) {
                    localStorage.setItem('bearer', data.user.refreshedToken);
                }
                return data.user;
            }));
        },
        async checkEmail({commit, rootGetters}: { commit: any, rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/check_email',
                data: data
            } as RequestOptionsInterface).then((response: Record<string, any> | Response | null) => {
                const data: { username: string } = (response as Response).getData();
                const code: any = (response as Response).getCode();
                if (code !== '20000') {
                    return  code;
                }
                return data;
            });
        },
        async loginWithEmail({commit, rootGetters}: { commit: any, rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/login',
                data: data
            } as RequestOptionsInterface).then((response: Record<string, any> | Response | null) => {
                const data: {token: string} = (response as Response).getData();
                return data.token;
            });
        },
        async sendResetPasswordLink({commit, rootGetters}: { commit: any, rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/password/reset_email',
                data: data
            } as RequestOptionsInterface).then((response: Record<string, any> | Response | null) => {
                const data: any = (response as Response).getData();
                return data;
            });
        },
        async resetPassword({commit, rootGetters}: { commit: any, rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/password/reset',
                data: data
            } as RequestOptionsInterface).then((response: Record<string, any> | Response | null) => {
                const data: any = (response as Response).getData();
                return data;
            });
        },
        async registerWithEmail({commit, rootGetters}: { commit: any, rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/register_by_email',
                data: data
            } as RequestOptionsInterface).then((response: Record<string, any> | Response | null) => {
                const data: {token: string} = (response as Response).getData();

                if (data.token) {
                    return data;
                }

                return response;
            });
        },
        async registerAdmin({commit, rootGetters}: { commit: any, rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/register_admin',
                data: data
            } as RequestOptionsInterface).then((response: Record<string, any> | Response | null) => {
                const data: {token: string} = (response as Response).getData();
                if (data.token) {
                    return data;
                }

                return response;
            });
        },
        // async refresh({commit, rootGetters}: { commit: any, rootGetters: any }) {

        //     return await RequestMethods.post({
        //         route: 'api/refresh',
        //     } as RequestOptionsInterface).then((response: Record<string, any> | Response | null) => {
        //         console.log('refresh', response);
        //         const data: {refresh: string} = (response as Response).getData();
        //         if (data && data.refresh){
        //             localStorage.setItem('bearer', data.refresh);
        //         }
        //     });
        // },
        async switchDarkMode({commit, rootGetters}: { commit: any, rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/dark_mode',
                data: data
            } as RequestOptionsInterface).then((response: Record<string, any> | Response | null) => {
                return response;
            });
        },
        async switchLanguage({commit, rootGetters}: { commit: any, rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/switch_language',
                data: data
            } as RequestOptionsInterface).then((response: Record<string, any> | Response | null) => {
                return response;
            });
        },
    },
};
