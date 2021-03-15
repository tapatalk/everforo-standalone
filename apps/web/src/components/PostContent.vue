<template>
    <div v-if="$store.state.Post.folded.indexOf(post.id) !== -1">
        <section
                v-if="sortStatus"
                class="folded"
        >
            <UserAvatar
                    :avatar="post.user.photo_url"
                    :username="post.user.name"
                    :profileId="post.user.id"
                    :scale="depth == 1 ? 2 : 1"
                    :is-ban="$store.getters['BanUser/isBan'](post.user_id, post.is_ban)"
                    :online="post.online"
            />
            <Username
                    :username="post.user.name"
                    :profileId="post.user.id"
            />
            <div
                    class="brief"
                    v-on:click="unFold"
            >
                <Dot />
                <TimeString :time="post.created_at"/>
                <span
                        v-if="post && post.deleted != 1 && post.deleted != 3 && !$store.getters['User/isBlocked'](post.user_id) &&
                         !$store.getters['BanUser/isBan'](post.user_id, post.is_ban)"
                        class="content-short"
                >{{contentShort}}</span>
            </div>
        </section>

    </div>
    <section v-else>
        <div 
            v-if="depth < indentDepth"
            :class="['fold-line', {'level-one': depth == 1}, {'mobile': isMobile}]" 
            v-on:click="onFold"
        ></div>
        <div
            class="post-block"
        >
            <div
                :id="post.id"
                class="main"
            >
                <UserAvatar
                    :avatar="post.user.photo_url"
                    :username="post.user.name"
                    :profileId="post.user_id"
                    :scale="depth == 1 ? 2 : 1"
                    :class="[{'mobile': isMobile}]"
                    :is-ban="$store.getters['BanUser/isBan'](post.user_id, post.is_ban)"
                    :online="post.online"
                />
                <div class="post-body">
                    <div :class="['poster-info', {'level-one': depth == 1}, {'mobile': isMobile}]">
                        <Username 
                            :username="post.user.name"
                            :profileId="post.user_id"
                        />
                        <Dot />
                        <TimeString :time="post.created_at"/>
<!--                        <Dot-->
<!--                            v-if="post.ipfs"-->
<!--                        />-->
<!--                        <span class="ipfs">-->
<!--                            <IPFS-->
<!--                                v-if="post.ipfs"-->
<!--                                :ipfs-hash="post.ipfs"-->
<!--                            />-->
<!--                        </span>-->
                    </div>
                    <PostContentHidden
                        v-if="post && (post.deleted == 1 || post.deleted == 3 || post.nsfw == 1 ||
                         $store.getters['User/isBlocked'](post.user_id) ||
                         $store.getters['BanUser/isBan'](post.user_id, post.is_ban))"
                        :post="post"
                    />
                    <ImageBlock
                        v-else-if="imageContent.length"
                        :images="imageContent"
                    />
                    <div
                        v-else
                        slot="content"
                        ref="content"
                        :class="['post-content', {'new': isAnchorPost}]"
                        v-html="htmlContent"
                    ></div>
                    <AttachedFiles
                        v-if="!(post.deleted == 1 || post.deleted == 3 || post.nsfw == 1 ||
                            $store.getters['User/isBlocked'](post.user_id) ||
                            $store.getters['BanUser/isBan'](post.user_id, post.is_ban))"
                        :attached-files="post.attached_files"
                    />
                    <PostAction
                        v-if="post && post.deleted != 1 && post.deleted != 3 && !$store.getters['User/isBlocked'](post.user_id) &&
                        !$store.getters['BanUser/isBan'](post.user_id, post.is_ban)"
                        :post="post"
                        :is-first-post="false"
                        :reply-placeholder="post.user.id == $store.state.User.id ? $t('share_thoughts') : $t('reply_to', {username: post.user.name})"
                        v-on:new-post="onNewPost"
                        v-on:edit-post="onEditPost"
                        v-on:post-delete="onPostDelete"
                        :class="[{'mobile': isMobile}]"
                    />
                </div>
            </div>
            <div :class="[{'nested': depth < indentDepth}, {'mobile': isMobile}]">
                <PostTree
                    v-if="post.children && post.children.length"
                    :parent-post="post"
                    :posts="post.children"
                    :depth="depth + 1"
                />
            </div>
        </div>
    </section>
