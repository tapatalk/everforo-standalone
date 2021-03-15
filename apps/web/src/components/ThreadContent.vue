<template>
    <section>
        <a-row
            class="thread-info"
            type="flex"
            justify="start"
            align="middle"
        >
            <UserAvatar
                :scale="3"
                :avatar="thread.user && thread.user.photo_url"
                :profileId="thread.user.id"
                :is-ban="$store.getters['BanUser/isBan'](thread.first_post.user_id, thread.first_post.is_ban)"
                :online="thread.first_post.online"
            />
            <div
                :class="{'mobile-name-date-ipfs-area':isMobile}"
            >
                <Username
                    :username="thread.user && thread.user.name"
                    :profileId="thread.user.id"
                />
                <Dot/>
                <TimeString
                    :time="thread.created_at"
                />
<!--                <Dot-->
<!--                    v-if="thread && thread.ipfs"-->
<!--                />-->
<!--                <span class="ipfs">-->
<!--                    <IPFS-->
<!--                        v-if="thread && thread.ipfs"-->
<!--                        :ipfs-hash="thread.ipfs"-->
<!--                    />-->
<!--                </span>-->
                <span 
                v-if="$store.getters['ThreadPin/isPin'](thread.id, thread.is_pin)"
                 class="pin-user">
                    <Dot/>
                    <Icons type="pin" />
                    {{$t('pin_by', {username: pinUser})}}
                </span>
            </div>
        </a-row>
        <div class="thread-title">{{thread.title}}</div>
        <PostContentHidden
            v-if="thread.first_post 
            && (thread.first_post.deleted == 1 || thread.first_post.deleted == 3 || $store.getters['User/isBlocked'](thread.first_post.user_id) ||
            thread.first_post.nsfw == 1 ||
            $store.getters['BanUser/isBan'](thread.first_post.user_id, thread.first_post.is_ban))"
            :post="thread.first_post"
        />
        <div
            v-else
            class="thread-content"
            ref="content"
            v-html="htmlContent"
            :class = "[{'mobile':isMobile}]"
        ></div>
        <AttachedFiles
                v-if="thread.first_post
            && !(thread.first_post.deleted == 1 || thread.first_post.deleted == 3 || $store.getters['User/isBlocked'](thread.first_post.user_id) ||
            thread.first_post.nsfw == 1 ||
            $store.getters['BanUser/isBan'](thread.first_post.user_id, thread.first_post.is_ban))"
            :attached-files="thread.first_post.attached_files"
        />
    </section>
