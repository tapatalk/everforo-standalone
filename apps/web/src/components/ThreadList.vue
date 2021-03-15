<template>
    <div
        v-if="categoryEmpty"
        class="category-empty"
    >
        <img src="/img/no-data.png" alt="">
        <p>{{$t('category_empty')}}</p>
    </div>
    <div
        v-else
    >
        <a-list
            v-if="threads.length"
            itemLayout="vertical"
            size="large"
            :dataSource="threads"
        >
            <a-list-item
                slot="renderItem"
                slot-scope="thread"
                :key="thread.id"
                :id="'t' + thread.id"
            >
                <ThreadListContent :thread="thread"/>
            </a-list-item>
        </a-list>
        <a-list
            v-else-if="!threads.length && !notShowFlag"
            itemLayout="vertical"
            size="large"
            :dataSource="pseudoData"
        >
            <a-list-item
                slot="renderItem"
                slot-scope="pseudo"
                :key="pseudo"
            >
                <a-skeleton
                    active
                    avatar
                    :paragraph="{rows: 3}"
                />
            </a-list-item>
        </a-list>
        <div
            class="request-join"
            v-if="notShowFlag"
        >
            <div
                class="request-join-icon"
            >
                <Icons
                    type="shuangren"
                />
            </div>
            
            <div
                class="request-join-test"
            >
                {{$t("this_group_is_closed_to_public")}}
            </div>
            <a-button
                v-if="groupJoining == 2 && $store.getters['User/isBanned'](this.$store.state.Group.id)"
                type="primary"
                v-on:click="onShowJoin"
                class="request-button"
            >
                {{$t('request_to_join')}}
            </a-button>
            <a-button
                v-else-if="groupJoining == 1 && $store.getters['User/isBanned'](this.$store.state.Group.id)"
                type="primary"
                v-on:click="onFollow"
                class="follow"
            >
                {{$t('join_group')}}
            </a-button>
        </div>
        
        <a-skeleton
            active
            avatar
            :paragraph="{rows: 3}"
            :loading="loadingAfter"
        />
        <NoMoreData
            v-if="noMoreData"
        />
        <JoinRequest
            v-if="showJoinFlag"
            v-on:close-invite-member="showJoinFlag=false"
         />
    </div>
