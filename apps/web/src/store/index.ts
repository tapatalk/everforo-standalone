import Vue from 'vue';
import Vuex from 'vuex';
import Attachment from '@/store/Attachment';
import Category from '@/store/Category';
import Flag from '@/store/Flag';
import Group from '@/store/Group';
import GroupExtensions from '@/store/GroupExtensions';
import Like from '@/store/Like';
import Members from '@/store/Members';
import GroupAdmin from '@/store/GroupAdmin';
import GroupPrivacy from '@/store/GroupPrivacy';
import BanUser from '@/store/BanUser';
import ThreadPin from '@/store/ThreadPin';
import Subscribe from '@/store/Subscribe';
import NotificationList from '@/store/NotificationList';
import Post from '@/store/Post';
import Profile from '@/store/Profile';
import ProfileList from '@/store/ProfileList';
import Thread from '@/store/Thread';
import ThreadList from '@/store/ThreadList';
import User from '@/store/User';
import Token from '@/store/Token';
import AirdropRule from '@/store/AirdropRule';

Vue.use(Vuex);

export default new Vuex.Store({

    state: {
        groupName: '',
        bearer: '',
        noticeMessage: '',
        errorMessage: '',
        showLoginModal: false,
        scrollReachTop: false,
        scrollReachBottom: false,
        showProgressLine: false,
        activatedGroupTab: 1,
    },

    getters: {
        getGroupName: (state): string => {
            return '';
        },
        getNoticeMessage: (state): string => {
            return state.noticeMessage;
        },
        getErrorMessage: (state): string => {
            return state.errorMessage;
        },
    },

    mutations: {
        setGroupName(state: any, groupName: string) {
            state.groupName = groupName;
        },
        setNoticeMessage(state: any, message: string) {
            state.noticeMessage = message;
            state.errorMessage = '';
        },
        setErrorMessage(state: any, message: string) {
            state.errorMessage = message;
            state.noticeMessage = '';
        },
        setShowLoginModal(state: any, flag: boolean) {
            state.showLoginModal = flag;
        },
        setScrollReachTop(state: any, flag: boolean) {
            state.scrollReachTop = flag;
        },
        setScrollReachBottom(state: any, flag: boolean) {
            state.scrollReachBottom = flag;
        },
        setShowProgressLine(state: any, flag: boolean) {
            state.showProgressLine = flag;
        },
        setActivatedGroupTab(state: any, key: number) {
            state.activatedGroupTab = key;
        }
    },

    modules: {
        Attachment,
        Category,
        Flag,
        Group,
        GroupExtensions,
        Like,
        Members,
        GroupAdmin,
        GroupPrivacy,
        NotificationList,
        Post,
        Profile,
        ProfileList,
        Thread,
        ThreadList,
        User,
        Token,
        AirdropRule,
        BanUser,
        ThreadPin,
        Subscribe
    },

    actions: {},
});
