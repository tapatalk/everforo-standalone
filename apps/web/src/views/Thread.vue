<template>
    <a-layout class="main-layout wide">
        <a-layout-content 
            v-if="postShowStatus"
            class="main-content no-left"
        >
            <Breadcrumb
                :category-id="threadCategoryId"
            />
            <ThreadContent
                v-if="thread && thread.id"
                :thread="thread"
            />
            <a-skeleton
                v-else
                active
                avatar
                :paragraph="{rows: 3}"
            />
            <section
                    v-if="thread && thread.first_post && !thread.first_post.deleted"
                    class="action-and-info"
            >
                <PostAction
                    :post="thread.first_post"
                    :thread-likes="thread.likes_count"
                    :thread-replies="thread.posts_count"
                    :isFirstPost="true"
                    :thread-title="thread.title"
                    :thread-no-recommend="thread.no_recommend"
                    :category-id="thread.category_index_id"
                    :reply-placeholder="thread.posts && thread.posts.length ? $t('share_thoughts') : $t('first_comment')"
                    v-on:new-post="onNewPost"
                    v-on:edit-post="onEditPost"
                    v-on:edit-thread-title="onEditThreadTitle"
                    v-on:edit-thread-category="onEditThreadCategory"
                    v-on:post-delete="onPostDelete"
                    v-on:swicth-recommend="onSwicthRecommend"
                />
            </section>
            <section class="post-list">
                <div
                    class="page-title"
                    v-if="thread && thread.posts_count"
                >
                    <PostInfo
                        :posts-count="thread.posts_count"
                        :likes-count="thread.likes_count"
                        :is-first-post="true"
                    />
                    <Sort
                        :selected-sort="sortBy"
                        :sort-by-data="sortByData"
                    />
                </div>
                <div
                    v-if="enableLoadBefore"
                    class='load-before'
                    v-on:click="loadBefore"
                >
                    <a>{{$t('load_before')}}</a>
                </div>
                <a-skeleton
                    active
                    avatar
                    :paragraph="{rows: 1}"
                    :loading="loadingBefore"
                />
                <PostTree
                    v-if="thread && thread.posts"
                    :posts="thread.posts"
                    :depth="1"
                />
                <a-skeleton
                    active
                    avatar
                    :paragraph="{rows: 1}"
                    :loading="loadingAfter"
                />
                <QuickReply
                    v-if="$store.state.User.id && loadedPage.length && thread.posts && thread.posts.length > 5"
                    :clear-reply="clearTailReply"
                    :post="thread.first_post"
                    :is-first-post="true"
                    v-on:new-post="onNewTailPost"
                />
                <EditorTrigger
                    v-if="!$store.state.User.id && thread && thread.posts && thread.posts.length"
                    :trigger-func="() => {$store.commit('setShowLoginModal', true);}"
                    :placeholder="$t('share_thoughts')"
                />
                <NoMoreData
                    v-if="noMoreData && thread && thread.posts && thread.posts.length"
                />
            </section>
        </a-layout-content>
        <a-layout-content 
            v-else
            class="request-join"
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
        </a-layout-content>
        <ThreadSider
            v-if="isFollow == false && !$store.state.User.super_admin"
        />
        <FlagPost
            v-if="showFlagPost"
        />
        <FlagPostListModal
            v-if="showFlagPostList"
        />
        <LikeListModal
            v-if="showLikeListModal"
        />
        <BlockUserModal
            v-if="showBlockUserModal"
        />
        <JoinRequest
            v-if="showJoinFlag"
            v-on:close-invite-member="showJoinFlag=false"
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
    import {RawLocation} from "vue-router";
    import {Response} from '@/http/Response';
    import {SORT_BY_GROUP, SORT_BY_THREAD} from '@/helpers/Utils';
    import {LikeInterface, PostInterface, ThreadInterface} from '@/helpers/Interfaces';
    import BlockUserModal from "@/components/BlockUserModal.vue";
    import Breadcrumb from "@/components/Breadcrumb.vue";
    import EditorTrigger from "@/components/EditorTrigger.vue";
    import FlagPost from "@/components/FlagPost.vue";
    import FlagPostListModal from "@/components/FlagPostListModal.vue";
    import LikeListModal from '@/components/LikeListModal.vue';
    import NoMoreData from '@/components/NoMoreData.vue';
    import JoinRequest from '@/components/JoinRequest.vue';
    import PostInfo from '@/components/PostInfo.vue';
    import PostTree from '@/components/PostTree.vue';
    import PostAction from '@/components/PostAction.vue';
    import QuickReply from '@/components/QuickReply.vue';
    import Sort from '@/components/Sort.vue';
    import ThreadContent from '@/components/ThreadContent.vue';
    import ThreadSider from '@/components/ThreadSider.vue';
    import GroupJoinRequest from '@/components/GroupJoinRequest.vue';
    import AdminLogin from '@/components/AdminLogin.vue';

    @Component({
        components: {
            BlockUserModal,
            Breadcrumb,
            EditorTrigger,
            FlagPost,
            FlagPostListModal,
            LikeListModal,
            NoMoreData,
            PostInfo,
            PostTree,
            PostAction,
            QuickReply,
            Sort,
            ThreadContent,
            ThreadSider,
            JoinRequest,
            GroupJoinRequest,
            AdminLogin,
        },
    })
    export default class Thread extends Vue {

        protected thread: ThreadInterface = {} as ThreadInterface;

        protected enableLoadBefore: boolean = false;
        protected loadingBefore: boolean = false;

        protected loadingAfter: boolean = false;
        protected disableLoadAfter: boolean = true;
        protected noMoreData: boolean = false;
        protected previousThreadId: number = -1000;
        protected firstPageSize:number = 0;
        protected postShowStatus: boolean = true;
        protected showJoinFlag = false;
        protected showGroupJoinFlag = false;

        protected clearTailReply: boolean = false;

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

        get threadId(): number {
            const slug = String(this.$route.params.thread_slug).match(/-?(\d+)$/);

            if (slug) {
                return parseInt(slug[1]);
            } else {
                return 0;
            }
        }

        get threadCategoryId() {
            return this.thread.category_index_id;
        }

        get isFollow(): boolean {
            return this.$store.getters['User/isFollow'](this.$store.state.Group.id);
        }

        get userId(): number {
            return this.$store.state.User.id;
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

        get sortBy(): string {
            return this.$route.params.sort ? this.$route.params.sort : SORT_BY_GROUP[1];
        }

        get sortByData(): string[] {
            return SORT_BY_THREAD;
        }

        get previousSort(): string {
            return this.$store.state.Thread.sort;
        }

        set previousSort(sort: string) {
            this.$store.commit('Thread/setSort', sort);
        }

        get page(): number {
            return this.$route.params.page ? parseInt(this.$route.params.page) : 1;
        }

        get postId(): number {
            return this.$route.params.post_id ? parseInt(this.$route.params.post_id) : 0;
        }

        get loadedPage(): number[] {
            return this.$store.state.Thread.loadedPage;
        }

        get scrollReachBottom() {
            return this.$store.state.scrollReachBottom;
        }

        get newPostNotification(): PostInterface {
            return this.$store.state.Post.new_post;
        }
        // show/hide flag post popup
        get showFlagPost(): number {
            return this.$store.state.Flag.flagPostId;
        }
        // show/hide flag post list popup
        get showFlagPostList(): number {
            return this.$store.state.Flag.flagPostListId;
        }
        // show liked usre list of a post
        get showLikeListModal(): number {
            return this.$store.state.Like.likeListPostId;
        }
        // show block user popup
        get showBlockUserModal(): number {
            return this.$store.state.Flag.blockUser;
        }

        get groupAdminFlag(): boolean {
            return this.$store.state.Group.group_admin.length > 0 ? false : true;
        }

        protected beforeDestroy() {
            this.thread = {} as ThreadInterface;
            this.$store.commit('Thread/clearLoadedPage');
        }

        protected onShowJoin() {
            if (this.$store.state.User.id && this.$store.state.User.activate) {
                this.showJoinFlag = true;
            } else {
                this.$store.commit('setShowLoginModal', true);
            }
        }

        protected created() {
            if (this.userStatus) {
                this.showGroupJoinFlag = true;
            }
        }

        @Watch('userId')
        protected onUserIdChange() {
            if (this.userStatus) {
                this.showGroupJoinFlag = true;
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
            // we click on user profile post, there is a bug we can't just close profile before redirecting
            this.$store.commit('Profile/showProfile', false);
            // if the same thread, same sort loaded the same page, we don't load it again
            if (this.sortBy === this.previousSort && this.loadedPage.indexOf(this.page) !== -1 
                && this.previousThreadId == this.threadId) {
                this.loadingAfter = false;
                return;
            }
            // already loaded something
            if (this.loadedPage.length) {
                // load after
                if (this.page > this.loadedPage[this.loadedPage.length-1]) {
                    // when there is no page loaded yet
                    if (this.loadingAfter) {
                        return;
                    }
                    // show skeleton
                    this.loadingAfter = true;
                    // disable load when nothing to load
                    this.disableLoadAfter = true;
                // load before
                } else if (this.page < this.loadedPage[0]) {
                    this.enableLoadBefore = false;
                    this.loadingBefore = true;
                }

            } else {
                // when there is no page loaded yet
                // in this case, impossible to have load before
                if (this.loadingAfter) {
                    return;
                }
            }


            const param = {
                thread_id: this.threadId,
                sort: this.sortBy,
                page: this.page,
                post_id: this.postId,
                thread_slug: this.$route.params.thread_slug,
            };
            if(this.previousThreadId != this.threadId) {
                this.thread = {} as ThreadInterface;
            }
            this.previousThreadId = this.threadId;

            // if refresh on page > 1, force to page 1, because we don't have scroll reach top event
            // if (this.page > 1 && (!this.thread.posts || !this.thread.posts.length || this.sortBy !== this.previousSort)) {
            // when switch sort, we need to reset the page to 1
            if (this.page > 1 && this.previousSort && this.sortBy !== this.previousSort) {
                param.page = 1;
                this.loadingAfter = false;
                this.disableLoadAfter = false;
                this.noMoreData = false;
                this.$router.push({
                    name: 'thread',
                    params: param,
                } as unknown as RawLocation);
                // must stop here
                return;
            }

            this.$store.dispatch('Thread/load', param)
                .then((response: Response) => {
                    if (response!.response?.data.code == '40003') {
                        this.postShowStatus = false;
                        return;
                    }
                    // hide skeleton
                    this.loadingAfter = false;
                    this.loadingBefore = false;

                    // if http status is not 200
                    if (response.getStatus() != 200) {
                        this.noMoreData = true;
                        this.$message.info(this.$t('network_error') as string);
                        return;
                    }
                    if(response.getCode() == '404') {
                        this.noMoreData = true;
                        // this.$message.info(this.$t('thread_not_exists') as string);
                        this.$router.push({
                            name: '404',
                        } as unknown as RawLocation);
                        return;
                    }
                    const responseData: {thread: ThreadInterface, page?: number} = response.getData();
                    const threadData = responseData.thread;
                    // if we already fetched the thread before. only load the posts for next page or for new sort
                    if(this.thread.id) {

                        if (this.previousSort && this.sortBy === this.previousSort) {
                            if (this.page > this.loadedPage[this.loadedPage.length-1]) {
                                this.thread.posts = (this.thread.posts as any[]).concat(threadData.posts);
                            } else if (this.page < this.loadedPage[0]) {
                                this.thread.posts = (threadData.posts).concat(this.thread.posts);

                                if (this.page > 1) {
                                    this.enableLoadBefore = true;
                                }
                            }
                        } else {
                            // clear loaded page history
                            this.$store.commit('Thread/clearLoadedPage');
                            this.thread.posts = threadData.posts;
                        }
                    } else {
                        // thread not loaded before, simply assign object
                        this.thread = threadData;
                        // if we load a page > 1 initially, we must enable load before
                        if (this.page > 1) {
                            this.enableLoadBefore = true;
                        }
                    }
                    //update post count
                    this.thread.posts_count = threadData.posts_count;
                    
                    //commit thread title
                    this.$store.commit('Thread/setTitle', threadData.title);

                    //commit thread is pin
                    this.$store.commit('Thread/setPin', threadData.is_pin);

                    // got data, we can scroll now
                    if(threadData.posts && threadData.posts.length >= this.pageLength) {
                        this.disableLoadAfter = false;
                        this.noMoreData =false;
                    } else {
                        // no new post coming, no moew data
                        this.noMoreData = true;
                    }
                    // run these two lines at the end
                    this.previousSort = this.sortBy;
                    if (responseData.page) {
                        // the page corrected by server, push it to the url, 
                        // it should not load agian, because it should be intercepted by loaded page check
                        this.$store.commit('Thread/addLoadedPage', responseData.page);
                        param.page = responseData.page;
 
                        this.$router.push({
                            name: 'thread',
                            params: Object.assign({group_name: this.groupName}, param),
                            hash: '#' + param.post_id,
                        } as unknown as RawLocation);
                        // this should only happen when first load a thread
                        if (responseData.page > 1) {
                            this.enableLoadBefore = true;
                        }

                        return;
                    } else {
                        this.$store.commit('Thread/addLoadedPage', param.page);
                    }
                    /* 
                    when load relative, if a page is less than `pageLength`. until we load posts `geq pageLength`
                    eg. we load page 4, and it has 4 posts, so we automatically load page 5, and 5 has 5 posts, continue to load page 6
                    if we don't want this behavior, just comment out the following if statement.
                    the potential issue is a complete empty page.
                    the nature of the problem is we doing the logic in frontend which should be done on API side.
                    */
                    if (!this.noMoreData && this.firstPageSize <= this.pageLength && this.sortBy == SORT_BY_GROUP[1]) {
                        for (let post in threadData.posts) {
                            if (!this.$store.getters['User/isBlocked'](threadData.posts[post].user_id)){
                                this.firstPageSize ++;
                            }
                        }
                        param.page ++;
                        this.$router.push({
                            name: 'thread',
                            params: Object.assign({group_name: this.groupName}, param),
                        } as unknown as RawLocation);
                    }
                });
        }

        protected loadBefore() {
            if (this.loadingBefore) {
                return;
            }

            const param = {
                thread_id: this.threadId,
                sort: this.sortBy,
                page: this.page - 1,
                thread_slug: this.$route.params.thread_slug,
            };

            this.enableLoadBefore = true;

            this.$router.push({
                name: 'thread',
                params: Object.assign({group_name: this.groupName}, param)
            } as unknown as RawLocation);
        }

        @Watch('scrollReachBottom')
        protected onScrollReachBottom(val: boolean): void {
            if (this.disableLoadAfter || !this.loadedPage.length || this.noMoreData || !val) {
                // this.loadingAfter = false;
                return;
            }

            this.disableLoadAfter = true;

            const param = {
                thread_id: this.threadId,
                sort: this.sortBy,
                page: this.page + 1,
                thread_slug: this.$route.params.thread_slug,
            };

            this.$router.push({
                name: 'thread',
                params: Object.assign({group_name: this.groupName}, param)
            } as unknown as RawLocation);
        }

        @Watch('newPostNotification')
        protected onNewPostNotification(postData: PostInterface): void {

            // dynamically add new post to post tree, when parent_id = -1
            if (postData && postData.thread_id == this.thread.id // todo add group_id
            && postData.id && postData.parent_id === -1){
                this.onNewPost(postData);

                this.$nextTick(() => {
                    this.$store.commit('Post/clearNewPost');
                })
            }
        }

        protected onNewPost(postData: PostInterface): void {
            if (postData.parent_id === -1) {
                this.thread.posts.unshift(postData);
            }
        }

        protected onNewTailPost(postData: PostInterface): void {
            this.thread.posts.push(postData);
            this.clearTailReply = true;
            // the child component listening on the change, so we must reset it
            this.$nextTick(() => {
                this.clearTailReply = false;
            });
        }

        protected onEditPost(postData: PostInterface): void {
            this.thread.first_post.content = postData.content;

            if (postData.attached_files) {
                this.thread.first_post.attached_files = postData.attached_files;
            }
        }

        protected onEditThreadTitle(title: string): void {
            this.thread.title = title;
        }

        protected onEditThreadCategory(category_index_id: number): void {
            this.thread.category_index_id = category_index_id;
        }

        protected onPostDelete(post: PostInterface): void {
            if (post.id === this.thread.first_post.id) {
                this.thread.first_post.deleted = 1;

                if (parseInt(post.deleted_by as unknown as string) != post.user_id) {
                    this.thread.first_post.deleted_by = this.$store.state.User;
                }
            }
        }

        protected onSwicthRecommend(no_recommend: number) {
            if (no_recommend) {
                this.$message.success(this.$t('hide_from_trending_notification') as string);
            } else {
                this.$message.success(this.$t('show_in_trending_notification') as string);
            }

            this.thread.no_recommend = no_recommend;
        }

    }
</script>
<style lang="scss" scoped>
    .action-and-info {
        padding: var(--p2) var(--p6) var(--p2);
    }

    iframe {
        display: none;
    }

    .post-list {

        padding: 0 var(--p6) var(--p6);
        border-top: $border-width $border-style var(--border-color5);

        .page-title {
            display: flex;
            justify-content: space-between;
            align-items: center;

            .sort {
                padding-bottom: 0;
                font-size: $font-size1 - 0.1;
            }
        }

        .load-before {
            padding: var(--p4) 0 0;

            a {
                @include load_more;
            }
        }

        .ant-skeleton {
            margin-top: var(--p4);
        }
    }

    .editor-trigger-section {
        margin-top: var(--p4);
    }

    .ant-skeleton {
        padding: 0 var(--p6);
    }
    .request-join {
        text-align: center;
        margin-top: 30%;
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