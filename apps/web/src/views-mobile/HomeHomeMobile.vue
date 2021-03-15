<template>
    <div>
        <a-row
            class="home-top-row"
        >
            <div class="container">
                <div
                    class="image"
                >
                    <img src="/img/home-home/cover_mobile.png" alt="">
                </div>
                <div class="title">
                    <span>{{$t('home_title')}}</span>
                </div>
                <div class="desc">
                    <span>{{$t('everforo_intro')}} </span>
                </div>
                <div
                        v-if="$store.state.User.id && $store.state.User.activate"
                        class="button"
                        v-on:click="showCreateGroup = true"
                >
                    <span>{{$t('create_a_group')}}</span>
                </div>
                <div
                        v-else
                        class="button"
                        v-on:click="showLoginModal"
                >
                    <span>{{$t('create_a_group')}}</span>
                </div>
                <!-- <div class="go">
                    <router-link :to="{path: `/g/everforo/all` }">
                        <span>{{$t('check_out_development')}}</span>
                    </router-link>
                </div> -->
            </div>
        </a-row>

        <!-- A Fair, Long Lasting Community  -->
        <div class="row-wrapper" >
            <div class="container">
            <a-row 
                class="everforo-intro-row"
            >
                <a-col :span="24">
                    <img src="/img/home-home/home3.png" alt="">
                </a-col>
                <a-col :span="24">
                    <div class="title" v-html="$t('fair_long_lasting_community_title')"></div>
                    <div class="content" v-html="$t('fair_long_lasting_community_desc')"></div>
                </a-col>
            </a-row>
            </div>
        </div>

        <!-- Your Privacy Statys in Your App. -->
         <div class="row-wrapper">
            <div class="container">
                <a-row 
                class="everforo-intro-row"
            >
                <a-col :span="24">
                    <img src="/img/home-home/home4.png" alt="">
                </a-col>
                <a-col :span="24">
                    <div class="title" v-html="$t('your_privacy_stays_in_app_title')"></div>
                    <div class="desc">{{$t('your_privacy_stays_in_app_desc')}}</div>
                    <div class="download-btns">
                        <a target="_blank" v-bind:href="appDownloadLink">
                            <img src="/img/apple-store.png" />
                        </a>
                        <a target="_blank" v-bind:href="appDownloadLink">
                            <img src="/img/google@2x.png" />
                        </a>
                    </div>
                </a-col>
            </a-row>
            </div>
        </div>

        <!-- Better than -->
        <div class="row-wrapper">
            <div class="container">
                <a-row 
                class="everforo-intro-row"
            >
                <a-col :span="24">
                    <video autoplay class="video"  playsinline muted src="/img/home-home/3.mp4"></video>
                </a-col>
                <a-col :span="24" style="margin-top:40px">
                    <div class="title" v-html="$t('better_group_title')"></div>
                    <div class="desc">{{$t('better_group_desc')}}</div>
                </a-col>
            </a-row>
            </div>
        </div>

        <div class="bottom-wrapper" style="height:36px;"/>

        <!-- Censorship Resistance -->
        <div class="row-wrapper theme-bg">
            <div class="container">
                <a-row 
                class="everforo-intro-row"
            >
                <a-col :span="24">
                    <img src="/img/home-home/home5.png" alt="">
                </a-col>
                <a-col :span="24">
                    <div class="title">{{$t('censorship_title')}}</div>
                    <div class="desc" v-html="$t('censorship_desc')"></div>
                </a-col>
                <a-col :span="24">
                    <a-button
                        v-if="$store.state.User.id && $store.state.User.activate"
                        type="primary"
                        size="large"
                        class="create-your-grp"
                        v-on:click="showCreateGroup = true"
                    >
                    {{$t('create_a_group')}}
                    </a-button>
                    <a-button
                        v-else
                        type="primary"
                        size="large"
                        class="create-your-grp"
                        v-on:click="showLoginModal"
                    >
                    {{$t('create_a_group')}}
                    </a-button>

                    <router-link :to="{name: `hometech` }" id="ipfs-learn-more" style="display:inline; padding-left:24px">
                                    <span>{{$t('learn_more')}}</span>
                    </router-link>
                </a-col>
            </a-row>
            </div>
            <div class="bottom-wrapper" style="height:36px;"/>
        </div>

        <div class="bottom-wrapper"/>

        <!-- Get Notified on The Go -->
        <div class="row-wrapper">
            <div class="container">
                <a-row 
                class="home-feature-row"
            >
                <a-col :span="24">
                    <img src="/img/home-home/home2-11.png" alt="">
                </a-col>
                <a-col :span="24">
                    <div class="title">{{$t('notification_intro_title')}}</div>
                    <div class="desc">{{$t('notification_intro_desc')}}</div>
                    <!-- <div class="title">A Better Facebook Groups</div>
                    <div class="desc">Everforo team has years of building blockchain products and realized that the best products are the one that is user-friendly. Everforo works just like Facebook Groups though you will be participating in a platform that has no messy privacy policy.</div> -->
