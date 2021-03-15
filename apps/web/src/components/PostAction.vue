<template>
    <!-- the following div is for login user -->
    <div
        v-if="$store.state.User.id && $store.state.User.activate"
        :class="['post-action', {'mobile': isMobile}]"
    >
        <div
            :class="['actions', {'first-post': isFirstPost}]"
        >
            <div
                v-if="!isFirstPost"
                :class="['item', {'mobile': isMobile}]"
                v-on:click="replyAction"
            >
                <span>
                    <Icons
                        type="pinglun"
                    />
                </span>
            </div>
            <!-- when user already liked the post -->
            <div
                v-if="liked"
                :class="['item', {'liked': liked}, {'mobile': isMobile}]"
            >
                <span
                    v-on:click="unlikeAction"
                >
                    <Icons
                        type="dianzan"
                    />
                </span>
                <span
                    v-if="isFirstPost"
                    v-on:click="unlikeAction"
                    class="text"
                >{{$t('liked')}}</span>
                <Dot
                    v-if="totalLikes"
                />
                <span
                    v-if="(!isFirstPost || isMobile) && totalLikes"
                    v-on:click="onLikeNumClicked"
                    class="num"
                >{{totalLikes}}</span>
                <LikePreview
                    v-if="isFirstPost && totalLikes && !isMobile"
                    :likes="likes"
                />
            </div>
            <!-- when user did not like the post yet -->
            <div
                v-else
                :class="['item', 'like', {'mobile': isMobile}]"
            >
                <span
                    v-on:click="likeAction"
                >
                    <Icons
                        type="dianzan"
                    />
                </span>
                <span
                    v-if="isFirstPost"
                    v-on:click="likeAction"
                    class="text"
                >{{$t('like')}}</span>
                <Dot
                    v-if="totalLikes > 0"
                />
                <span
                    v-if="(!isFirstPost || isMobile) && totalLikes"
                    v-on:click="onLikeNumClicked"
                    class="num"
                >{{totalLikes}}</span>
                <LikePreview
                    v-if="isFirstPost && totalLikes && !isMobile"
                    :likes="likes"
                />
            </div>
            <!-- Flag -->
            <div
                v-if="totalReport"
                :class="['item', 'flag',{'liked': flagged}, {'mobile': isMobile}]"
                v-on:click="showFlagList"
            >
                <span>
                    <Icons
                        type="flag"
                    />
                </span>
                <span
                    class="text"
                    v-if="isFirstPost"
                >{{$t('flag')}}</span>
                <Dot
                    v-if="totalReport"
                />
                <span
                    class="num"
                >{{totalReport}}</span>
            </div>

            <!-- share -->
            <div 
            :class="['like','item', {'mobile': isMobile}]"
            v-if="isFirstPost && subscribeFeatureStatus && !isSubscribe"
            v-on:click="subscribeThread"
            >
                <span :class="[{'subscribe-icon':isFirstPost}]">
                    <Icons
                        type="subscribe"
                    />
                </span>
                <span
                    class="text"
                    v-if="isFirstPost"
                >{{$t('subscribe')}}</span>
            </div>

            <div 
            :class="['like','item', 'liked', {'mobile': isMobile}]"
            v-if="isFirstPost && subscribeFeatureStatus && isSubscribe"
            v-on:click="unsubscribeThread"
            >
                <span :class="[{'unsubscribe-icon':isFirstPost}]">
                    <Icons
                        type="unsubscribe"
                    />
                </span>
                <span
                    class="text"
                    v-if="isFirstPost"
                >{{$t('subscribed')}}</span>
            </div>

            <a-dropdown
                :trigger="['click']"
                v-if="shareFeatureStatus"
            >
                <div 
                    :class="['like','item', {'mobile': isMobile}]"
                    
                >
                    <span :class="[{'share-icon':isFirstPost}]">
                        <Icons
                            type="share"
                        />
                    </span>
                    <span
                        class="text"
                        v-if="isFirstPost"
                    >{{$t('share')}}</span>
                    
                </div>
                <a-menu slot="overlay" 
                        class="share-menu"
                 >
                    <a-menu-item>
                        <div class="share-div">
                            <span 
                                v-if="!isMobile"
                                class="share-with"
                            >
                                {{$t('share_with')}}：
                            </span>
                            <a v-on:click="onShare('facebook')"><img class="share-img" :src="facebookImg"></a>
                            <a class="twitter-img" v-on:click="onShare('twitter')" ><img class="share-img" :src="twitterImg"></a>
                        </div>
                    </a-menu-item>
                    <a-menu-item>
                        <div class="share-div share-span">
                            <span 
                                v-if="!isMobile"
                                class="share-with"
                            >
                                {{$t('link')}}：
                            </span>
                            <span 
                                class="share-link"
                                :title="shareLink"
                                v-text="shortShareLink">
                            </span>
                            <span
                                v-clipboard:error="onError"
                                v-clipboard:copy="shareLink"
                                v-clipboard:success="onCopy"
                            >
                                <Icons
                                    type="copy"
                                />
                            </span>
                        </div>
                    </a-menu-item>
                </a-menu>
            </a-dropdown>

            <a-dropdown
                v-if="showMoreButton"
                :trigger="['click']"
                v-on:visibleChange="menuVisibleHandler"
            >
                <a
                    :class="['show-more-action', {'menu-showed': showActionMenu}]"
                >
                    <Icons
                        type="more"
                    />
                </a>
                <a-menu slot="overlay" class="comment-action-menu">
                    <a-menu-item
                        v-if="showDeleteButton"
                    >
                        <div 
                            v-on:click="editAction"
                            class="item"
                        >
                            <Icons
                                type="bianji"
                            />
                            <span
                                class="text"
                            >{{$t('edit')}}</span>
                        </div>
                    </a-menu-item>
                    <a-menu-item
                        v-if="showDeleteButton"
                    >
                        <div
                            v-on:click="showDeleteConfirm = true"
                            class="item"
                        >
                            <Icons
                                type="shanchu"
                            />
                            <span
                                class="text"
                            >{{$t('delete')}}</span>
                        </div>
                    </a-menu-item>
                    <a-menu-item
                        v-if="showFlagButton"
                    >
                        <div 
                            v-on:click="flagAction"
                            class="item"
                        >
                            <Icons
                                type="flag"
                            />
                            <span
                                class="text"
                                
                            >{{$t('flag')}}</span>
                        </div>
                    </a-menu-item>
                    <a-menu-item
                        v-if="isFirstPost && isAdmin && threadPinStatus"
                    >
                        <div 
                            v-on:click="unpinThread"
                            class="item"
                        >
                            <Icons
                                type="unpin"
                            />
                            <span
                                class="text"
                                
                            >{{$t('unpin')}}</span>
                        </div>
                    </a-menu-item>
                    <a-menu-item
                        v-if="isFirstPost && isAdmin && !threadPinStatus"
                    >
                        <div 
                            v-on:click="onPin"
                            class="item"
                        >
                            <Icons
                                type="pin"
                            />
                            <span
                                class="text"
                                
                            >{{$t('pin')}}</span>
                        </div>
                    </a-menu-item>
                </a-menu>
            </a-dropdown>
        </div>
        <keep-alive>
            <QuickReply
                v-if="showReply"
                :post="post"
                :is-first-post="isFirstPost"
                :clear-reply="clearReply"
                :set-editor-focus="setEditorFocus"
                :placeholder="replyPlaceholder"
                v-on:new-post="onNewPost"
                v-on:close-editor="onCloseReply"
            />
        </keep-alive>
        <PostEdit
            v-if="showEdit && !isFirstPost"
            :post="post"
            :set-editor-focus="setEditorFocus"
            v-on:edit-post="onEditPost"
        />
        <ThreadEdit
            v-if="showEdit && isFirstPost"
            :post="post"
            :is-edit="true"
            :thread-title="threadTitle"
            :category-id="categoryId"
            v-on:edit-post="onEditPost"
            v-on:edit-thread-title="onEditThreadTitle"
            v-on:edit-thread-category="onEditThreadCategory"
            v-on:close="showEdit = false"
        />
        <ConfirmModal
            v-if="showDeleteConfirm"
            :reverse-button="true"
            :yes-text="deleteButton"
            :no-text="$t('not_now')"
            v-on:confirm="deleteAction"
            v-on:cancel="showDeleteConfirm = false"
        >
            <div
                class="confirm-message"
            >{{$t('delete_post')}}</div>
        </ConfirmModal>
        <ConfirmModal
                v-if="showPinConfirm"
                :reverse-button="true"
                :yes-text="$t('Yes')"
                :no-text="$t('cancel')"
                v-on:confirm="pinThread"
                v-on:cancel="showPinConfirm = false"
        >
            <div class="ant-ban-image"><QuestionMark/></div>

            <div
                    class="confirm-message"
            >{{$t('pin_topic')}}</div>
        </ConfirmModal>
    </div>
    <!-- the following div is for guests -->
    <div
        v-else
        :class="['post-action', {'mobile': isMobile}]"
    >
        <div
            :class="['actions', {'first-post': isFirstPost}]"
        >
            <div
                :class="['item',  {'mobile': isMobile}]"
                v-on:click="showLoginModal"
                v-if="isFirstPost == false"
            >
                <Icons
                    type="pinglun"
                />
            </div>
            <div
                :class="['item',  {'mobile': isMobile}]"
            >
                <span
                    v-on:click="showLoginModal"
                >
                    <Icons
                        type="dianzan"
                    />
                </span>
                <span
                    v-if="isFirstPost"
                    class="text"
                    v-on:click="showLoginModal"
                >{{$tc('likes', totalLikes)}}</span>
                <Dot
                    v-if="(!isFirstPost || isMobile) && totalLikes"
                />
                <span
                    v-if="(!isFirstPost || isMobile) && totalLikes"
                    v-on:click="onLikeNumClicked"
                    class="num"
                >{{totalLikes}}</span>
                <Dot
                    v-if="isFirstPost && totalLikes && !isMobile"
                />
                <LikePreview
                    v-if="isFirstPost && totalLikes && !isMobile"
                    :likes="likes"
                />
            </div>

            <div 
                :class="['like','item', {'mobile': isMobile}]"
                v-if="isFirstPost && subscribeFeatureStatus"
                v-on:click="showLoginModal"
            >
                <span :class="[{'subscribe-icon':isFirstPost}]">
                    <Icons
                        type="subscribe"
                    />
                </span>
                <span
                    class="text"
                    v-if="isFirstPost"
                >{{$t('subscribe')}}</span>
            </div>

            <div
                v-if="totalReport"
                :class="['item', 'flag',  {'mobile': isMobile}]"
                v-on:click="showFlagList"
            >
                <span>
                    <Icons
                        type="flag"
                    />
                </span>
                <span
                    class="text"
                    v-if="isFirstPost"
                >{{$t('flag')}}</span>
                <Dot
                    v-if="totalReport"
                />
                <span
                    class="num"
                >{{totalReport}}</span>
            </div>

            <a-dropdown
                :trigger="['click']"
                v-if="shareFeatureStatus"
            >
                <div 
                    :class="['like','item', {'mobile': isMobile}]"
                    
                >
                    <span :class="[{'share-icon':isFirstPost}]">
                        <Icons
                            type="share"
                        />
                    </span>
                    <span
                        class="text"
                        v-if="isFirstPost"
                    >{{$t('share')}}</span>
                </div>
                <a-menu slot="overlay" 
                        class="share-menu"
                 >
                    <a-menu-item>
                        <div class="share-div">
                            <span
                                v-if="!isMobile"
                                class="share-with"
                            >
                                {{$t('share_with')}}：
                            </span>
                            <a v-on:click="onShare('facebook')"><img class="share-img" :src="facebookImg"></a>
                            <a class="twitter-img" v-on:click="onShare('twitter')" ><img class="share-img" :src="twitterImg"></a>
                        </div>
                    </a-menu-item>
                    <a-menu-item>
                        <div class="share-div share-span">
                            <span
                                v-if="!isMobile" 
                                class="share-with"
                            >
                                {{$t('link')}}：
                            </span>
                            <span 
                                class="share-link"
                                :title="shareLink"
                                v-text="shortShareLink">
                            </span>
                            <span 
                                v-clipboard:error="onError"
                                v-clipboard:copy="shareLink"
                                v-clipboard:success="onCopy"
                            >
                                <Icons
                                    type="copy"
                                />
                            </span>
                        </div>
                    </a-menu-item>
                </a-menu>
            </a-dropdown>


        </div>
        <div
            v-if="isFirstPost"
            :class="['guest-editor', {'mobile': isMobile}]"
        >
            <EditorTrigger
                :placeholder="$t('share_thoughts')"
            />
        </div>
    </div>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Vue, Watch} from 'vue-property-decorator';
    import {RawLocation} from "vue-router";
    import {IS_MOBILE, SORT_BY_GROUP, SORT_BY_THREAD, StorageLocal} from "@/helpers/Utils";
    import {FlagInterface, LikeInterface, PostInterface, ThreadInterface} from '@/helpers/Interfaces';
    import ConfirmModal from '@/components/ConfirmModal.vue';
    import Dot from '@/components/Dot.vue';
    import EditorTrigger from '@/components/EditorTrigger.vue';
    import LikePreview from '@/components/LikePreview.vue';
    import PostEdit from '@/components/PostEdit.vue';
    import QuickReply from '@/components/QuickReply.vue';
    import ThreadEdit from '@/components/ThreadEdit.vue';
    import VueClipboard from 'vue-clipboard2';
    import QuestionMark from '@/components/QuestionMark.vue';

    //Load plugin
    Vue.use(VueClipboard);

    @Component({
        components: {
            ConfirmModal,
            Dot,
            EditorTrigger,
            LikePreview,
            PostEdit,
            QuickReply,
            ThreadEdit,
            QuestionMark,
        },
    })
    export default class PostAction extends Vue {
        @Prop()
        public post!: PostInterface;
        @Prop()
        public isFirstPost!: boolean; // is the post of the first post in a thread, in other word, post is the thread content
        @Prop()
        public threadTitle!: string;
        @Prop()
        public categoryId!: number;
        @Prop()
        public threadLikes!: number;
        @Prop()
        public threadReplies!: number;
        @Prop()
        public threadNoRecommend!: number;
        @Prop()
        public replyPlaceholder!: string;

        protected totalReplies: number = 0;
        protected showReply: boolean = false;
        protected showEdit: boolean = false;
        protected deleteConfirm: boolean = false;
        protected clearReply: boolean = false;
        protected setEditorFocus: boolean = false;
        protected isMobile: boolean = IS_MOBILE;
        protected showActionMenu: boolean = false;
        protected likeActionDsiabled: boolean = false;
        protected unlikeActionDsiabled: boolean = false;
        protected showDeleteConfirm: boolean = false;
        protected unSubscribeDisabled: boolean = false;
        protected subscribeDisabled: boolean = false;
        protected unpinDisabled: boolean = false;
        protected pinDisabled: boolean = false;
        protected showPinConfirm: boolean = false;
        protected facebookImg: string = '/img/facebook.png';
        protected twitterImg: string = '/img/twitter.png';

        get likes(): LikeInterface[] {
            return this.post.likes instanceof Array ? this.post.likes : [];
        }

        get liked(): boolean {
            for (let i = 0; i < this.post.likes.length; i++) {
                if (this.post.likes[i].user_id == this.$store.state.User.id
                    && this.post.likes[i].post_id == this.post.id) {
                    return true;
                }
            }

            return false;
        }

        // total likes
        get totalLikes(): number {
            return this.post.likes.length;
        }

        get flags(): FlagInterface[] {
            return this.post.flags instanceof Array ? this.post.flags : [];
        }

        get flagged(): boolean {

            for (let i = 0; i < this.post.flags.length; i++) {
                if (this.post.flags[i].user_id == this.$store.state.User.id
                    && this.post.flags[i].post_id == this.post.id) {
                    return true;
                }
            }
            
            return false;
        }

        get threadPinStatus(): boolean {
            return this.$store.getters['ThreadPin/isPin'](this.post.thread_id, this.$store.state.Thread.is_pin);
        }

        get adminStatus(): number {
            return this.$store.getters['GroupExtensions/getFeatureStatus']('adminsAndModerators');
        }

        get isAdmin():boolean {
            return this.$store.getters['User/isSuperAdmin']() || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 1, this.adminStatus) 
            || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 2, this.adminStatus) 
            || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 3, this.adminStatus);
        }

        // total flags
        get totalReport(): number | undefined {
            return this.post.total_report;
        }

        get deleteButton(): any {
            return this.$store.state.User.super_admin ? this.$t('delete_forever') : this.$t('delete');
        }

        get subscribeFeatureStatus(): number {
            return this.$store.getters['GroupExtensions/getFeatureStatus']('subscription');
        }

        get shareFeatureStatus(): number {
            return this.$store.getters['GroupExtensions/getFeatureStatus']('share_externally');
        }

        //check user subscribe thread
        get isSubscribe(): boolean | undefined {
            return this.$store.getters['Subscribe/isSubscribe'](this.post.thread_id, this.post.is_subscribe) && this.$store.getters['User/isFollow'](this.$store.state.Group.id);
        }

        //Used to control show more buttons
        get showMoreButton(): boolean | undefined | number {
            return this.isAdmin || this.isPostOwner || (!this.flagged && this.isFollowingGroup);
        }

        //Used to control show delete buttons
        get showDeleteButton(): boolean {
            return this.isPostOwner || this.isAdmin;
        }

        //Used to control show flag buttons
        get showFlagButton(): boolean {
            return !this.flagged && this.isFollowingGroup && !this.isPostOwner && !this.isAdmin;
        }

        get isPostOwner(): boolean {
            return this.post && this.post.user_id === this.$store.state.User.id;
        }

        get isFollowingGroup(): boolean {
            return this.$store.getters['User/isFollow'](this.$store.state.Group.id);
        }

        //share link
        get shareLink(): string
        {
            var shareThreadLink = process.env.VUE_APP_DOMAIN + "/thread/" + this.$route.params.thread_slug;
            //if firt post,return thread link,if comment post,return post link
            return this.isFirstPost ? shareThreadLink : shareThreadLink + "/" + SORT_BY_GROUP[1] + "/1/" + this.post.id;
        }

        //Shorten the link
        get shortShareLink(): string
        {
            if (this.shareLink.length > 20) {
               return this.shareLink.substring(0, 25) + '...' + this.shareLink.substring(this.shareLink.length - 7, this.shareLink.length);
            }
            return this.shareLink;
        }

        get userId(): number
        {
            return this.$store.state.User.id;
        }

        @Watch('userId')
        protected onUserIdChange() {
            if (this.$route.params.unsubscribe === 'unsubscribe' && this.$store.state.User.id && this.$route.params.user_id == this.$store.state.User.id) {
                this.unsubscribeThread(true);
            }
        }

        protected created(): void {
            // set total replies, and total likes
            if (this.isFirstPost) {
                if (this.threadReplies) {
                    this.totalReplies = this.threadReplies;
                }

            } else if (this.post && this.post.children) {
                this.totalReplies = this.post.children.length;
            }

            // if it's the first post, always show reply box
            if (this.isFirstPost) {
                this.showReply = true;
                //if first post and unsubscribe,unsubscribe this thread
                if (this.$route.params.unsubscribe === 'unsubscribe') {
                    if (this.$store.state.User.id) {
                        if (this.$route.params.user_id == this.$store.state.User.id) {
                            this.unsubscribeThread(true);
                        } else {
                            this.$message.info(this.$t('unsubscribe_user_error') as string);
                        }
                    } else {
                        this.showLoginModal();
                    }
                }
            }
        }

        protected beforeDestroy(): void {
            delete this.post;
            delete this.threadTitle;
        }

        protected menuVisibleHandler(visible: boolean) {
            this.showActionMenu = visible;
        }

        //Copy success
        protected onCopy(): void{
            this.$message.info(this.$t('link_copy') as string);
        }
        // Copy failed
        protected onError(): void{
            this.$message.error(this.$t('link_copy_error') as string);
        }

        protected onShare(type:string): void {
            var url:string    = this.shareLink;
            var text   = this.$store.state.Thread.title;
            
            var left = (window.innerWidth - 550) / 2;
            if(left < 0){
                left = 0;
            }
            var top = (window.innerHeight - 450) / 2;
            if(top < 0){
                top = 0
            }
            var popup_style = 'left='+left+',top='+top+',width=550,height=450,personalbar=0,toolbar=0,scrollbars=0,resizable=0';

            if (type == 'facebook')
            {
                window.open("//www.facebook.com/sharer/sharer.php?u=" + encodeURIComponent(url) + '&text=' + encodeURIComponent(text), '', popup_style);
            }
            else if (type == 'twitter')
            {
                window.open('//twitter.com/share?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(text), '', popup_style);
            }
        }

        protected onPin()
        {
            this.$store.dispatch('ThreadPin/pinStatus',  { thread_id: this.post.thread_id})
                    .then((response) => {
                        if (response && response.response && response.response.data && response.response.data.data) {
                            if (response.response.data.data.status === "0" || this.$store.state.User.super_admin) {
                                this.pinThread();
                            } else {
                                this.showPinConfirm = true;
                            }
                        }
                    })
        }

        /**
         * show reply post/thread editor
         */
        protected replyAction() {
            if (this.isFirstPost) {
                this.showEdit = false;
                this.showReply = true;
                this.setEditorFocus = !this.setEditorFocus;
            }else{
                this.showEdit = false;
                this.showReply = !this.showReply;
                this.setEditorFocus = true;
            }
        }

        /**
         * after editor send a new post to server successfully,
         * we clear the editor when it's a first level reply
         * also hide the editor when it's a nested reply
         * @param postData
         */
        @Emit('new-post')
        protected onNewPost(postData: PostInterface): PostInterface {
            if (this.isFirstPost) {
                this.clearReply = true;
                // the child component listening on the change, so we must reset it
                this.$nextTick(() => {
                    this.clearReply = false;
                });
            } else {
                this.clearReply = true;
                // the child component listening on the change, so we must reset it
                this.$nextTick(() => {
                    this.clearReply = false;
                    this.showReply = false;
                });
            }

            return postData;
        }

        protected likeAction() {
            if (this.liked || !this.post || !this.post.id || this.likeActionDsiabled) {
                return;
            }

            this.likeActionDsiabled = true;

            const data = new FormData();

            data.append('post_id', this.post.id + '');

            this.$store.dispatch('Post/like', data)
                        .then((data: LikeInterface | any) => {
                            if (data && data.response && data.response.data
                                    && data.response.data.code == 401) {
                                this.likeActionDsiabled = false;
                                this.$message.error(this.$t('ban_message') as string);
                            } else if (data && data.response && data.response.data
                                && data.response.data.code == 40003){
                                    this.likeActionDsiabled = false;
                                this.$message.error(this.$t('join_error') as string);
                            } else {
                                //if user subscribe thread,change subscribe status
                                if (data.is_subscribe) {
                                    this.$store.commit('Subscribe/setSubscribe', this.post.thread_id);
                                }
                                this.unlikeActionDsiabled = false;
                                this.$store.commit('User/addGroup', this.$store.state.Group);
                                this.post.likes.unshift({post_id: this.post.id, user_id: this.$store.state.User.id, is_ban : 0});
                            }
                            // after unlike, user can like again
                        });

        }

        protected unlikeAction() {
            if (!this.liked || !this.post || !this.post.id || this.unlikeActionDsiabled) {
                return;
            }

            this.unlikeActionDsiabled = true;

            const data = new FormData();

            data.append('post_id', this.post.id + '');

            this.$store.dispatch('Post/unlike', data)
                        .then((data: LikeInterface | any) => {
                            if (data && data.response && data.response.data
                                    && data.response.data.code == 401) {
                                //code 401 is user be banned
                                this.unlikeActionDsiabled = false;
                                this.$message.error(this.$t('ban_message') as string);
                            } else {
                                // after unlike, user can like again
                                this.likeActionDsiabled = false;
                                // remove like from list
                                for (let i in this.post.likes) {
                                    if (this.post.likes[i].user_id == this.$store.state.User.id) {
                                        this.post.likes.splice(parseInt(i), 1);
                                    }
                                }
                            }
                        });

        }

        //user subscribe thread
        protected subscribeThread() {
            if (!this.subscribeDisabled) {
                this.subscribeDisabled = true;
                this.$store.dispatch('Subscribe/subscribeThread',  { thread_id: this.post.thread_id})
                    .then((response) => {
                        if (response && response.response && response.response.data && response.response.data.data
                        && response.response.data.data.success) {
                            this.$store.commit('Subscribe/setSubscribe', this.post.thread_id);
                            this.$store.dispatch('User/getMe');
                            this.$store.dispatch('Group/getStat');
                            this.unSubscribeDisabled = false;
                            this.$message.info(this.$t('subscription_on') as string);
                        } else if (response && response.response && response.response.data
                                    && response.response.data.code == 401) {
                                this.subscribeDisabled = false;
                                this.$message.error(this.$t('ban_message') as string);
                            } else if (response && response.response && response.response.data
                                && response.response.data.code == 40003){
                                    this.subscribeDisabled = false;
                                this.$message.error(this.$t('join_error') as string);
                            }
                    })
                    .catch(() =>{
                        this.unSubscribeDisabled = false;
                        //error
                    });
            }
        }

        //user unsubscribe thread
        protected unsubscribeThread(flag = false) {
            if (!this.unSubscribeDisabled) {
                this.unSubscribeDisabled = true;
                this.$store.dispatch('Subscribe/unsubscribeThread',  { thread_id: this.post.thread_id, user_id: ((flag && this.$route.params.user_id) ? this.$route.params.user_id : 0)})
                    .then((response) => {
                        if (response && response.response && response.response.data && response.response.data.data
                        && response.response.data.data.success) {
                            this.subscribeDisabled = false;
                            this.$store.commit('Subscribe/setUnSubscribe', this.post.thread_id);
                            if (flag && this.$route.params.user_id) {
                                this.$message.info(this.$t('unsubscribe_message') as string,20);
                            } else {
                                this.$message.info(this.$t('subscription_off') as string);
                            }
                        }
                    })
                    .catch(() =>{
                        this.subscribeDisabled = false;
                        //error
                    });
            }
        }

        //admin unpin thread
        protected unpinThread()
        {
            if (!this.unpinDisabled) {
                this.unpinDisabled = true;
                this.$store.dispatch('ThreadPin/unpin',  { thread_id: this.post.thread_id})
                    .then((response) => {
                        if (response && response.response && response.response.data && response.response.data.data
                        && response.response.data.data.success) {
                            this.pinDisabled = false;
                            this.$store.commit('ThreadPin/setUnpin', this.post.thread_id);
                            this.$message.info(this.$t('unpin_success') as string);
                        } else if (response && response.response && response.response.data && response.response.data.code
                                && response.response.data.code == 403) {
                            this.unpinDisabled = false;
                            this.$message.error(this.$t('no_permission') as string);
                        }
                    })
                    .catch(() =>{
                        this.unpinDisabled = false;
                        //error
                    });
            }
        }

        //admin pin thread
        protected pinThread()
        {
            if (!this.pinDisabled) {
                this.pinDisabled = true;
                this.$store.dispatch('ThreadPin/pin',  { thread_id: this.post.thread_id})
                    .then((response) => {
                        if (response && response.response && response.response.data && response.response.data.data
                        && response.response.data.data.success) {
                            this.unpinDisabled = false;
                            this.$store.commit('ThreadPin/setPin', this.post.thread_id);
                            this.$store.commit('ThreadPin/setPinUser', this.$store.state.User.name);
                            this.showPinConfirm = false;
                            this.$message.info(this.$t('pin_success') as string);
                        } else if (response && response.response && response.response.data && response.response.data.code
                                && response.response.data.code == 403) {
                            this.pinDisabled = false;
                            this.$message.error(this.$t('no_permission') as string);
                        }
                    })
                    .catch(() =>{
                        this.pinDisabled = false;
                        //error
                    });
            }
        }


        protected editAction() {
            if (!this.isFirstPost) {
                this.showReply = false;
            }

            this.showEdit = !this.showEdit;
            this.setEditorFocus = true;
            this.$nextTick (() => {
                this.setEditorFocus = false;
            });
        }
        /**
         * flag a post, show popup, let user choose flag reasons
         */
        protected flagAction() {
            this.$store.commit('Flag/setFlagPostId', this.post.id);
        }

        protected showFlagList() {
            this.$store.commit('Flag/setFlagPostListId', this.post.id);
        }

        @Emit('edit-post')
        protected onEditPost(postData: PostInterface): PostInterface {
            this.showEdit = false;
            return postData;
        }

        @Emit('edit-thread-title')
        protected onEditThreadTitle(title: string): string {
            this.showEdit = false;
            return title
        }

        @Emit('edit-thread-category')
        protected onEditThreadCategory(category_index_id: number): number {
            this.showEdit = false;
            return category_index_id;
        }

        protected deleteAction(): void {

            if (!this.post || !this.post.id) {
                return;
            }

            const data = new FormData();

            data.append('post_id', this.post.id + '');

            this.$store.dispatch('Post/delete', data)
                .then((postData: PostInterface | any) => {
                    if (postData && postData.response && postData.response.data
                            && postData.response.data.code == 401) {
                        this.$message.error(this.$t('ban_message') as string);
                        this.showDeleteConfirm = false;
                    } else if (postData && postData.response && postData.response.data
                            && postData.response.data.code == 403) {
                        this.$message.error(this.$t('no_permission') as string);
                        this.showDeleteConfirm = false;
                    } else {
                        this.showDeleteConfirm = false;
                        if (postData && (postData.deleted == 1 || postData.deleted == 2 || postData.deleted == 3)) {
                            if (this.isFirstPost) {
                                var threadList:any = [];
                                var oldThreadList = this.$store.state.ThreadList.threadList;
                                for (let index = 0; index < oldThreadList.length; index++) {
                                    if (oldThreadList[index].id !== postData.thread_id) {
                                        threadList = threadList.concat(oldThreadList[index]);
                                    }
                                    
                                }
                                this.$store.commit('ThreadList/setThreadList', threadList);
                                this.$router.push({
                                    name: 'group',
                                    params: {group_name: this.$route.params.group_name},
                                } as unknown as RawLocation);
                            } else {
                                this.onPostDelete(postData);
                            }
                        }
                    }
                });
        }

        @Emit('post-delete')
        protected onPostDelete(post: PostInterface): PostInterface {
            return post;
        }

        protected showLoginModal() {
            this.$store.commit('setShowLoginModal', true);
        }

        protected onCloseReply(val: boolean): void {
            /**
             * close editor if it's a post reply editor, we never close a thread reply editor 
             */
            if (val){
                this.showReply = false;
            }
        }

        protected onLikeNumClicked(){
            this.$store.commit('Like/setLikeListPostId', this.post.id);
        }

        protected noRecmmend() {

            const data = new FormData();

            data.append('thread_id', this.post.thread_id + '');

            this.$store.dispatch('Thread/trending', data)
                        .then((response) => {
                            if (response) {
                                this.switchRecommend();
                            }
                        });
        }

        @Emit('swicth-recommend')
        protected switchRecommend(): number {
            return this.threadNoRecommend ? 0 : 1;
        }

    }
