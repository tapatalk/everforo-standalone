<template>
    <div
        v-if="attachedFiles && attachedFiles.length"
        class='attached-files'
    >
        <div
            v-for="attached_file in attachedFiles" 
            :key="attached_file.id"
            class="attached-file"
            v-on:click="downloadAttachment(attached_file.url)"
        >
            <div class="l">
                <span
                    :style="{backgroundImage: 'url(' + mimeTypeIcon(attached_file.mime_type + ')')}"
                    :title="attached_file.mime_type"
                ></span>
            </div>
            <div class="r">
                <span>{{attached_file.name}}</span>
                <span>{{sizeDisplay(attached_file.size)}}</span>
            </div>
        </div>
    </div>
</template>
<script lang="ts">
    import {Component, Prop, Vue} from 'vue-property-decorator';
    import {formatBytes} from '@/helpers/Utils';

    @Component({
        components: {

        },
    })
    export default class AttachedFiles extends Vue {
        @Prop()
        public attachedFiles!: {};

        protected mimeTypeIcon(mimeType: string) {
            if (mimeType.match(/^application\/pdf/)) {
                return '/img/file-icons/pdf@2x.png';
            }else if (mimeType.match(/^application\/(zip|vnd\.rar|x\-7z\-compressed)/)) {
                return '/img/file-icons/zip@2x.png';
            }else if (mimeType.match(/^text\//)) {
                return '/img/file-icons/txt@2x.png';
            }else if (mimeType.match(/^image\//)) {
                return '/img/file-icons/image@2x.png';
            }else if (mimeType.match(/^application\/(msword|vnd\.openxmlformats\-)/)) {
                return '/img/file-icons/doc@2x.png';
            } else {
                return '/img/file-icons/file_default@2x.png';
            }
        }

        protected sizeDisplay(bytes: number) {
            return formatBytes(bytes);
        }

        protected downloadAttachment(url: string) {
            if (this.$store.state.User.id) {
                window.open(url,'_blank');
            } else {
                this.$store.commit('setShowLoginModal', true);
            }
        }

    }
</script>
<style lang="scss" scoped>
    .attached-files {
        position: relative;
        display: flex;
        justify-content: flex-start;
        align-items: center;
        flex-wrap: wrap;
        padding-top: var(--p4);

        .attached-file {
            display: flex;
            width: auto;
            margin: 0 var(--p4) var(--p2) 0;
            padding: var(--p2);
            background-color: var(--input-bg);
            border-radius: $border-radius1;
            border: $border-width $border-style var(--border-color1);
            cursor: pointer;
            
            .l {
                width: 40px;
                height: 40px;
                overflow: hidden;

                span {
                    display: block;
                    width: 100%;
                    height: 100%;
                    background-size: 100% 100%;
                }
            }

            .r {
                width: auto;
                max-width: 200px;
                height: 40px;

                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding-left: var(--p2);

                span {
                    display: block;
                    width: 100%;
                    height: 1em;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    font-size: $font-size0;

                    &:first-child {
                        font-size: $upload-desc-font-size;
                        margin-bottom: var(--p2);
                        font-weight: $title-weight;
                        white-space: nowrap;
                    }
                }
            }
        }
    }
</style>