</template>
<script lang="ts">
    import {Component, Prop, Ref, Vue} from 'vue-property-decorator';
    // import "@/declaration/vue-meta";
    import {MetaInfo} from 'vue-meta';
    import RequestMethods from '@/http/RequestMethods';
    import {DeltaOpsInterface, PostInterface, ThreadInterface} from '@/helpers/Interfaces';
    import {Response} from '@/http/Response';
    import {IS_MOBILE, convertQuillDeltaToHTML, insertCard, parseUnicode, twitterWidget, facebookSDK} from "@/helpers/Utils";
    import AttachedFiles from "@/components/AttachedFiles.vue";
    import Dot from '@/components/Dot.vue';
    import IPFS from '@/components/IPFS.vue';
    import PostContentHidden from '@/components/PostContentHidden.vue';
    import TimeString from '@/components/TimeString.vue';
    import UserAvatar from '@/components/UserAvatar.vue';
    import Username from '@/components/Username.vue';

    @Component<ThreadContent>({
        components: {
            AttachedFiles,
            Dot,
            IPFS,
            PostContentHidden,
            TimeString,
            UserAvatar,
            Username,
        },
        metaInfo(): MetaInfo {
            return {
                meta: [
                    // Twitter Card
                    {name: 'twitter:card', content: this.$i18n.t('everforo') as string},
                    {name: 'twitter:title', content: (this as unknown as ThreadContent).thread.title},
                    {name: 'twitter:description', content: (this as unknown as ThreadContent).htmlContent.substring(0, 100)},
                    // image must be an absolute path
                    {name: 'twitter:image', content: process.env.VUE_APP_DOMAIN + '/img/logo@2x.png'},
                    // Facebook OpenGraph
                    {property: 'fb:app_id', content: process.env.VUE_APP_ID as string},
                    {property: 'og:url', content: process.env.VUE_APP_DOMAIN + "/g/" + this.$route.params.group_name + "/thread/" + this.$route.params.thread_slug},
                    {property: 'og:title', content: (this as unknown as ThreadContent).thread.title},
                    {property: 'og:site_name', content: this.$i18n.t('everforo') as string},
                    {property: 'og:type', content: 'website'},
                    {property: 'og:image', content: process.env.VUE_APP_DOMAIN + '/img/logo@2x.png'},
                    {property: 'og:description', content: this.htmlContent.substring(0, 100)}
                ],
            }
        }
    })
    export default class ThreadContent extends Vue {

        @Ref('content')
        readonly content!: HTMLDivElement;

        @Prop()
        public thread!: ThreadInterface;

        protected isMobile: boolean = IS_MOBILE;

        protected links_set: Set<string> = new Set();

        get htmlContent(): string {
            if (this.thread.first_post && this.thread.first_post.content && this.thread.first_post.deleted != 1) {
                return convertQuillDeltaToHTML(this.thread.first_post.content);
            }

            return '';
        }

        get pinUser():string | undefined
        {
            if (this.$store.getters['ThreadPin/pinUser'](this.thread.id))
            {
                return this.$store.state.User.name;
            }
            return this.thread.pin_user;
        }

        protected created(){
            if (this.thread.first_post.content && this.thread.first_post.content.length) {
                
                let delta: DeltaOpsInterface[] = [];

                const urlRegex =/(\bhttps?:\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
                
                try{
                    delta = JSON.parse(this.thread.first_post.content);
                } catch(e) {
                    console.info('illegal json:' + this.thread.first_post.content);
                }
                for (let i = 0; i < delta.length; i++) {

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
                    }
                }
            }
        }

        protected mounted() {
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
            delete this.thread;
        }



    }
</script>
<style lang="scss" scoped>

    section {

        padding: 0 var(--p6);

        .thread-info {
            
            .mobile-name-date-ipfs-area {
                display: inline-block;
                width: calc(100vw - 100px);
                margin-left: var(--p4);

            }

            .name {
                margin-left: var(--p2);
                &.mobile {
                    font-size: 1rem;
                    margin-left: 0px;
                    line-height: 2rem;
                }
            }

            .time-string {
                
                &.mobile {
                    font-size: 1rem;
                }
            }

            .ipfs {
                display: inline-block;
                position: relative;
                bottom: 1px;

                .dropdown-link {
                    width: 15px;
                    height: 18px;
                    display: flex;
                }
            }
            .dot-dot {
                color: var(--desc-color);
            }
        }

        .thread-title {
            @include title_font;
            padding: var(--p4) 0 0;
        }

        .thread-content {
            @include content_font;
            padding: var(--p2) 0 0;
            font-size: $font-size2;
            user-select: text;

            &.mobile {
                font-size: 1rem;
            }
        }

    }

    .deleted {
        padding-bottom: var(--p4);
    }
</style>
<style lang="scss">
    .thread-content {

        p {
            margin-bottom: 0;
        }

        ul {
            list-style: disc;
            padding-inline-start: 40px;
        }

        .ql-image {
            max-width: 100%;
        }

        @include link_preview;

        @media (min-width: 1200px) {
            .ql-video {
                width: 100%;
                border-radius: $border-radius1 * 2;
                height: 480px; // (900 - 48) * 9 / 16
            }
        }

        @media (min-width: 1000px) and (max-width: 1200px) {
            .ql-video {
                width: 100%;
                border-radius: $border-radius1 * 2;
                height: 423px; // (800 - 48) * 9 / 16
            }
        }

        @media (max-width: 1000px) {
            .ql-video {
                width: 100%;
                border-radius: $border-radius1;
                height: 380px; // (700 - 24) * 9 / 16
            }
        }
    }
    .pin-user {
            color: var(--desc-color);
            .ico {
                font-size: 1.2rem;
                color: var(--desc-color);
            }
        }
</style>