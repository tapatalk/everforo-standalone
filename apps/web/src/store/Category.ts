import RequestMethods from '@/http/RequestMethods';
import {CategoryInterface, RequestOptionsInterface} from '@/helpers/Interfaces';
import {Response} from '@/http/Response';

export default {
    namespaced: true,
    state: {
        categories: [] as CategoryInterface[],
    },
    getters: {},
    mutations: {
        setCategories(state: any, data: any) {
            state.categories = data;
        },
    },
    actions: {
        async load({commit, rootGetters}: { commit: any, rootGetters: any }) {
            commit('setCategories', await RequestMethods.fetch({
                route: 'api/get_category',
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                const data:{category: CategoryInterface[]} = (response as Response).getData();
                return data.category;
            }));
        },
        async order({rootGetters}: { rootGetters: any }, data: FormData) {
            await RequestMethods.post({
                route: 'api/category/order',
                data: data,
            } as RequestOptionsInterface)
            .then((response: Response | Record<string, any> | null) => {
                return response;
            })
        }
    },
};