<!-- 
                    <a-button
                        v-if="$store.state.User.id && $store.state.User.activate"
                        type="primary"
                        size="large"
                        class="create-your-grp"
                        v-on:click="showCreateGroup = true"
                    >
                    {{$t('create_your_group')}}
                    </a-button>
                    <a-button
                        v-else
                        type="primary"
                        size="large"
                        class="create-your-grp"
                        v-on:click="showLoginModal"
                    >
                    {{$t('create_your_group')}}
                    </a-button> -->
                </a-col>
            </a-row>
            </div>
        </div>

        <!-- threaded view -->
        <div class="row-wrapper">
            <div class="container">
                <a-row 
                class="home-feature-row"
            >
                <a-col :span="24">
                    <img src="/img/home-home/home2-22.png" alt="">
                </a-col>
                <a-col :span="24">
                    <div class="title">{{$t('thread_view_title')}}</div>
                    <div class="desc">{{$t('thread_view_desc')}}</div>
                </a-col>
            </a-row>
            </div>
        </div>

        <!-- easy to moderate -->
        <div class="row-wrapper">
            <div class="container">
            <a-row 
                class="home-feature-row"
            >
                <a-col :span="24">
                    <img src="/img/home-home/home2-33.png" alt="">
                </a-col>
                <a-col :span="24">
                    <div class="title">{{$t('easy_moderate_title')}}</div>
                    <div class="desc">{{$t('easy_moderate_desc')}}</div>
                </a-col>
            </a-row>
            </div>
        </div>

        <!-- Sub Forums -->
        <div class="row-wrapper">
            <div class="container">
            <a-row 
                class="home-feature-row"
            >
                <a-col :span="24">
                    <img src="/img/home-home/home2-44.png" alt="">
                </a-col>
                <a-col :span="24">
                    <div class="title">{{$t('sub_forums_title')}}</div>
                    <div class="desc">{{$t('sub_forums_desc')}}</div>
                </a-col>
            </a-row>
            </div>
        </div>

        <!-- Advanced Post Editor -->
        <div class="row-wrapper">
            <div class="container">
            <a-row 
                class="home-feature-row"
            >
                <a-col :span="24">
                    <img src="/img/home-home/home2-55.png" alt="">
                </a-col>
                <a-col :span="24">
                    <div class="title">{{$t('advanced_editor_title')}}</div>
                    <div class="desc">{{$t('advanced_editor_desc')}}</div>
                </a-col>
            </a-row>
            </div>
        </div>

        <div class="bottom-wrapper" style="padding: 24px 0"/>

        <!-- <div class="row-wrapper">
            <div class="container">
                <a-row 
                class="home-feature-row"
            >
                <a-col :span="24">
                    <img src="/img/home-home/6.png" alt="">
                </a-col>
                <a-col :span="24">
                    <div class="title">Curating Content</div>
                    <div class="desc">As the group owner you can enable content filtering so that offensive content such as hate speech and pornographic images are prevented from entering into your group. Even though the content is immutable on the blockchain, you can still prevent and remove unwanted content from appearing in your group.</div>
                </a-col>
            </a-row>
            </div>
        </div> -->


<!-- 

        <div class="row-wrapper dark-bg">
            <div class="container">
                <a-row 
                class="home-feature-row"
            >
                <a-col :span="24">
                    <img src="/img/home-home/7.png" alt="">
                </a-col>
                <a-col :span="24">
                    <div class="title">Censorship Resistance</div>
                    <div class="desc">Your group is truly decentralized and your posts are stored in the blockchain, powered by EOS and IPFS. Meaning no one can manipulate your content. And no one can take away your ownership of the content you created.</div>
                </a-col>
            </a-row>
            </div>
        </div>



        <div class="row-wrapper light-grey-bgd">
            <div class="container">
                <a-row 
                class="home-feature-row"
            >
                <a-col :span="24">
                    <img src="/img/home-home/8.png" alt="">
                </a-col>
                <a-col :span="24">
                    <div class="title">Are you a crypto and blockchain enthusiast?</div>
                    <div class="desc">Check our how our system works. We built on top of an existing blockchain (EOS) using IPFS as storage provider. Meaning that your group will be forever in the network, and you can build your own interface to access the content anytime you want.</div>
                    <a-button
                        type="primary"
                        size="large"
                        v-on:click="learnMore"
                        class="create-your-grp"
                    >
                    {{$t('learn_more')}}
                    </a-button>
                </a-col>
            </a-row>
            </div>
        </div> -->




        <div 
            class="footer-wrapper"
        >
            <Footer />
        </div>
        <GroupCreate
                v-if="showCreateGroup"
                v-on:close-create-group="showCreateGroup = false"
        />
    </div>
