import {Response} from '@/http/Response';
import RequestMethods from '@/http/RequestMethods';
import {RequestOptionsInterface, UploadedImageUrl, UserInterface} from '@/helpers/Interfaces';
import {convertBase64ToFile, mimeToExt} from '@/helpers/Utils';

export default {
    namespaced: true,
    state: {
        show: false,
        old_id: 0,
        id: 0,
        name: '',
        photo_url: '',
        created_at: '',
        updated_at: '',
        last_seen: '',
        posts: 0,
        likes: 0,
        is_ban: 0,
        online: false,
        is_follow: 0,
        is_admin: 0,
    },
    getters: {
        
    },
    mutations: {
        setIsBan(state: any, flag: number) {
            // set profile ban status
            state.is_ban = flag;
        },
        setIsFollow(state: any, flag: number) {
            // set profile ban status
            state.is_follow = flag;
        },
        setProfileId(state: any, id: number) {
            state.id = id;
        },
        showProfile(state: any, flag: boolean) {
            state.show = flag;
        },
        setProfile(state: any, data: any) {
            Object.assign(state, data);
        },
        updateName(state: any, name: string) {
            state.name = name;
        },
        updateAvatar(state: any, url: string) {
            state.photo_url = url;
        },
    },
    actions: {
        async getProfile({commit, rootGetters}: { commit: any, rootGetters: any }, params: any) {
            commit('setProfile', await RequestMethods.fetch({
                route: 'api/profile/' + params.id  + (params.group_name ? '/'+params.group_name : ''),// update api, join banned info with profile
            } as RequestOptionsInterface).then((response: Record<string, any> | Response | null) => {
                const data: {user: UserInterface} = (response as Response).getData();

                return data.user;
            }));
        },
        async uploadAvatar({rootState, rootGetters}: { rootState: any, rootGetters: any },
            fileData: { dataUrl: string, fileName?: string }) {
            /**
            * dataUrl is a base64 string, we need to convert it to a file object
            */
            const mime = fileData.dataUrl.match(/data:([a-zA-Z0-9]+\/[a-zA-Z0-9-.+]+).*,.*/);
            let mimeType = '';

            if (mime && mime.length) {
                mimeType = mime[1];
            }

            const fileName = fileData.fileName
                            ? fileData.fileName
                            : Date.now() + '-' + rootState.User.id + '_profile' + mimeToExt(mimeType);

            const file = convertBase64ToFile(fileData.dataUrl);

            if (file === false) {
                return {url: '', thumb_url: '', id: 0};
            }

            const data = new FormData();

            data.append('image', file, fileName);

            return await RequestMethods.post({
                route: 'api/avatar/upload',
                data: data,
            } as RequestOptionsInterface).then((response: Response | Record<string, any> | null) => {
                return (response as Response).getData() as UploadedImageUrl;
            });
        },
        async update({rootGetters}: { rootGetters: any }, data: FormData) {
            return await RequestMethods.post({
                route: 'api/user/update_info',
                data: data,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return (response as Response);
                });
        },
        async getUserPosts({rootGetters}: { rootGetters: any }, parameters: {profile_id: number, filter: string, page: number}) {
            return await RequestMethods.fetch({
                route: 'api/profile_posts/{profile_id}/{filter}/{page}',
                param: parameters,
            } as RequestOptionsInterface)
                .then((response: Response | Record<string, any> | null) => {
                    return (response as Response);
                });
        },
    },
};
