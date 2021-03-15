<template>
    <div
        v-if="posts.length"
        class="posts"
        :class="[{'mobile':isMobile}]"
    >
        <div
            v-for="(post, index) in posts"
            :key="String(index) + post.id"
            class="item"
        >
            <PostContent
                v-if="post && post.id"
                :post="post"
                :depth="depth"
            />
            <div
                v-else
                class="load-more"
            >
                <span
                    v-on:click="loadMore(index)"
                >{{$tc('load_more', parseInt(post))}}</span>
            </div>
        </div>
        <div
            v-if="hasLoadMore"
            class="editor-box"
        >
            <EditorTrigger
                v-if="!bottomReply"
                :avatar-scale="1"
                :placeholder="replyPlaceholder"
                :triggerFunc="showBottomReply"
            />
            <QuickReply
                v-else
                :post="pesudoPost"
                :is-first-post="false"
                :clear-reply="false"
                :set-editor-focus="true"
                :placeholder="replyPlaceholder"
                v-on:new-post="onNewPost"
                v-on:close-editor="() => {}"
            />
        </div>
    </div>
</template>

<script lang="ts">
    import {Component, Prop, Vue} from 'vue-property-decorator';
    import {Response} from '@/http/Response';
    import {PostInterface} from '@/helpers/Interfaces';
    import {IS_MOBILE, SORT_BY_GROUP, SORT_BY_THREAD} from "@/helpers/Utils";
    import EditorTrigger from "@/components/EditorTrigger.vue";
    import PostContent from "@/components/PostContent.vue";
    import QuickReply from "@/components/QuickReply.vue";

    @Component({
        components: {
            EditorTrigger,
            PostContent,
            QuickReply,
        },
    })
    export default class PostTree extends Vue {

        @Prop()
        public parentPost!: PostInterface;
        @Prop()
        public posts!: PostInterface[];
        @Prop()
        public depth!: number; // how deep the post tree nested

        protected isMobile: boolean = IS_MOBILE;
        protected parentPostId: number = -1;
        protected hasLoadMore: boolean = false;
        protected disableLoadMore: boolean = false;
        protected bottomReply: boolean = false;

        protected pesudoPost: PostInterface = {} as PostInterface;

        protected replyPlaceholder: string = this.$t('share_thoughts') as string;

        protected created() {
            if (this.posts && this.posts[0] && this.posts[0].parent_id != -1) {
                this.parentPostId = this.posts[0].parent_id;
                // with parent post_id and thread_id, it is enough to submit a reply
                this.pesudoPost.id = this.posts[0].parent_id;
                this.pesudoPost.thread_id = this.posts[0].thread_id;
            }

            if (this.posts && this.posts.length && this.posts[this.posts.length - 1]
                && (typeof this.posts[this.posts.length - 1] == 'number' || typeof this.posts[this.posts.length - 1] == 'string')) {
                this.hasLoadMore = true;

                if (this.parentPost.user.id != this.$store.state.User.id) {
                    this.replyPlaceholder = this.$t('reply_to', {username: this.parentPost.user.name}) as string;
                }
            }
        }

        protected beforeDestroy() {
            delete this.posts;
        }

        protected showBottomReply() {
            this.bottomReply = true;
        }

        /**
         * load more sub posts when there is a load more link
         */
        protected loadMore(index: number) {
            // 
            if (this.parentPostId == -1 && this.disableLoadMore) {
                return;
            }

            this.disableLoadMore = true;

            this.$store.dispatch('Thread/loadMore',
                    {post_id: this.parentPostId, sort : this.$route.params.sort ? this.$route.params.sort : SORT_BY_GROUP[1]})
                .then((response: Response) => {

                    // if http status is not 200
                    if (response.getStatus() != 200) {
                        this.$message.info(this.$t('network_error') as string);
                        return;
                    }

                    const responseData: {posts: PostInterface[]} = response.getData();

                    if (typeof this.posts[index] === 'string' || typeof this.posts[index] === 'number') {
                        // use `this.posts.length` becasue we will load the sub post tree from server, 
                        // we don't need to keep the dynamically added posts  
                        this.posts.splice(index, this.posts.length, ...responseData.posts);
                    }
                });

        }

        /**
         * reply via quicl reply bar at the bottom of a post tree
         */
        protected onNewPost(postData: PostInterface): void {
            this.hasLoadMore = false;
            this.posts.push(postData);
        }

    }
</script>

<style lang="scss" scoped>
    .posts {
        margin: 0;
        padding: 0;
        line-height: 1;
        list-style: none;
        position: relative;

        &.mobile {
            padding-top: 10px;
        }

        .item {
            padding: 0;
            position: relative;

            .load-more {
                padding: var(--p6) 0 0;

                span {
                    @include load_more;
                }
            }
        }
    }

    .posts::v-deep .ant-list-item {
        padding: 0;
    }

    .editor-box {

        .editor-trigger-section {
            width: 54%;
            padding-top: var(--p4);
        }
    }
</style>
