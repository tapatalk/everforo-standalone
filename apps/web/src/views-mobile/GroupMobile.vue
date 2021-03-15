<template>
    <a-layout class="main-layout">
        <GroupCardMobile
        v-on:show-manage-group="onShowGroupManage"/>
        <a-layout-content class="main-content">
            <div class="top">
                <div class="page-title">
                    <GroupSelectMobile
                        :selected-group="sort"
                    />
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
    </a-layout>
</template>
<script lang="ts">
    import {Component, Vue, Watch} from 'vue-property-decorator';
    import {RawLocation, Route} from 'vue-router';
    import {SORT_BY_GROUP} from '@/helpers/Utils';
    import GroupCardMobile from '@/components-mobile/GroupCardMobile.vue';
    import QuickTopic from '@/components/QuickTopic.vue';
    import Sort from '@/components/Sort.vue';
    import GroupSelectMobile from '@/components-mobile/GroupSelectMobile.vue'
    import ThreadList from '@/components/ThreadList.vue';
    import GroupManage from '@/components/GroupManage.vue';
    import {Response} from "@/http/Response";
    import {GroupFeatureInterface} from "@/helpers/Interfaces";

    @Component<GroupMobile>({
        beforeRouteUpdate(to: Route, from: Route, next: any) {
            if (to.params.sort) {
                this.sort = to.params.sort;
            }

            next();
        },
        components: {
            GroupCardMobile,
            QuickTopic,
            Sort,
            ThreadList,
            GroupSelectMobile,
            GroupManage
        },
    })
    export default class GroupMobile extends Vue {
        
        public sort: string = SORT_BY_GROUP[1];
        protected showManageGroup: boolean = false;
        protected defaultKey: string = '1';


        get sortByData(): string[] {
            return SORT_BY_GROUP;
        }

        get userId(): number {
            return this.$store.state.User.id;
        }

        @Watch('userId')
        protected onUserIdChange() {
            if (this.$route.params.type === 'join' && this.$store.state.User.id && this.$route.query.email == this.$store.state.User.email) {
                this.follow();
            }
        }

        protected created() {
            if (this.$route.params.sort) {
                this.sort = this.$route.params.sort;
            }
            if (this.$route.params.type) {
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
                } else {
                    this.$router.push({
                            name: '404',
                        } as unknown as RawLocation);
                }
            }
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
    }
</script>
<style lang="scss" scoped>
    .top {
        width: 100%;

        .page-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--p8) var(--p6);
            background-color: var(--body-bg);

            .t {
                @include title_font;
                @include capitalize;
            }
        }

        .quick-topic {
            padding: 0 var(--p6) var(--p9) var(--p6);
        }
    }
</style>

<style lang="scss">
    .ant-list-split .ant-list-item {
        padding: var(--p9) var(--p6);
    }

    .main-content {
        padding-bottom: 3rem;
    }
</style>