<template>
    <div
        class="notification-box"
    >
        <a-dropdown
            :trigger="['click']"
            :placement= "isMobile?'bottomCenter':'bottomRight'"
            :overlayStyle="{ postion:'fixed', width: isMobile?'100vw':'auto'}"
            v-on:visibleChange="onVisibleChange"
        >
            <a class="ant-dropdown-link">
                <div
                    class='bell'
                    ref='bell'
                >
                    <Icons type="tongzhi"/>
                    <iframe ref="nsod" src="/nsod" allow="autoplay" />
                </div>
                <div 
                    v-if="newNotification.size"
                    class="new"
                >{{this.newNotification.size}}</div>
            </a>
            <a-menu
                slot="overlay"
                id="nlist"
                :class="['notification-list', {'mobile': isMobile}, {'no-data': notificationList.length == 0}]"
                v-on:scroll.native="onScroll"
                v-touch:swipe="onScroll"
            >
                <a-menu-item
                    v-if="notificationList.length == 0 && !loadingMore"
                    class="notification-item"
                >
                    {{$t('no_more_notifications')}}
                </a-menu-item>
                <a-menu-item
                    v-else
                    class="notification-item"
                    v-for="notification in notificationList"
                    :key="notification.id"
                >
                        <div class="item" v-on:click="onNotificationItemClicked(notification)">
                            <div 
                                v-if="notification.token"
                                class="left"
                            >
                                <UserAvatar
                                    :avatar="notification.token.logo"
                                    :username="notification.token.name"
                                    :is-ban="notification.is_ban"
                                    scale="2"
                                    :online="notification.online"
                                />
                            </div>
                            <div 
                                v-else
                                class="left"
                            >
                                <UserAvatar
                                    :avatar="notification.user && notification.user.photo_url"
                                    :username="notification.user && notification.user.name"
                                    :is-ban="notification.is_ban"
                                    scale="2"
                                    :online="notification.online"
                                />
                            </div>
                            <div class="right">
                                <div
                                    class="content"
                                    v-html="notification.msg"
                                ></div>
                                <div class="time">
                                    <TimeString
                                        :time="notification.created_at"
                                        :is-notification="true"
                                    />
                                </div>
                            </div>
                        </div>
                </a-menu-item>
                <a-menu-item
                    v-if="loadingMore"
                >
                    <a-skeleton avatar :paragraph="{ rows: 1 }" active/>
                </a-menu-item>
            </a-menu>
        </a-dropdown>
        <MemberList
                v-if="showMemberListStatus"
                v-on:close-member-list="showMemberListStatus = false"
                :createSort = "memberListSort"
        />
    </div>
