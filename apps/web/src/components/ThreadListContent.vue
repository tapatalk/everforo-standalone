<template>
    <section>
        <router-link
            :to="{name: 'thread', params: thread.group ? {thread_id: thread.id, thread_slug: thread.slug, group_name: thread.group.name} : {thread_id: thread.id, thread_slug: thread.slug}}"
        >
            <a-row
                class="thread-info"
                type="flex"
                justify="start"
                align="middle"
            >
                <UserAvatar
                    :scale="2"
                    :avatar="thread.user ? thread.user.photo_url : ''"
                    :profileId="thread.user ? thread.user.id: 0"
                    :is-ban="$store.getters['BanUser/isBan'](thread.first_post.user_id,
                     thread.first_post.is_ban)"
                    :online="thread.online"
                />
                <Username
                    :username="thread.user ? thread.user.name : ''"
                    :profileId="thread.user ? thread.user.id : 0"
                />
                <span
                    class="category-tringle"
                    v-on:click="onCategoryClicked"
                    v-if="!isMobile">
                    <Icons type="category_tringle"/>
                </span>
                <span
                    class="category-name"
                    v-on:click="onCategoryClicked"
                    v-if="(thread.category && thread.category.id) && !isMobile">
                    {{(thread.category && thread.category.id)? categorySymbol + thread.category.name : thread.group.title}}
                </span>
                <span
                    class="category-name"
                    v-on:click="onCategoryClicked"
                    v-else-if="!isMobile">
                    {{$route.name === 'group' && $store.state.Category.categories && $store.state.Category.categories.length ? $t('uncategorized') : (thread.group ? thread.group.title : ($store.state.Group ? $store.state.Group.title : ''))}}
                </span>
                <Dot />
                <TimeString
                    :time="thread.created_at"
                />
                <Dot v-if="thread.is_pin" />
                <Icons v-if="thread.is_pin" type="pin" />
                <UnreadMark
                    v-if="thread.unread"
                />
                
            </a-row>
            <div
                v-if="thread.first_post && thread.first_post.deleted != 1 && thread.first_post.deleted != 3 &&
                !$store.getters['User/isBlocked'](thread.first_post.user_id) &&
                !$store.getters['BanUser/isBan'](thread.first_post.user_id, thread.first_post.is_ban)"
                :class="['thread-title', {'has-media': (images.length || videos.length)},
                {'has-category': (thread.category && thread.category.id)}, {'mobile': isMobile}]"
            >{{thread.title}}
            </div>
            <PostContentHidden
                v-if="thread.first_post && !thread.is_pin
                && (thread.first_post.deleted == 1 || thread.first_post.deleted == 3 || $store.getters['User/isBlocked'](thread.first_post.user_id) ||
                 thread.first_post.nsfw == 1 ||
                 $store.getters['BanUser/isBan'](thread.first_post.user_id, thread.first_post.is_ban))"
                :post="thread.first_post"
            />
            <VideoBlock
                v-else-if="videos.length && !thread.is_pin"
                :videos="videos"
            />
            <ImageBlock
                v-else-if="images.length && !thread.is_pin"
                :images="images"
            />
            <div
                v-else-if="textContent && !thread.is_pin"
                :class="['thread-content', {'overflow':textOverflow}, {'max-height': maxHeight}, {'mobile': isMobile}]"
                v-html="textContent"
                ref="content"
            ></div>
            <PostInfo
                v-if="thread.first_post.deleted != 1 && thread.first_post.deleted != 3 && !$store.getters['User/isBlocked'](thread.first_post.user_id) &&
                !$store.getters['BanUser/isBan'](thread.first_post.user_id, thread.first_post.is_ban) && !thread.is_pin
                && (thread.posts_count || thread.likes_count)"
                :posts-count="parseInt(thread.posts_count)"
                :likes-count="parseInt(thread.likes_count)"
                :is-first-post="false"
            />
        </router-link>
    </section>