</script>
<style lang="scss" scoped>

    .post-action {

        font-size: $font-size1;
        padding: var(--p4) 0;

        .show-more-action {
            width: 15px;
            height: 15px;
            display: block;

            &.menu-showed {
                display: block;
            }

            .ico {
                color: var(--font-color6);
            }
        }

        

        &.mobile {
            .actions {
                .item {
                    padding: 0 40px 0 0;
                }
            }
        }

        .actions {

            display: flex;
            justify-content: flex-start;
            align-items: center;

            &.first-post {
                .item {
                    margin: 0 var(--p8) 0 0;

                    &.mobile {
                        padding: 0 var(--p4) 0 0;
                        width: auto;
                        .text {
                            font-size: $font-size1;
                        }

                        .ico {
                            font-size: $font-size0;
                        }
                    }

                    .ico {
                        font-size: $font-size4;
                        margin-right: var(--p2);
                    }

                    .text {
                        margin-top: 0.2rem;
                        font-size: $font-size2;
                    }

                    .num {
                        font-size: $font-size2;
                    }
                }

                .show-more-action {
                    .ico {
                        font-size: $font-size4;
                        width: $font-size4;
                        height: $font-size4;
                    }
                }
            }

            .item {
                @include button_font;
                margin: 0 var(--p8) 0 0;
                font-size: 1rem;
                display: flex;
                justify-content: center;
                align-items: center;

                // &.mobile {
                //     padding: 0 var(--p10) 0 0;
                // }

                .ico {
                    font-size: 0.9rem;
                    color: var(--font-color6);
                    vertical-align: middle;
                }

                .num {
                    font-size: 0.9rem;
                    // margin-top:2px;
                    // vertical-align: 0.11rem;
                    color: var(--font-color6);
                }

                .text {
                    color: var(--font-color6);
                    font-weight: 500;
                    font-size: 0.9rem;
                }

                &:hover {
                    .ico, .text {
                        color: var(--font-color1);
                    }      
                }

                &.liked {
                    .num, .text, .ico {
                        color: var(--theme-color);
                    }
                }

                .cancel, .confirm {

                    font-weight: $title-weight;
                    font-size: 1rem;

                    &:last-child {
                        margin-left: var(--p4);
                    }
                }

                .cancel {
                    color: var(--theme-color);
                }

                .confirm {
                    color: $error-color;
                }

            }
        }

        .guest-editor {
            margin-top: var(--p6);

            &.mobile {
                margin-top: 24px;
            }
        }
    }
    .share-div {
        height: 25px;
        width: 430px;
        margin-top: 10px;
    }

    .share-with {
        font-size: $font-size2;
        text-align: right;
        display:inline-block;
        width: 100px;
        color: #8C97AD;
    }

    .share-link {
        font-size: $font-size2;
        margin-right: 15px;
    }

    .twitter-img {
        margin-left: 20px;
    }

    .share-img {
        height: 20px;
        margin-bottom: 5px;
    }

    // more action dropdown
    .comment-action-menu {
        .item {
            display: flex;
            align-items: center;

            .ico {
                margin-right: var(--p3);
            }

            .text {
                display: inline-block;
                &::first-letter {
                    text-transform: uppercase;
                }
            }
        }
    }
    .share-menu {
        .ant-dropdown-menu-item:hover {
            background-color: var(--navbar-bg);
        }
    }
    .question-mark {
        margin: 0 auto;
    }

    .ant-ban-image {
        text-align: center;
    }
</style>