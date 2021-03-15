import RequestMethods from '@/http/RequestMethods';
import {RequestOptionsInterface, ThreadInterface} from '@/helpers/Interfaces';
import {Response} from '@/http/Response';
import {SORT_BY} from '@/helpers/Utils';

export default {
    namespaced: true,
    state: {
        previousGroup: '',
        previousSort: SORT_BY[0],
        previousCategory: -1,
        loadedCategoryPage: [],
        threadList: [] as ThreadInterface[],
    },
    getters: {},
    mutations: {
        setPreviousGroup(state: any, groupName: string) {
            state.previousGroup = groupName;
        },
        setPreviousSort(state: any, sort: string) {
            state.previousSort = sort;
        },
        setPreviousCategory(state: any, categoryId: number) {
            state.previousCategory = categoryId;
        },
        addLoadedCategoryPage(state: any, page: number) {
            // never is 'bottom' type
            state.loadedCategoryPage.push(page as never);
        },
        emptyLoadedCategoryPage(state: any) {
            // never is 'bottom' type
            state.loadedCategoryPage = [];
        },
        setThreadList(state: any, data: ThreadInterface[]) {
            state.threadList = data;
        },
    },
    actions: {
        async load({rootGetters}: { rootGetters: any }, params: Record<string, any>) {
            return await RequestMethods.fetch({
                route: 'api/category/{sort}/{page}/{category_id}',
                param: params,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {

                    const data: { threads: ThreadInterface[] } = (response as Response).getData();
                    const code: any = (response as Response).getCode();
                    if (data.threads) {
                        return data.threads;
                    } else {
                        return code;
                    }
                })
                .catch((er) => {
                    return [];
                });
        },
        async feed({rootGetters}: { rootGetters: any }, urlParams : Record<string, any>) {
            return await RequestMethods.fetch({
                route: 'api/groups/{page}/{my_group}',
                param: { my_group: urlParams.filter ? 1 : 0, page : urlParams.page ? urlParams.page : 1},
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {

                    const data: {feed: ThreadInterface[]} = (response as Response).getData();

                    return data.feed;
                })
                .catch((er) => {
                    return [];
                });
        },
    },
};