</template>
<script lang="ts">
    import {Component, Prop, Ref, Vue} from 'vue-property-decorator';
    import {CATEGORY_SYMBOL, IS_MOBILE} from "@/helpers/Utils";
    import {DeltaOpsInterface, ThreadInterface, PostInterface} from '@/helpers/Interfaces';
    import ImageBlock from '@/components/ImageBlock.vue';
    import PostContentHidden from '@/components/PostContentHidden.vue';
    import PostInfo from '@/components/PostInfo.vue';
    import TimeString from '@/components/TimeString.vue';
    import UnreadMark from "@/components/UnreadMark.vue";
    import UserAvatar from '@/components/UserAvatar.vue';
    import Username from '@/components/Username.vue';
    import VideoBlock from '@/components/VideoBlock.vue';
    import Dot from '@/components/Dot.vue';
    import CategoryMark from '@/components/CategoryMark.vue';
    import {RawLocation} from "vue-router";

    @Component({
        components: {
            ImageBlock,
            PostContentHidden,
            PostInfo,
            TimeString,
            UnreadMark,
            UserAvatar,
            Username,
            VideoBlock,
            Dot,
            CategoryMark,
        },
    })
    export default class ThreadListContent extends Vue {

        @Ref('content')
        readonly content!: HTMLDivElement;

        @Prop()
        public thread!: ThreadInterface;

        readonly maxCharacter: number = IS_MOBILE ? 180 : 310;

        protected videos: DeltaOpsInterface[] = [];
        protected images: DeltaOpsInterface[] = [];
        protected textContent: string = '';
        protected textOverflow: boolean = false;
        protected maxHeight: boolean = false;

        protected isMobile:boolean = IS_MOBILE;
        protected categorySymbol: string = CATEGORY_SYMBOL;

        protected threadTitleSlug: string = '';

        protected created(): void {
            if (!this.thread.first_post || !this.thread.first_post.content 
                || this.thread.first_post.deleted == 1) {
                return;
            }

            let delta: DeltaOpsInterface[] = [];
            try{
                delta = JSON.parse(this.thread.first_post.content);
            } catch(e) {
                console.info('illegal json:' + this.thread.first_post.content);
            }
            const text: DeltaOpsInterface[] = [];
            let totalTextLength: number = 0;

            for (let i = 0; i < delta.length; i++) {
                // for videos and images
                if (delta[i] && delta[i].insert && typeof delta[i].insert === 'object') {

                    if (delta[i].insert.video) {
                        this.videos.push(delta[i]);
                    }
                    if (delta[i].insert.image) {
                        this.images.push(delta[i]);
                    }
                    // for text
                } else if (delta[i] && delta[i].insert && typeof delta[i].insert === 'string') {
                    // if we already have 6 lines or 200 characters. that's enough for preview
                    if (text.length >= 6 || totalTextLength > this.maxCharacter) {
                        this.textOverflow = true;
                        continue;
                    }

                    // it's just newline and space.
                    if (delta[i].insert.match(/^[\n\s]+$/)) {
                        // if the previous line doesn't end with newline mark, we can add one newline mark
                        // otherwise just ignore it
                        if (!text[i - 1] || !text[i - 1].insert.match(/\n$/)) {
                            text.push({insert: '\n'});
                        }
                    } else {
                        // if text end with multiple newline mark, leave only one
                        if (delta[i].insert.match(/\n+$/)) {
                            delta[i].insert = delta[i].insert.replace(/\n+$/, '\n');
                        }

                        text.push(delta[i]);
                        totalTextLength = totalTextLength + delta[i].insert.length;
                    }
                }
            }

            // post content is always a json string of Delta, we need to convert it html
            const QuillDeltaToHtmlConverter = require('quill-delta-to-html').QuillDeltaToHtmlConverter;

            let converter: any = new QuillDeltaToHtmlConverter(text, {});
            this.textContent = converter.convert();

            if (this.textContent.length > this.maxCharacter) {
                this.textContent = this.textContent.substring(0, this.maxCharacter) + '...';
                this.textOverflow = true;
            }

            if (!this.videos.length && !this.images.length && this.textContent) {
                // text maximum 8 lines
                this.maxHeight = true;
            }

            converter = null;
        }

        protected beforeDestroy() {
            delete this.thread;
        }

        protected onCategoryClicked(e: Event){
            // console.log("category item clicked");
            e.preventDefault();
            if(this.thread.group != null) {
                this.$router.push({
                    name: 'group',
                    params: {group_name: this.thread.group?.name},
                } as unknown as RawLocation);
            }
        }

    }
</script>
<style lang="scss" scoped>

    .thread-info {
        margin-bottom: var(--p2);

        .name, .unread {
            margin-left: var(--p2);
        }

        .name.mobile {
            margin-left: var(--p4);
        }

        .category-tringle {
            @include info_font;
            margin-left: var(--p2);
            width: 7px;
            height: 10px;
        }

        .category-name {
            @include info_font;
            margin-left: var(--p2);
        }
        .dot-dot {
                color: var(--desc-color);
            }
        .ico {
                color: var(--desc-color);
            }
    }

    .thread-title {
        @include title_font;
        padding: var(--p1) 0 var(--p2);

        &.has-media {
            padding-bottom: var(--p3);
        }

        &.has-category {
            padding-bottom: var(--p1);
        }

        &.mobile {
            @include mobile_thread_title_font;
        }
    }

    .thread-category {
        @include info_font;
        padding: 0 0 var(--p3) 0;

        &.has-media {
            padding: 0 0 var(--p4) 0;
        }

        &.mobile {
            @include mobile_thread_category_font;
            padding: 0 0 12px 0;
        }
    }

    .thread-content {
        position: relative;
        @include content_font;

        &.overflow {
            mask-image: linear-gradient(180deg, #000 60%, transparent);
        }

        &.max-height {
            overflow: hidden;
            max-height: 12.8rem;
        }

        &.mobile {
            @include mobile_thread_content_font;
        }
    }

</style>
<style>
    .thread-content p {
        margin-bottom: 0;
    }
</style>