</template>

<script lang="ts">
    import {Component, Prop, Ref, Vue, Watch} from 'vue-property-decorator';
    import {IS_MOBILE, SORT_BY_GROUP, convertQuillDeltaToHTML, insertCard, parseUnicode, twitterWidget, facebookSDK} from "@/helpers/Utils";
    import {DeltaOpsInterface, PostInterface} from '@/helpers/Interfaces';
    import {Response} from '@/http/Response';
    import AttachedFiles from "@/components/AttachedFiles.vue";
    import ImageBlock from '@/components/ImageBlock.vue';
    import Dot from '@/components/Dot.vue';
    import IPFS from '@/components/IPFS.vue';
    import PostAction from '@/components/PostAction.vue';
    import PostContentHidden from '@/components/PostContentHidden.vue';
    import TimeString from '@/components/TimeString.vue';
    import UserAvatar from '@/components/UserAvatar.vue';
    import Username from '@/components/Username.vue';
import { indexOf } from 'lodash';

    @Component({
        components: {
            AttachedFiles,
            ImageBlock,
            Dot,
            IPFS,
            PostAction,
            PostContentHidden,
            TimeString,
            UserAvatar,
            Username,
        }
    })
    export default class PostContent extends Vue {

        @Ref('content')
        readonly content!: HTMLDivElement;

        @Prop()
        public post!: PostInterface;
        @Prop()
        public depth !: number; // how deep the post tree nested

        protected contentShort: string | null = null;

        protected indentDepth: number = IS_MOBILE ? 2 : 7;

        protected isMobile: boolean = IS_MOBILE;

        protected imageContent: DeltaOpsInterface[] = [];
        protected htmlContent: string = '';
        protected isAnchorPost: boolean = false;

        protected links_set: Set<string> = new Set();

        get newPostNotification(): PostInterface {
            return this.$store.state.Post.new_post;
        }

        get sortStatus(): boolean {
            return this.post.deleted == 2 ? false : true;
        }

        protected beforeCreate() {
            //https://vuejs.org/v2/guide/components-edge-cases.html#Circular-References-Between-Components
            // Circular References Between Components
            this.$options.components!.PostTree = require('@/components/PostTree.vue').default;
        }

        protected created() {

            // let delta: DeltaOpsInterface[] = [];
            // try{
            //     delta = JSON.parse(this.post.content);
            // } catch(e) {
            //     console.info('illegal json:' + this.post.content);
            // }

            if (this.post.content && this.post.content.length) {
                
                let delta: DeltaOpsInterface[] = [];

                const urlRegex =/(\bhttps?:\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;

                try{
                    delta = JSON.parse(this.post.content);
                } catch(e) {
                    console.info('illegal json:' + this.post.content);
                }
                for (let i = 0; i < delta.length; i++) {

                    if (typeof delta[i].insert === 'object' && delta[i].insert.image){
                        this.imageContent.push(delta[i]);
                    }

                    if (delta[i].insert && typeof delta[i].insert === 'string') {
                        let string = delta[i].insert.replaceAll(/[\r\n]/gi, ' ');
                        let result = null;

                        while((result = urlRegex.exec(string)) !== null) {
                            if (result && result.length) {
                                for (let j = 0; j < result.length; j++) {
                                    this.links_set.add(result[j]);
                                }
                            }
                        }
                        // also search for link
                        // if (delta[i].attributes && delta[i].attributes.link) {
                        //     links_set.add(delta[i].attributes.link);
                        // }
                    }
                }

                if (this.imageContent.length == 0) {
                    this.htmlContent = convertQuillDeltaToHTML(this.post.content);
                }
            }

            const text: DeltaOpsInterface[] = [];
            let totalTextLength: number = 0;

            // for (let i = 0; i < delta.length; i++) {
                // for videos and images

            // const foldDepth = IS_MOBILE ? 2 : 4;
            // if (this.depth === foldDepth) {
            //     this.onFold();
            // }
            // when user blocked the post, we fold it, note, in this case when don't have to generate short content
            if ((this.$store.state.User.id && this.$store.getters['User/isBlocked'](this.post.user_id))
                || (this.post.is_ban && this.post.is_ban > 0) || this.post.deleted == 2) {
                this.$store.commit('Post/addFolded', this.post.id);
            }

        }

        protected mounted() {

            if (this.$route.params && this.$route.params.post_id
                && parseInt(this.$route.params.post_id) == this.post.id) {

                // scroll to this post
                const postEle = document.getElementById(this.post.id + '') as HTMLDivElement;
                const rect = postEle.getBoundingClientRect();

                window.scrollTo(0, rect.top - 150);

                this.isAnchorPost = true;

                setTimeout(() => {
                    this.isAnchorPost = false;
                }, 3000);
            }

            if (this.links_set.size) {

                this.links_set.forEach((url: string) => {

                    if (url.indexOf('twitter.com') !== -1) {
                        twitterWidget(this.content, url);
                        
                    } 
                    // else if (url.indexOf('facebook.com') !== -1){
                    //     facebookSDK(this.content, url);
                    // } 
                    else {
                        const data = new FormData;

                        data.append('url', url);

                        this.$store.dispatch('Thread/linkPreview', data)
                        .then((response: Response) => {
                            const data: {title: string, image: string, description: string} = response.getData();

                            insertCard(this.content, url, data);
                        });
                    }
                });
            }
        }

        protected beforeDestroy() {
            delete this.post;
        }

        @Watch('$route', {immediate: true})
        protected onRouteUpdate(): void {
            if (this.$route.params && this.$route.params.post_id
                && parseInt(this.$route.params.post_id) == this.post.id) {

                // scroll to this post
                const postEle = document.getElementById(this.post.id + '') as HTMLDivElement;
                const rect = postEle.getBoundingClientRect();

                window.scrollTo(0, rect.top - 150);

                this.isAnchorPost = true;

                setTimeout(() => {
                    this.isAnchorPost = false;
                }, 3000);
            }
        }

        protected onFold() {

            if (this.contentShort === null) {

                let delta: DeltaOpsInterface[] = [];
                this.contentShort = '';
                try{
                    delta = JSON.parse(this.post.content);
                }catch(e) {
                    // catch when delta is broken
                }

                for (let i = 0; i < delta.length; i++) {
                    
                    if (delta[i] && delta[i].insert && typeof delta[i].insert === 'string') {
                        this.contentShort = this.contentShort + delta[i].insert;

                        if (this.contentShort.length > 50) {
                            this.contentShort = this.contentShort.substring(0, 50) + '...';
                            break;
                        }
                    }
                }
            }
        
            this.$store.commit('Post/addFolded', this.post.id);
        }

        protected unFold() {
            this.$store.commit('Post/removeFolded', this.post.id);
        }

        @Watch('newPostNotification')
        protected onNewPostNotification(postData: PostInterface): void {

            // dynamically add new post to post tree, when parent_id = post.id
            if (postData && postData.thread_id == this.post.thread_id // todo add group_id
            && postData.id && postData.parent_id == this.post.id){
                this.onNewPost(postData);

                this.$nextTick(() => {
                    this.$store.commit('Post/clearNewPost');
                });
            }
        }

        protected onNewPost(postData: PostInterface): void {

            if (!this.post.children) {
                this.post.children = [];
            }

            this.post.children.unshift(postData);
        }

        protected onEditPost(postData: PostInterface): void {
            this.post.content = postData.content;
            // update html content
            this.htmlContent = convertQuillDeltaToHTML(this.post.content);

            if (postData.attached_files) {
                this.post.attached_files = postData.attached_files;
            }
        }

        protected onPostDelete(post: PostInterface): void {
            if (post.id === this.post.id) {
                this.post.deleted = post.deleted;
                if (this.post.deleted == 2) {
                    this.$store.commit('Post/addFolded', this.post.id);
                }
                if (parseInt(post.deleted_by as unknown as string) != post.user_id) {
                    this.post.deleted_by = this.$store.state.User;
                }
            }
        }
    }
</script>

<style lang="scss" scoped>

    $avatar-size: $avatar-size1;
    $avatar-margin-right: var(--p2);
    $name-time-size: $font-size1 - 0.1;

    .post-block {
        padding: var(--p6) 0 0;

        .main {
            display: flex;

            .avatar-div {
                flex-shrink: 0;
                margin-right: $avatar-margin-right;

                &.mobile {
                    margin-right: var(--p4);
                }
               
            }
            
            .poster-info {

                padding-top: var(--p1);
                height: 28px;
                line-height: 24px;

                &.mobile {
                    height: auto;
                    line-height: 1.5rem;
                }

                &.level-one {
                    height: 34px;
                    line-height: 24px;
                    padding-top: var(--p2);

                    &.mobile {
                        line-height: 1.5rem;
                        height: auto;
                    }
                }

                .name, .time-string {
                    font-size: $name-time-size;
                }

                .ipfs {
                    display: inline-block;
                    position: relative;
                    bottom: 1px;

                    .dropdown-link {
                        $size: 15px;
                        width: 12px;
                        height: 14.4px;
                        display: flex;
                    }
                }

                .dot-dot {
                    color: var(--desc-color);
                }
            }

            .post-body {
                flex: 1 1 auto;
                /* it should be calc(100% - avartar-size - margin-right), 
                but unless user typeing a really long word, 
                the editor will spill over the right border by (avartar-size - margin-right)px
                it's fine if your typing normal text with space in it */
                max-width: 100%;

                .post-content {
                    @include content_font;
                    @include wrap_words;
                    line-height: $category-line-height;
                    transition: all 2s ease-in-out 1s;

                    &.new {
                        background-color: var(--high-color);
                    }
                }

                .post-action {
                    font-size: $name-time-size;
                    padding: var(--p3) 0 0;
                    
                    &.mobile {
                        padding: var(--p5) 0 0;
                    }
                }
            }
        }

        .nested {
            margin-left: var(--nested-margin-left);
            &.unindent {
                margin-left: 0;
            }

            // &.mobile {
            //     margin-left: 52px;
            //     &.unindent {
            //         margin-left: 0;
            //     }
            // }
        }
    }

    .folded {
        position: relative;
        display: flex;
        flex-direction: row;
        justify-content: flex-start;
        align-items: center;
        padding: var(--p7) 0 0;

        .avatar-div {
            flex-shrink: 0;
            margin-right: $avatar-margin-right;
        }

        .name, .time-string {
            font-size: $name-time-size;
        }

        .brief {
            cursor: pointer;

            &:hover {
                opacity: .7;
            }

            .content-short {
                margin-left: var(--p2);
                color: var(--font-color3);
                @include wrap_words;
            }
        }
    }

    .fold-line {

        $hover-area: 16px;
        $line-width: 2px;

        position: absolute;
        top: var(--fold-line-top);
        left: $avatar-size / 2 - (($hover-area - $line-width) / 2);
        width: $hover-area;
        height: calc(100% - 54px);
        cursor: pointer;

        &:before {
            content: "";
            position: absolute;
            width: $line-width;
            height: 100%;
            top: 0;
            left: ($hover-area - $line-width) / 2;
            background-color: var(--border-color1);
            box-shadow: 0 0 1px 0 var(--border-color1);
        }

        &:hover:before {
            background-color: $border-focus;
        }

        &.level-one {
            top: 80px;
            left: 12px;
            height: calc(100% - 64px);

            &.mobile {
                top: 64px;
            }
        }
    }
</style>
<style lang="scss">
    // special global logic to override ant-design/quill style
    .post-content {
        p {
            margin-bottom: 0;
        }
        .ql-image {
            max-width:100%;
            max-height: 800px;
        }

        @include link_preview;
    }
</style>
