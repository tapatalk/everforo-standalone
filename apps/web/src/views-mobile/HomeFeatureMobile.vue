<template>
    <div>
        <div 
            class="cover"
        >
            <div class="title">Modern Touch to Old School Forum Software</div>
            <div class="desc">Everforo combines the beauty of good old forum software, the ease of use of Facebook Groups and the future of decentralization on blockchain.</div>
        </div>
        <div class="graphs">
            <div class="title">Threaded View</div>
            <div class="desc">Whether you are old enough to remember the original Newsgroup or young enough to use Reddit. Threaded View is a fun way to organize interactions between users, allows you to easily switch between sorting the posts chronologically or by popularity.</div>
            <img src="/img/home-feature/1.png">
        </div>
        <div class="graphs">
            <div class="title">Sub Forums</div>
            <div class="desc">Or Categories, Tags, whatever you call it. You can categorize the group discussions into different sub forums. Just like the legacy forum software but we make it works just like a modern hashtag.</div>
            <img src="/img/home-feature/2.png">
        </div>
        <div class="graphs">
            <div class="title">Easy to Moderate</div>
            <div class="desc">Simply add another member as an admin to start administrating. Because all content is stored in the blockchain, you can always recover deleted content.</div>
            <img src="/img/home-feature/3.png">
        </div>
        <div class="graphs">
            <div class="title">Advanced Post Editor</div>
            <div class="desc">While replies are posted and organized like Facebook Groups, the topic starter can post a long post with a combination of images, videos and paragraphs. Just like writing a long blog except you get way more interactions with your fellas.</div>
            <img src="/img/home-feature/4.png">
        </div>
        <div 
            class="footer"
        >
            <div class="title">More to Come</div>
            <div class="desc">The journey has just started! Everforo team is dedicated to bring you the best, modern community group experience that emphasize in fairness and transparency. Check back often and let us know what you think! Already own a forum and want to see how it looks in Everforo? Contact us today and see if we can work something out for you!</div>
            <div class="contact">
                <a href="mailto:support@everforo.com">Contact us</a>
            </div>
            <ul>
                <li
                    v-for="(doc, index) in docs"
                    :key="index"
                >
                    <a :href="doc.link">{{$t(doc.name)}}</a>
                </li>
            </ul>
        </div>
    </div>
</template>
<script lang="ts">
    import {Component, Vue} from 'vue-property-decorator';
    import {windowWidth} from '@/helpers/Utils';

    @Component({
        components: {
        }
    })
    export default class HomeFeature extends Vue {
        
        protected docs: { name: string, link: string }[] = [
            {name: 'privacy_policy', link: 'privacy'},
            {name: 'everforo_inc', link: ''},
        ];
    }
</script>
<style lang="scss" scoped>
    .cover {
        position: relative;
        width: 100%;
        max-height: 600px;
        background-image: url("/img/home-feature/cover.png");
        background-size: cover;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: $font-color-contrast;
        text-align: center;
        padding: 50px 30px;

        .title {
            font-size: 21px;
            line-height: 25px;
            font-weight: 500;
            margin-bottom: var(--p8);
        }

        .desc {
            font-size: 16px;
            line-height: 20px;
        }
    }

    .graphs {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: stretch;
        padding: var(--p8) var(--p6);

        .title {
            font-size: 16px;
            font-weight: 500;
            line-height: 24px;
            margin-bottom: 8px;
            margin-top: 30px;
        }

        .desc {
            font-size: 16px;
            line-height: 24px;
            color: var(--font-color1);
        }

        img {
            display: block;
            max-width: 100%;
            margin: 8px auto 0 auto;
        }
    }

    .footer {
        position: relative;
        width: 100%;
        background-image: url("/img/home-feature/footer.png");
        background-size: cover;
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: $font-color-contrast;
        padding: 50px 30px;

        .title {
            font-size: 21px;
            line-height: 25px;
            font-weight: 500;
            margin-bottom: var(--p8);
        }

        .desc {
            font-size: 16px;
            line-height: 20px;
        }

        .contact {
            height: 40px;
            width: 220px;
            font-size: 16px;
            line-height: 40px;
            background-color: #ffffff;
            padding: 0 var(--p8);
            border-radius: 20px;
            margin-bottom: 24px;
            margin-top: 24px;

            a {
                color: var(--font-color1);
            }
        }

        ul{
            margin-top: var(--p8);
            li {
                display: inline-block;
                padding: 0 var(--p6);
                border-right: 1px solid var(--font-color2);
                &:last-of-type{
                    border-right: 0;
                }
                a{
                    @include description_font();
                    color: $font-color-contrast;
                }
            }
        }
    }
</style>