</template>
<script lang="ts">
    import {Component, Ref, Vue, Watch} from 'vue-property-decorator';
    import Echo from 'laravel-echo';
    import {RawLocation} from 'vue-router';
    import {NotificationInterface} from '@/helpers/Interfaces';
    import {IS_MOBILE, SORT_BY_GROUP, SORT_BY_MEMBER_ADMIN} from '@/helpers/Utils';
    import TimeString from '@/components/TimeString.vue';
    import MemberList from '@/components/MemberList.vue';
    import UserAvatar from '@/components/UserAvatar.vue';

    @Component({
        components: {
            TimeString,
            UserAvatar,
            MemberList,
        },
    })
    export default class Notifications extends Vue {

        @Ref('bell')
        readonly bell!: HTMLDivElement;
        @Ref('nsod')
        readonly nsod!: HTMLIFrameElement;

        protected isMobile: boolean = IS_MOBILE;
        protected notificationList: NotificationInterface[] = [];
        protected nlist: HTMLElement | null = null;
        // record the previous user id, reset notificaions list when switching user
        protected previousUserId: number = 0;
        // a flag to display skeleton when first time load
        protected loadingMore: boolean = false;
        // if scroll reached the bottom, try to load more
        protected scrollReachBottom: boolean = false;
        // which page to load
        protected page: number = 1;
        // if there is more to load
        protected loadMore: boolean = true;
        // perpage config, sync with server
        protected perpage: number = 20;
        protected memberListSort: string = '';
        protected showMemberListStatus: boolean = false;

        // indicate how many real time new notificaions, reset to 0 each time user click notification list
        get newNotification(): number {
            return this.$store.state.NotificationList.newNotification;
        }

        get userId(): number {
            return this.$store.state.User.id;
        }
        get adminStatus(): number {
            return this.$store.getters['GroupExtensions/getFeatureStatus']('adminsAndModerators');
        }

        get isAdmin(): string {
            return this.$store.getters['User/isSuperAdmin']() || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 1, this.adminStatus)
                    || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 2, this.adminStatus)
                    || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 3, this.adminStatus);
        }

        @Watch('userId', {immediate: true})
        protected onUserLogin(newId: number) {
            /**
             * listen to the change from socket, display number in read circle when there is new real time message
             */
            if (!newId) {
                return;
            }

            (window as any).io = require('socket.io-client');

            if (typeof (window as any).io !== 'undefined') {
                // first leaving old channels
                if ((window as any).Echo && (window as any).Echo.connector && (window as any).Echo.connector.channels) {
                    const oldChannels = Object.keys((window as any).Echo.connector.channels);
                    
                    if (oldChannels.length) {
                        for (const oldChannel of oldChannels) {
                            (window as any).Echo.leave(oldChannel);
                        }
                    }
                }

                // listen notifications from laravel echo server
                (window as any).Echo = new Echo({
                    broadcaster: 'socket.io',
                    host: process.env.VUE_APP_API_URL,
                    path: '/ws/socket.io',
                });

                (window as any).Echo.private('web-user.' + this.userId)
                                    .listen('ThreadActivitiesEvent', (eventData: NotificationInterface) => {
                                        this.notificationList.unshift(eventData);

                                        this.$store.commit('NotificationList/addNewNotification', eventData.msg);
                                        // hide the skeleton

                                        const postData = {    
                                                            id: eventData.post_id,
                                                            thread_id: eventData.thread_id,
                                                            parent_id: eventData.post_parent_id,
                                                            user_id: eventData.user_id,
                                                            user: {
                                                                name: eventData.user!.name,
                                                                photo_url: eventData.user!.photo_url,
                                                            },
                                                            created_at: eventData.created_at,
                                                            content: eventData.post_content,
                                                            is_new: true,
                                                            ipfs: eventData.ipfs,
                                                            likes: [],
                                                            flags: [],
                                                            attached_files: eventData.attached_files,
                                                        };
                                        this.$store.commit('Post/notifyNewPost', postData);

                                        this.onNew('newpost');
                                    })
                                    .listen('PostActivitiesEvent', (eventData: NotificationInterface) => {

                                        this.notificationList.unshift(eventData);
                                        this.$store.commit('NotificationList/addNewNotification', eventData.msg);
                                        // hide the skeleton
                                        this.onNew('newnotification');
                                    })
                                    .listen('ReportPostEvent', (eventData: NotificationInterface) => {

                                        this.notificationList.unshift(eventData);
                                        this.$store.commit('NotificationList/addNewNotification', eventData.msg);
                                        // hide the skeleton
                                        this.onNew('newnotification');
                                    })
                                    .listen('AirdropActivitiesEvent', (eventData: NotificationInterface) => {
                                        this.notificationList.unshift(eventData);
                                        this.$store.commit('NotificationList/addNewNotification', eventData.msg);
                                        this.onNew('newnotification');
                                    })
                                    .listen('JoinRequestEvent', (eventData: NotificationInterface) => {
                                        this.notificationList.unshift(eventData);
                                        this.$store.commit('NotificationList/addNewNotification', eventData.msg);
                                        this.onNew('newnotification');
                                    });

            
            }
        }

        protected onNew(messageType: string) {
            this.bell.classList.add('ringing');

            this.nsod.contentWindow?.postMessage(messageType, process.env.VUE_APP_DOMAIN as string);

            setTimeout(() => {
                this.bell.classList.remove('ringing');
            }, 2000);
        }

        protected fetchNotifications() {

            if (!this.loadMore) {
                return;
            }

            this.loadMore = false;
            this.loadingMore = true;

            const data = new FormData;

            data.append('page', this.page + '');
            data.append('perpage', this.perpage + '');

            this.$store.dispatch('NotificationList/load', data)
            .then((notificationsData: NotificationInterface[]) => {

                this.page = this.page + 1;

                if (notificationsData.length) {
                    
                    const dataa = []
                    for (let i = 0; i < notificationsData.length; i++) {
                        dataa.push(notificationsData[i].id);
                    }

                    if (this.notificationList.length) {
                        this.notificationList = this.notificationList.concat(notificationsData);
                    } else {
                        this.notificationList = notificationsData;
                    }

                    this.$store.commit('NotificationList/setNotificationList', notificationsData);
                    // after we load a full page, enable load more
                    if (notificationsData.length >= this.perpage) {
                        this.loadMore = true;
                    }

                    // mark notifications read
                    // const data = new FormData;
                    // for (let i = 0; i < notificationsData.length; i++) {
                    //     data.append('notification_ids[]', notificationsData[i].id + '');
                    // }
                    // this.$store.dispatch('NotificationList/read', data);
                }
            })
            .finally(() => {
                this.previousUserId = this.userId;
                this.loadingMore = false;
            });
        }

        protected onVisibleChange(flag: any) {

            this.$store.commit('NotificationList/clearNewNotification');

            // fetch notification list when user click
            if (flag && this.notificationList.length == 0) {
                // when swtich user, reset notification list
                if (this.userId != this.previousUserId) {
                    this.page = 1;
                    this.notificationList = [];
                    this.loadMore = true;
                }

                // if we have not load any notificaions yet
                if (this.page == 1) {
                    this.fetchNotifications();
                }
            }
        }

        protected onScroll(e: MouseEvent) {
            if (!this.nlist) {
                this.nlist = document.getElementById('nlist');
            }

            if (this.nlist) {
                const rect = this.nlist.getBoundingClientRect();
                // when scroll reached bottom, height + scrolltop == scrollheight
                if (Math.ceil(this.nlist.scrollTop + rect.height) >= this.nlist.scrollHeight){
                    this.scrollReachBottom = true;
                } else {
                    this.scrollReachBottom = false;
                }
            }
        }

        @Watch('scrollReachBottom')
        protected onScrollReachBottom(val: boolean) {
            if (val) {
                this.fetchNotifications();
            }
        }

        protected onNotificationItemClicked(notification: NotificationInterface) {
            if (notification.type == 'airdrop') {
            
                this.$store.commit('Profile/showProfile', true);
                this.$store.commit('Profile/setProfileId', this.$store.state.User.id);
            
            } else if (notification.type=='join') {
                this.$store.dispatch('User/getMe');
                const parameters = {
                    group_name: notification.group_name,
                }
                const location = {name: 'group', force: true, params: parameters} as unknown as RawLocation;
                this.$router.push(location).catch(()=>{});
            } else if (notification.type=='join_request') {
                if (this.$route.params.group_name !== notification.group_name) {
                    this.$store.dispatch('User/getMe');
                    const parameters = {
                        group_name: notification.group_name,
                        type: 'join_request',
                    }
                    const location = {name: 'groups', force: true, params: parameters} as unknown as RawLocation;
                    this.$router.push(location).catch(()=>{});
                } else {
                    if (!this.isAdmin) {
                        this.$message.error(this.$t('no_permission') as string);
                        return;
                    }
                    this.showMemberListStatus = true;
                    this.memberListSort = SORT_BY_MEMBER_ADMIN[4];
                }

            } else {
                const parameters = {
                    group_name: notification.group_name,
                    sort: this.$store.getters['User/getSort'](notification.group_name),//get default sort by group name
                    page: 1,
                    post_id: notification.post_id,
                    thread_slug: notification.thread_slug ? notification.thread_slug : notification.thread_id,
                }

                const location = {name: 'thread', force: true, params: parameters} as unknown as RawLocation;
                this.$router.push(location).catch(()=>{});
            }
        }
    }

