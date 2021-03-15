<template>
    <section>
        <div
                v-if="videoHtml"
                class="video-container"
                v-html="videoHtml"
                ref="container"
        ></div>
    </section>
</template>
<script lang="ts">
    import {Component, Prop, Ref, Vue} from 'vue-property-decorator';
    import {DeltaOpsInterface} from '@/helpers/Interfaces';

    @Component
    export default class VideoBlock extends Vue {
        @Ref('container')
        readonly container!: HTMLDivElement;

        @Prop()
        public videos!: DeltaOpsInterface[];

        get videoHtml(): string {

            if (!this.videos || !this.videos.length) {
                return '';
            }

            const QuillDeltaToHtmlConverter = require('quill-delta-to-html').QuillDeltaToHtmlConverter;

            let converter: any = new QuillDeltaToHtmlConverter([this.videos[0]], {});
            const html = converter.convert();
            converter = null;
            return html;
        }

        protected mounted() {

            const ratio = 9 / 16;
            const width = this.container.getBoundingClientRect().width;
            const height = ratio * width;
            const iframe = this.container.querySelectorAll('iframe.ql-video');

            for (let i = 0; i < iframe.length; i++) {
                iframe[i].setAttribute('style', "width:" + width + "px; height:" + height + "px;");
            }
        }

    }
</script>
<style lang="scss" scoped>
    .video-container {
        position: relative;
        width: 100%;
        height: auto;
        border-radius: $border-radius1 * 2;
        overflow: hidden;
    }
    
    @media only screen and (max-width: 700px) {
        .video-container {
            border-radius: $border-radius1;
        }
    }
</style>
<style lang="scss">
    .video-container iframe {
        border-radius: $border-radius1 * 2;
        overflow: hidden;
    }
    @media only screen and (max-width: 700px) {
        .video-container iframe {
            border-radius: $border-radius1;
        }
    }
</style>
