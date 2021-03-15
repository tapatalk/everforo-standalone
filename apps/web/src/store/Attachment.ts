import RequestMethods from '@/http/RequestMethods';
import {RequestOptionsInterface, UploadedImageUrl} from '@/helpers/Interfaces';
import {Response} from '@/http/Response';
import {convertBase64ToFile, mimeToExt} from '@/helpers/Utils';

export default {
    namespaced: true,
    state: {},
    getters: {},
    mutations: {},
    actions: {
        async uploadAttach({rootState, rootGetters}: { rootState: any, rootGetters: any },
                           fileData: { dataUrl: string, fileName?: string }) {

            const mime = fileData.dataUrl.match(/data:([a-zA-Z0-9]+\/[a-zA-Z0-9-.+]+).*,.*/);
            let mimeType = '';

            if (mime && mime.length) {
                mimeType = mime[1];
            }

            const fileName = fileData.fileName
                ? fileData.fileName
                : Date.now() + '-' + rootState.User.id + mimeToExt(mimeType);

            const file = convertBase64ToFile(fileData.dataUrl);

            if (file === false) {
                return {url: '', thumb_url: '', id: 0};
            }

            const data = new FormData();

            data.append('image', file, fileName);

            return await RequestMethods.post({
                route: 'api/attach/upload',
                data: data,
            } as RequestOptionsInterface).then((response: Response | Record<string, any> | null) => {
                return (response as Response).getData() as UploadedImageUrl;
            });
        },
        async uploadGroupPic({rootState, rootGetters}: { rootState: any, rootGetters: any },
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
                : Date.now() + '-' + rootState.User.id + mimeToExt(mimeType);

            const file = convertBase64ToFile(fileData.dataUrl);

            if (file === false) {
                return {url: '', thumb_url: '', id: 0};
            }

            const data = new FormData();

            data.append('image', file, fileName);

            return await RequestMethods.post({
                route: 'api/upload_group_pic',
                data: data,
            } as RequestOptionsInterface).then((response: Response | Record<string, any> | null) => {
                return (response as Response).getData() as UploadedImageUrl;
            });
        },
        async uploadAttachedFiles({rootState, rootGetters}: { rootState: any, rootGetters: any }, file: File) {

            const data = new FormData();

            data.append('attached_file', file);

            return await RequestMethods.post({
                route: 'api/attached_file/upload',
                data: data,
            } as RequestOptionsInterface).then((response: Response | Record<string, any> | null) => {
                return response;
            });
        },
    },
};
