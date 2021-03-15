import RequestMethods from '@/http/RequestMethods';
import {ERC20TokenInterface, RequestOptionsInterface, GroupInterface} from '@/helpers/Interfaces';
import {Response} from '@/http/Response';

interface Statistics {
    members: number;
    threads: number;
}

export default {
    namespaced: true,
    state: {
        id: 0,
        name: '',
        title: '',
        created_at: '',
        owner: 0,
        cover: '',
        logo: '',
        updated_at: '',
        description: '',
        members: 0,
        online_members: 0,
        threads: 0,
        fetchedStatisticGroupName: '',
        no_recommend: 0,
        super_no_recommend: 0,
        erc20_token: {},
        attached_files: {},
        group_admin: {},
        joining: 1,
        visibility: 1,
    },
    getters: {
        cover: (state: any) => {
            return state.cover ? state.cover : '/img/default_cover.png';
        },
    },
    mutations: {
        setCurrentGroup(state: any, data: GroupInterface) {
            Object.assign(state, data);

            if (data && data.erc20_token) {
                state.erc20token = data.erc20_token;
            }
            //update group info ,erc20_token will reset
            // } else {
            //     state.erc20_token = {
            //         id: 0,
            //         group_id: state.id,
            //         name: '',
            //         symbol: '',
            //         balance: '',
            //         decimal: '',
            //     };
            // }
        },
        setGroupIsShow(state: any, isShow: boolean) {
            state.isShow = isShow;
        },
        setGroupStat(state: any, data: Statistics) {
            state.members = data.members;
            state.threads = data.threads;

            state.fetchedStatisticGroupName = state.name;
        },
        setToken(state: any, token: ERC20TokenInterface) {
            state.erc20_token = token;
        },
        setTokenName(state: any, name: string) {
            state.erc20_token.name = name;
        },
        setTokenSymbol(state: any, symbol: string) {
            state.erc20_token.symbol = symbol;
        },
        setTokenBalance(state: any, balance: number) {
            state.erc20_token.balance = balance;
        },
        setTokenDecimal(state: any, decimal: number) {
            state.erc20_token.decimal = decimal;
        },
        setTokenStatus(state: any, status: number) {
            state.erc20_token.status = status;
        },
        setTokenOrderId(state: any, order_id: string) {
            state.erc20_token.order_id = order_id;
        },
        setAttachFilesSetting(state: any, setting: any) {
            state.attached_files = setting;
        }
    },
    actions: {
        async load({commit, rootState, rootGetters}: { commit: any, rootState: any, rootGetters: any }) {
            return await RequestMethods.fetch({
                route: 'api/group/info',
            } as RequestOptionsInterface).then((response) => {
                const data: { group: GroupInterface } = (response as Response).getData();
                commit('setCurrentGroup', data.group);
                return data.group;
            })
        },
        async create({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/group/create',
                data: data,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return (response as Response);
                });
        },
        async update({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/group/update',
                data: data,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return (response as Response);
                });
        },
        async delete({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/group/delete',
                data: data,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return (response as Response);
                });
        },
        async follow({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/follow',
                data: data,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return (response as Response).getData();
                });
        },
        async unfollow({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/unfollow',
                data: data,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return (response as Response).getData();
                });
        },
        async getStat({commit, rootGetters}: { commit: any, rootGetters: any }) {
            commit('setGroupStat', await RequestMethods.fetch({
                route: 'api/group/statistic',
            } as RequestOptionsInterface)
            .then((response) => {
                const data: { group_statistic: Statistics } = (response as Response).getData();
                return data.group_statistic;
            }));
        },
        async createERC20Token({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/erc20token/create',
                data: data
                } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                });
        },
        async deleteERC20Token({rootGetters}: { rootGetters: any }) {
            return await RequestMethods.post({
                route: 'api/erc20token/delete',
                } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                });
        },
        async getAttachedFilesSetting({rootGetters}: { rootGetters: any }) {
            return await RequestMethods.fetch({
                    route: 'api/attached_files/setting',
                } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                });
        },
        async updateAttachedFilesSetting({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                    route: 'api/attached_files/setting',
                    data: data,
                } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return response;
                });
        }
    },
};