</script>
<style lang="scss" scoped>

    .ant-dropdown {
        position: fixed;
    }

    .notification-box {
        height: 100%;
        position: relative;

        .ant-dropdown-link {
            display: inline-block;
            padding: 0 var(--p4);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;

            .bell {
                display: flex;
                &.ringing {
                    animation: bell-shaking .5s cubic-bezier(.36, .07,.19,.97) 0s 3;
                }

                iframe {
                    display: none;
                }
                
                .ico {
                    font-size: $font-size4;
                    color: var(--category-color);
                }
            }
        }

        .new {
            $size: 14px; 
            position: absolute;
            top: 16px;
            min-width: $size;
            height: $size;
            line-height: $size;
            text-align: center;
            color: #ffffff;
            overflow: hidden;
            background-color: $error-color;
            font-size: 12px;
            padding: 0 3px;
            border-radius: 7px;
            right: 20%;
        }
    }

    .notification-list {
        width: 464px;
        background-color: var(--navbar-bg);
        box-shadow: $box-shadow;
        padding: 0;
        border-radius: 4px;
        max-height: 606px;
        overflow-y: scroll;
        overflow-x: hidden;

        &.mobile {
            width: 100%;
            height: 60vh;
        }
        margin-top: -3px;

        .notification-item {
            padding: 0;
        }

        &.no-data {    

            overflow-y: hidden;

            .notification-item {            
                padding:  var(--p10) var(--p6) var(--p8);
                color: var(--desc-color);
            }
        }

        & > li .item:hover {
            background-color: var(--hover-bg);
        }

        .notification-title {
            font-size: 16px;
            line-height: 20px;
            font-weight: 500;
            padding: var(--p5) 0 var(--p1) var(--p5);
        }

        li:last-child .item {
            border-bottom: none;
        }

        .item {
            display: flex;
            flex-direction: row;
            padding: var(--p4) var(--p6);
            border-bottom: 1px solid var(--border-color5);

            .left {
                margin-right: var(--p4);
            }

            .right {
                .content {
                    font-size: 14px;
                    line-height: 21px;
                    max-width: 360px;
                    color: var(--font-color1);
                    line-break: auto;
                    white-space: pre-line;
                }

                .time {
                    margin-top: 8px;
                    .time-string {
                        color: var(--desc-color);
                        font-size: 12px;
                        line-height: 16px;
                    }
                }
            }
        }
    }

    @keyframes bell-shaking {
    from {
        transform: rotate(20deg);
    }
    100% {
        transform-origin: center center;
        transform: rotate(-20deg);
    }
}
</style>