</template>
<script lang="ts">
    import {Component, Vue, Watch} from 'vue-property-decorator';
    import {RawLocation} from "vue-router";
    import {ALL_POST_ID, SORT_BY_GROUP} from '@/helpers/Utils';
    import {ThreadInterface} from "@/helpers/Interfaces";
    import NoMoreData from '@/components/NoMoreData.vue';
    import ThreadListContent from '@/components/ThreadListContent.vue';
    import JoinRequest from '@/components/JoinRequest.vue';

    @Component({
        components: {
            NoMoreData,
            ThreadListContent,
            JoinRequest,
        },
    })
    export default class ThreadList extends Vue {

        protected threads: ThreadInterface[] = [];
        // show the skeleton at the end
        protected loadingAfter: boolean = false;
        // disable load next page
        protected disableLoadAfter: boolean = true;
        // when there is no more data to load
        protected noMoreData: boolean = false;
        // if no thread in category 
        private categoryEmpty: boolean = false;
        private previousPage: number = 1;
        protected notShowFlag: boolean = false;
        protected showJoinFlag: boolean = false;

        readonly pageLength: number = 10;

        get groupName(): string {
            return this.$store.state.Group.name;
        }

        get groupJoining(): number {
            if (this.$store.getters['GroupExtensions/getFeatureStatus']('GroupLevelPermission')) {
                return this.$store.state.Group.joining;
            } else {
                return 1;
            }
        }

        get groupVisibility(): number {
            if (this.$store.getters['GroupExtensions/getFeatureStatus']('GroupLevelPermission')) {
                return this.$store.state.Group.visibility;
            } else {
                return 1;
            }
        }

        get pseudoData(): number[] {
            return [1, 2];
        }

        get scrollReachBottom(): boolean {
            return this.$store.state.scrollReachBottom;
        }
        
        get previousGroup(): string {
            return this.$store.state.ThreadList.previousGroup;
        }

        set previousGroup(groupName: string) {
            this.$store.commit('ThreadList/setPreviousGroup', groupName);
        }

        get previousSort(): string {
            return this.$store.state.ThreadList.previousSort;
        }

        set previousSort(sort: string) {
            this.$store.commit('ThreadList/setPreviousSort', sort);
        }

        get previousCategory(): number {
            return this.$store.state.ThreadList.previousCategory;
        }

        set previousCategory(categoryId: number) {
            this.$store.commit('ThreadList/setPreviousCategory', categoryId);
        }

        get loadedPage(): number[] {
            return this.$store.state.ThreadList.loadedCategoryPage;
        }

        get storedThreadList(): ThreadInterface[] {
            return this.$store.state.ThreadList.threadList;
        }

        // before this component gets destroyed, do some garbage collection
        protected beforeDestroy() {
            delete this.threads;
            delete this.onRouteUpdate;
        }

        protected collectParameters(): {group_name: string, sort: string, category_id: number, page: number } {
            return {
                group_name: this.$route.params.group_name ? this.$route.params.group_name : '',
                sort: this.$route.params.sort ? this.$route.params.sort : SORT_BY_GROUP[1],
                category_id: this.$route.params.category_id ? parseInt(this.$route.params.category_id) : ALL_POST_ID,
                page: this.$route.params.page ? parseInt(this.$route.params.page) : 1,
            }
        }

        protected onFollow() {
            if (this.$store.state.User.id && this.$store.state.User.activate) {
                this.$store.dispatch('Group/follow')
                    .then((response: { success: number }) => {
                        if (response.success) {
                            location.reload();
                        }
                    });
            } else {
                this.$store.commit('setShowLoginModal', true);
            }
        }

        @Watch('$route', {immediate: true})
        protected onRouteUpdate(): void {
            const param = this.collectParameters();
            if (this.loadingAfter && this.previousSort === param.sort) {
                return;
            }
            // show skeleton
            this.loadingAfter = true;
            // disable load when nothing to load
            this.disableLoadAfter = true;

            if (this.previousGroup === param.group_name
                && this.previousCategory === param.category_id
                && this.previousSort === param.sort
                && !this.threads.length && this.storedThreadList.length) {
                // if we have saved a thread list, no need to call server
                // console.log(this.storedThreadList);
                this.threads = this.$store.state.ThreadList.threadList;
                this.disableLoadAfter = false;
                this.loadingAfter = false;

                if (this.storedThreadList.length < this.pageLength) {    
                    this.disableLoadAfter = true;
                    this.noMoreData = true;
                }
                return;
            }

            // if the page is loaded already, we don't load it again
            // if (this.previousGroup === param.group_name
            //     && this.previousSort === param.sort
            //     && this.previousCategory === param.category_id
            //     && this.loadedPage.indexOf(param.page) !== -1) {
            //     this.loadingAfter = false;
            //     this.disableLoadAfter = false;
            //     return;
            // }

            // if refresh on page > 1, force to page 1, because we don't have scroll reach top event
            if (param.page > 1 && (!this.threads.length || param.sort !== this.previousSort)) {
                param.page = 1;
                this.loadingAfter = false;
                this.disableLoadAfter = false;
                this.categoryEmpty = false;

                this.threads = [];
                this.$store.commit('ThreadList/emptyLoadedCategoryPage');

                this.$router.push({
                    name: 'group',
                    params: param,
                } as unknown as RawLocation);
                // must stop here
                return;
            }

            // when switch group, change category id or change sort, empty thread list first
            if (this.previousGroup !== param.group_name
                || this.previousSort !== param.sort
                || this.previousCategory !== param.category_id) {
                this.loadingAfter = false;
                this.disableLoadAfter = false;

                this.categoryEmpty = false;
                this.threads = [];
                this.$store.commit('ThreadList/emptyLoadedCategoryPage');

                window.scrollTo(0, 0);
            }

            this.noMoreData = false;

            this.$store.dispatch('ThreadList/load', param)
                .then((threadsData) => {
                    if (threadsData == '40003') {
                        this.notShowFlag = true;
                        this.noMoreData = false;
                        this.loadingAfter = false;
                        this.disableLoadAfter = false;
                        return;
                    }
                    this.loadingAfter = false;
                    // record the loaded page
                    this.$store.commit('ThreadList/addLoadedCategoryPage', param.page);

                    this.previousGroup = param.group_name;

                    if (threadsData.length) {
                        // if we loaded a full page, enable load after again
                        if (threadsData.length >= this.pageLength){
                            this.disableLoadAfter = false;
                        } else {
                            this.noMoreData = true;
                        }

                        this.threads = this.threads.concat(threadsData);
                        // when we are going to a new category, or there we just arrived at this page
                        // we save thread list to store
                        // if (this.previousSort !== param.sort
                        //     || this.previousCategory !== param.category_id
                        //     || !this.storedThreadList.length) {
                            this.storeThreadList(this.threads);
                        // }

                        this.previousCategory = param.category_id;
                        this.previousSort = param.sort;
                        
                    } else {
                        // when there is no more thread
                        // if we already loaded some thread
                        if (this.previousGroup === param.group_name 
                            && this.previousCategory === param.category_id) {

                            if (param.page !== this.previousPage) {

                                param.page = this.previousPage;

                                this.noMoreData = true;

                                // we push the url back to previous page
                                // this.$router.push({
                                //     name: 'group',
                                //     params: Object.assign({group_name: this.groupName}, param)
                                // } as unknown as RawLocation);
                                // return;
                            }else{
                                this.categoryEmpty = true;
                            }

                        } else {
                            // when there is no thread in category at all
                            this.previousCategory = param.category_id;
                            this.categoryEmpty = true;
                        }
                    }
                });
        }

        @Watch('scrollReachBottom')
        protected onScrollReachBottom(val: boolean) {
            // when we are in the middle of a loading,
            // or when the initial loading is not completed yet.
            // we don't trigger load more
            if (this.loadingAfter || !this.loadedPage.length || !val) {
                return;
            }

            if (this.disableLoadAfter || this.noMoreData || !val) {
                this.loadingAfter = false;
                return;
            }

            this.disableLoadAfter = true;

            const param = this.collectParameters();
            // we remember the previous page,
            // in case load the next page failed, we push the url back to previous page
            this.previousPage = param.page;
            param.page = param.page + 1;
            // push to next page
            this.$router.push({
                name: 'group',
                params: Object.assign({group_name: this.groupName}, param)
            } as unknown as RawLocation);
        }

        protected storeThreadList(data: ThreadInterface[]) {
            this.$store.commit('ThreadList/setThreadList', data);
        }

        protected onShowJoin() {
            if (this.$store.state.User.id && this.$store.state.User.activate) {
                this.showJoinFlag = true;
            } else {
                this.$store.commit('setShowLoginModal', true);
            }
        }

    }
</script>
<style lang="scss" scoped>
    .category-empty {
        @include no_data;
    }

    .ant-list-item {
        border-bottom: $border-width $border-style var(--border-color5);

        &:hover {
            background-color: var(--hover-bg);
        }
    }
    .ant-skeleton {
        padding: var(--p6)
    }
    .request-join {
        text-align: center;
        margin-top: 45%;
        .request-join-icon {
            .ico {
                font-size: 2.5rem;
            }
            margin-bottom: 10px;
        }
        .request-join-test {
            font-size: 1.1rem;
            margin-bottom: var(--p6);
        }
        .request-button {
            margin: auto;
            height: 40px;
            padding-left: 25px;
            padding-right: 25px;
        }
        .follow {
            width: 240px;
            font-weight: 500;
            height: 40px;
            display: inline-block;
        }
    }
</style>