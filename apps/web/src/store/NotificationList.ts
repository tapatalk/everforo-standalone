import RequestMethods from '@/http/RequestMethods';
import { RequestOptionsInterface, NotificationInterface } from '@/helpers/Interfaces';
import { Response } from '@/http/Response';

export default {
    namespaced: true,
    state: {
        notificationList: [] as NotificationInterface[],
        newNotification: new Set(),
    },
    getters: {},
    mutations: {
        setNotificationList(state: any, data: NotificationInterface[]) {
            state.notificationList = data;
        },
        addNewNotification(state: any, id: number) {
            state.newNotification.add(id);
        },
        clearNewNotification(state: any) {
            state.newNotification = new Set();
        },
    },
    actions: {
        async load({rootState, rootGetters}: { rootState: any, rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/notifications/list',
                data: data,
            } as RequestOptionsInterface)
                .then((response) => {
                    const data: {notifications: NotificationInterface[]} = (response as Response).getData();

                    return data.notifications;
                })
                .catch((er) => {
                    return [];
                });
        },
        async read({rootState, rootGetters}: { rootState: any, rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/notifications/read',
                data: data,
            } as RequestOptionsInterface)
                .then((response) => {
                    const data: {notifications: NotificationInterface[]} = (response as Response).getData();

                    return data.notifications;
                })
                .catch((er) => {
                    return [];
                });
        },
    },
};