</template>
<script lang="ts">
    import {Component, Vue} from 'vue-property-decorator';
    import {NAV_BAR_HEIGHT, windowHeight, windowWidth} from "@/helpers/Utils";
    import Footer from '@/components/Footer.vue';
    import GroupCreate from '@/components/GroupCreate.vue';
    import {RawLocation} from "vue-router";
    import {getAppDownloadLinkInStore} from '@/helpers/AppLinkUtil';

    @Component({
        components: {
            Footer,
            GroupCreate,
        }
    })
    export default class HomeHome extends Vue {

        protected showCreateGroup: boolean = false;
        protected appDownloadLink = getAppDownloadLinkInStore();

        protected showLoginModal() {
            this.$store.commit('setShowLoginModal', true);
        }

        protected learnMore() {
            this.$router.push({
                name: 'hometech',
            } as unknown as RawLocation);
            window.scrollTo(0,0);
        }

    }
</script>
<style lang="scss" scoped>
    $margin-left: 5%;
    $margin-right:5%;

    .container {
        max-width: 100%;
        margin: 0 auto;
        overflow: hidden;
    }

    .video {
        max-width: 90%;
        margin-left: $margin-left;
        margin-right: $margin-right;
    }

    .home-top-row {
        padding-top: 40px;
        text-align: center;
        background-color: var(--home-bg);
        padding-bottom: 34px;

        .image {
            width: 100%;
            // margin: 0 auto;
            margin-left: 20px;

            img {
                max-width: 100%;
            }
        }

        .title {
            color: #222532;
            font-size: 1.6rem;
            line-height: 2.1rem;
            font-weight: 500;
            margin-top: 17px;
            margin-left: $margin-left;
            margin-right: $margin-right;
        }

        .desc {
            font-size: 1rem;
            line-height: 1.6rem;
            color: var(--font-color1);
            margin: 14px 0 24px 0;
            margin-left: $margin-left;
            margin-right: $margin-right;
        }

        .button {
            width: 220px;
            height: 40px;
            font-size: 1rem;
            line-height: 40px;
            border-radius: 4px;
            background-color: var(--theme-color);
            color: #ffffff;
            display: inline-block;
            margin-bottom: 13px;
            margin-left: $margin-left;
            margin-right: $margin-right;
        }

        .go {
            font-size: 1rem;
            line-height: 1.6rem;
            color: var(--theme-color);
            text-decoration: underline;
        }
    }

    .everforo-intro-row {
        // padding: 48px 0;
        padding-left: 0;
        padding-right: 0;
        padding-top: 48px;
        padding-bottom: 24x;
        color: var(--font-color1);

        & > div > img {
            height: auto;
            max-height: 250px;
            max-width: 88%;
            margin: 0 auto 30px auto;
            display: block;
        }

        .title {
            font-size: 1.3rem;
            font-weight: 500;
            line-height: 2rem;
            margin-bottom: 14px;
            margin-left: $margin-left;
            margin-right: $margin-right;
        }

        .content, .desc {
            font-size: 1rem;
            line-height: 1.6rem;
            margin-left: $margin-left;
            margin-right: $margin-right;
        }

        .download-btns {
            text-align: center;
            margin-top: 32px;
            margin-left: $margin-left;
            margin-right: $margin-right;

            img {
                width: 150px;
                height: 50px;
            }

            a {
                display: inline-block;
            }

            a:first-child {
                margin-right: 15px;
            }
        }

        .create-your-grp {
            font-size: 1rem;
            line-height: 1.3rem;
            border-radius: 8px;
            background-color: #3D72DE;
            height: 40px;
            width: 220px;
            margin-top: 32px;
            margin-left: $margin-left;
        }
    }

    .home-feature-row {
        padding: 48px 0 0 0;
        color: var(--font-color1);
        margin-left: 5%;
        margin-right: 5%;

        & > div > img {
            height: auto;
            max-height: 250px;
            max-width: 88%;
            margin: 0 auto 30px auto;
            display: block;
        }

        .title {
            font-size: 1.3rem;
            font-weight: 500;
            line-height: 1.6rem;
            margin-bottom: 14px;
        }

        .content, .desc {
            font-size: 1rem;
            line-height: 1.6rem;
        }

        .create-your-grp {
            font-size: 1rem;
            line-height: 1.3rem;
            border-radius: 8px;
            background-color: #3D72DE;
            height: 40px;
            width: 220px;
            margin-top: 32px;
        }
    }

    .row-wrapper {
        &.theme-bg {
            background-color: #374B75;
            .title, .desc{
                color: $font-color-contrast;
            }
        }

        &.gray-bg {
            background-color: rgb(226,226,226);
        }

        &.dark-bg {
            background-color: rgb(55,71,104);
            .title, .desc{
                color: $font-color-contrast;
            }
        }

        &.light-grey-bgd {
            background-color: rgba(250,250,254,1);
        }
    }

    #ipfs-learn-more {
        font-size: 1rem;
        line-height: 1.4rem;
        color: #ffffff;
        text-decoration: underline;
    }
</style>
