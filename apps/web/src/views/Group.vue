<template>
    <a-layout class="main-layout">
        <GroupSider
            v-on:show-manage-group="onShowGroupManage"
        />
        <a-layout-content class="main-content">
            <div class="top">
                <div class="page-title">
                    <span class="t">{{currentCategory}}</span>
                    <Sort
                        :selected-sort="sort"
                        :sort-by-data="sortByData"
                    />
                </div>
                <QuickTopic/>
            </div>
            <ThreadList/>
        </a-layout-content>
        <GroupManage
            v-if="showManageGroup"
            v-on:close-create-group="showManageGroup = false"
            :default-key="defaultKey"
        />
        <CreateGroupSuccess
            v-if="showCreateGroupSuccessConfirm"
            v-on:confirm="onShowSetting"
            v-on:cancel="onCancel"
        />
        <MemberList
                v-if="showMemberListStatus"
                v-on:close-member-list="showMemberListStatus = false"
                :createSort = "memberListSort"
        />
        <GroupJoinRequest
                v-if="showGroupJoinFlag"
                v-on:close-invite-member="showGroupJoinFlag=false"
        />
        <AdminLogin
            v-if="groupAdminFlag"
        />
    </a-layout>
</template>
<script lang="ts">
    import {Component, Vue, Watch} from 'vue-property-decorator';
    import {RawLocation, Route} from 'vue-router';
    import {Response} from '@/http/Response';
    import {
        ALL_POST_ID,
        CATEGORY_SYMBOL,
        SORT_BY_GROUP,
        SORT_BY_MEMBER_ADMIN,
        SORT_BY_SUBSCRIBE,
        SORT_BY_THREAD
    } from '@/helpers/Utils';
    import GroupSider from '@/components/GroupSider.vue';
    import GroupManage from '@/components/GroupManage.vue';
    import QuickTopic from '@/components/QuickTopic.vue';
    import CreateGroupSuccess from '@/components/CreateGroupSuccess.vue';
    import MemberList from '@/components/MemberList.vue';
    import Sort from '@/components/Sort.vue';
    import ThreadList from '@/components/ThreadList.vue';
    import GroupJoinRequest from '@/components/GroupJoinRequest.vue';
    import AdminLogin from '@/components/AdminLogin.vue';
    import {CategoryInterface, GroupFeatureInterface} from "@/helpers/Interfaces";

    @Component<Group>({
        beforeRouteUpdate(to: Route, from: Route, next: any) {
            if (to.params.sort) {
                this.sort = to.params.sort;
            }

            next();
        },
        components: {
            GroupSider,
            GroupManage,
            QuickTopic,
            Sort,
            ThreadList,
            CreateGroupSuccess,
            MemberList,
            GroupJoinRequest,
            AdminLogin,
        },
    })
    export default class Group extends Vue {

        protected currentCategory: string = '';
        protected showManageGroup: boolean = false;
        protected sortByData:string[] = SORT_BY_THREAD;
        protected defaultKey: string = '1';
        protected memberListSort: string = '';
        protected showCreateGroupSuccessConfirm: boolean = false;
        protected showMemberListStatus: boolean = false;
        protected showGroupJoinFlag: boolean = false;

        public sort: string = SORT_BY_GROUP[1]; //this one is public because we need access in beforeRouteUpdate

        get categories(): CategoryInterface[] {
            return this.$store.state.Category.categories;
        }

        get categoryId(): number {
            return this.$route.params.category_id ? parseInt(this.$route.params.category_id) : ALL_POST_ID;
        }

        get groupId(): number {
            return this.$store.state.Group.id;
        }

        get userId(): number {
            return this.$store.state.User.id;
        }

        get routeType(): any {
            return this.$route.params.type;
        }

        get groupFeatures(): any[] {
            return this.$store.state.GroupExtensions.features;
        }

        get adminStatus(): number {
            return this.$store.getters['GroupExtensions/getFeatureStatus']('adminsAndModerators');
        }

        get groupAdminFlag(): boolean {
            return this.$store.state.Group.group_admin.length > 0 ? false : true;
        }

        get isFollow(): boolean {
            return this.$store.getters['User/isFollow'](this.$store.state.Group.id);
        }

        get groupJoining(): number {
            if (this.$store.getters['GroupExtensions/getFeatureStatus']('GroupLevelPermission')) {
                return this.$store.state.Group.joining;
            } else {
                return 1;
            }
        }

        get joinStatus(): boolean {
            return this.$store.state.User.joinStatus ? true : false;
        }

        get userStatus(): boolean {
            if (!this.userId) {
                return false;
            }
            if (this.isFollow) {
                return false;
            }
            if (this.groupJoining == 2 && !this.joinStatus) {
                return true;
            }
            return false;
        }

        get isAdmin():boolean {
            return this.$store.getters['User/isSuperAdmin']() || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 1, this.adminStatus) 
            || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 2, this.adminStatus);
        }

        @Watch('userId')
        protected onUserIdChange() {
            if (this.$route.params.type === 'join' && this.$store.state.User.id && this.$route.query.email == this.$store.state.User.email) {
                this.follow();
            }
            if (this.userStatus) {
                this.showGroupJoinFlag = true;
            }
        }

        protected created() {
            if (this.userStatus) {
                this.showGroupJoinFlag = true;
            }

            if (this.$route.params.sort) {
                this.sort = this.$route.params.sort;
            }
            if (this.$route.name === 'register' || this.$route.name === 'passwordreset')
            {
                this.$store.commit('setShowLoginModal', true);
            } else if (this.$route.params.type) {
                if (this.$route.params.type === 'join') {
                    if (this.$store.state.User.id) {
                        if (this.$route.query.email == this.$store.state.User.email) {
                            this.follow();
                        } else {
                            this.$message.error(this.$t('invite_auto_follow_error') as string);
                        }
                    } else {
                        this.$store.commit('setShowLoginModal', true);
                    }
                } else if(this.$route.params.type === 'create') {
                    if (this.isAdmin) {
                        this.showCreateGroupSuccessConfirm = true;
                    }
                } else if(this.$route.params.type === 'join_request') {
                    if (this.isAdmin || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 3, this.adminStatus)) {
                        this.showMemberListStatus = true;
                        this.memberListSort = SORT_BY_MEMBER_ADMIN[4];
                    } else {
                        this.$message.error(this.$t('no_permission') as string);
                    }
                } else {
                    this.$router.push({
                            name: '404',
                        } as unknown as RawLocation);
                }
            }
        }

        @Watch('groupId')
        protected onGroupIdChange(newId:any, oldId:number)
        {
            if (this.$route.params.type && this.$route.params.type === 'create' && newId > oldId && this.isAdmin) {
                this.showCreateGroupSuccessConfirm = true;
            }
            // if (this.$route.params.type && this.$route.params.type === 'join_request' && this.isAdmin
            //         && (this.isAdmin || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 3, this.adminStatus))) {
            //     this.showMemberListStatus = true;
            //     this.memberListSort = SORT_BY_MEMBER_ADMIN[4];
            // }
        }

        @Watch('routeType')
        protected onRouteTypeChange()
        {
            if (this.$route.params.type && this.$route.params.type === 'create' && this.isAdmin) {
                this.showCreateGroupSuccessConfirm = true;
            } else if (this.$route.params.type && this.$route.params.type === 'join_request') {
                if (this.isAdmin && (this.isAdmin
                        || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 3, this.adminStatus))) {
                    this.showMemberListStatus = true;
                    this.memberListSort = SORT_BY_MEMBER_ADMIN[4];
                } else {
                    this.$message.error(this.$t('no_permission') as string);
                }
            }
        }

        protected onShowSetting() {
            this.showCreateGroupSuccessConfirm = false;
            this.defaultKey = '2';
            this.$store.commit('setActivatedGroupTab', '2');
            this.onShowGroupManage();
        }

        protected onCancel() {
            this.showCreateGroupSuccessConfirm = false;
        }

        protected follow() {
            this.$store.dispatch('Group/follow')
                            .then((response: { success: number }) => {
                                if (response.success) {
                                    this.$store.dispatch('User/getMe');
                                    this.$store.dispatch('Group/getStat');
                                    this.$message.info(this.$t('invite_auto_follow_success') as string);
                                }
                            });
        }

        protected onShowGroupManage() {
        
            this.$store.dispatch('GroupExtensions/fetchExtensionList')
            .then((response: Response) => {
                const data: {features: GroupFeatureInterface[]} = response.getData();
                if (data && data.features && data.features.length) {
                    // sort group extensions, filter it
                    const extensionSort = ["adminsAndModerators", "GroupLevelPermission", "attached_files", "subscription", "share_externally"];

                    const featuresSorted = [];

                    for (let i in data.features) {

                        let idx = extensionSort.indexOf(data.features[i].feature_name);
                        if (idx >= 0) {
                            featuresSorted[idx] = data.features[i];
                        }
                    }

                    this.$store.commit('GroupExtensions/clearFeature');

                    for (let i in featuresSorted) {
                        this.$store.commit('GroupExtensions/setFeature', featuresSorted[i]);
                    }
                }
                this.showManageGroup = true;
            });
        }

        @Watch('categoryId', {immediate: true})
        protected onCategoryIdChange(): void {
            if (this.categoryId === ALL_POST_ID) {
                this.currentCategory = CATEGORY_SYMBOL + this.$t('all_post') as string;
            } else {
                for (let i = 0; i < this.categories.length; i++) {
                    if (this.categories[i].category_id === this.categoryId) {
                        this.currentCategory = CATEGORY_SYMBOL + this.categories[i].name;
                        break;
                    }
                }
            }
        }

        @Watch('userId')
        protected onUserId() {
            this.updateSortByData();
        }

        @Watch('groupFeatures', {immediate: true, deep: true})
        protected onFeatures() {
            this.updateSortByData();
        }

        private updateSortByData() {
            if (!this.$store.state.User.id || !this.$store.state.User.activate) {
                this.sortByData = SORT_BY_THREAD;
            } else {
                if (this.$store.getters['GroupExtensions/getFeatureStatus']('subscription')) {
                    this.sortByData = SORT_BY_SUBSCRIBE;
                } else {
                    this.sortByData = SORT_BY_GROUP;
                }
            }
        }
    }
</script>
<style lang="scss" scoped>
    .top {
        width: 100%;

        .page-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--p3) var(--p6) 0;
            background-color: var(--body-bg);

            .t {
                @include title_font;
                @include capitalize;
            }
        }
    }
</style>