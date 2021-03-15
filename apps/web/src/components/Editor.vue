<template>
    <div
        :class="['editor-box', {'focus': hasFocus}, {'mobile': isMobile}]"
        v-on:keyup.enter="(e) => prepareData(e)"
    >
        <VueScrollBar
            :max-height="maxHeight ? maxHeight : 300"
            :min-height="minHeight? minHeight : 0"
            :scroll-to="scrollPos"
            :pasting="pasting"
            v-on:click-scroll-wrap="onClickContainer"
            v-on:reset-pasting="pasting = false"
        >
            <div id="confined_box">
                <div
                    ref="editorDiv"
                    v-on:click.self="onClickContainer"
                ></div>
            </div>
        </VueScrollBar>
        <section>
            <div
                v-for="(attach, index) in attached_files"
                :key="index"
                class="attach-block"
            >
                <div class='name-size'>
                    <span>{{attach.fileObj.name}}</span>
                    <span>({{sizeDisplay(attach.fileObj.size)}})</span>
                    <span
                        v-if="attach.progress"
                    >{{attach.progress >= 100 ? $t('uploaded') : attach.progress + '%'}}</span>
                </div>
                <span
                    v-on:click="removeAttachedFiles(index)"
                >
                    <Icons
                        type='chacha'
                    /> 
                </span>
            </div>
        </section>
        <div class="divider"></div>
        <div class="toolbar-box">
            <a-button
                type="primary"
                class="send"
                :disabled="sendDisabled"
                v-on:click="prepareData"
            >{{sendBtnText}}
            </a-button>
            <div
                ref="toolbarDiv"
                v-on:click.self="onClickContainer"
            >
                <button 
                    v-if="showAttachedFiles"
                    :class="['ql-attach', {'disabled': disableAttachFiles}]"
                >
                    <svg viewBox="0 0 24 24" version="1.1">
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <g fill-rule="nonzero">
                                <path d="M8.11339412,23.9703402 C5.98279789,23.9837221 3.92821261,23.1792643 2.37325744,21.7226453 C0.859805966,20.3157498 0,18.3425544 0,16.2761831 C0,14.2098118 0.859805966,12.2366163 2.37325744,10.8297208 L12.139431,1.56549085 C14.4835755,-0.521830283 18.0196692,-0.521830283 20.3638137,1.56549085 C21.4497875,2.57234685 22.0669941,3.98625439 22.0669941,5.46716558 C22.0669941,6.94807677 21.4497875,8.36198432 20.3638137,9.36884031 L10.5976402,18.636067 C9.17975735,19.892463 7.04703089,19.892463 5.62914808,18.636067 C4.9730432,18.0276006 4.60016543,17.1732517 4.60016543,16.2784306 C4.60016543,15.3836094 4.9730432,14.5292605 5.62914808,13.9207942 L15.3953216,4.65206915 C15.878191,4.2038057 16.6250537,4.2038057 17.1079231,4.65206915 C17.3340044,4.86161209 17.4625028,5.15591413 17.4625028,5.46416889 C17.4625028,5.77242366 17.3340044,6.0667257 17.1079231,6.27626864 L7.34174954,15.546492 C7.13811605,15.7348044 7.02233543,15.999573 7.02233543,16.2769322 C7.02233543,16.5542915 7.13811605,16.8190601 7.34174954,17.0073725 C7.77610853,17.4118182 8.44918137,17.4118182 8.88354036,17.0073725 L18.6497139,7.74464085 C19.2822917,7.15617998 19.6416249,6.33113368 19.6416249,5.46716558 C19.6416249,4.60319748 19.2822917,3.77815119 18.6497139,3.18969031 C17.2969099,1.9371026 15.2078331,1.9371026 13.8550291,3.18969031 L4.08885559,12.4554186 C3.02730999,13.442464 2.42425881,14.8266529 2.42425881,16.2761831 C2.42425881,17.7257132 3.02730999,19.1099021 4.08885559,20.0969475 C6.36010056,22.2035633 9.87118271,22.2035633 12.1424277,20.0969475 L21.9086012,10.8312192 C22.3911839,10.3822262 23.1386199,10.3822262 23.6212027,10.8312192 C23.847284,11.0407621 23.9757824,11.3350642 23.9757824,11.6433189 C23.9757824,11.9515737 23.847284,12.2458757 23.6212027,12.4554186 L13.8550291,21.7226453 C12.2998641,23.1798857 10.2445639,23.9844134 8.11339412,23.9703402 Z"></path>
                            </g>
                        </g>
                    </svg>
                </button>
                <button class="ql-bold"></button>
                <button class="ql-italic"></button>
                <button class="ql-underline"></button>
                <button 
                    :class="['ql-image', {'disabled': disableImageInsert}]"
                ></button>
                <button class="ql-emoji"></button>
                <button 
                    ref="linkButton"
                    class="ql-link disabled"
                    v-on:click="linkPopPosition"
                ></button>
                <button 
                    v-if="isThreadEditor" 
                    class="ql-list" value="ordered"></button>
                <button 
                    v-if="isThreadEditor"
                    class="ql-list" value="bullet"></button>
                <!-- <button class="ql-video"></button> -->
                <!--            <select class="ql-size"></select>-->
                <!--            <button class="ql-strike"></button>-->
                <!--            <select class="ql-color"></select>-->
                <!--            <select class="ql-background"></select>-->
                <!--            <select class="ql-font"></select>-->
                <!--            <button class="ql-script" value="sub"></button>-->
                <!--            <button class="ql-script" value="super"></button>-->
                <!--            <button class="ql-header" value="1"></button>-->
                <!--            <button class="ql-header" value="2"></button>-->
                <!--            <button class="ql-blockquote"></button>-->
                <!--            <button class="ql-code-block"></button>-->
                <!--            <button class="ql-indent" value="-1"></button>-->
                <!--            <button class="ql-indent" value="+1"></button>-->
                <!--            <button class="ql-direction" value="rtl"></button>-->
                <!--            <select class="ql-align"></select>-->
                <!--            <button class="ql-formula"></button>-->
                <!--            <button class="ql-clean"></button>-->
                <button
                    v-if="!this.sendDisabled"
                    v-on:click="onClearContent"
                    class="editor-reset">
                    <Icons
                        type="shanchu"
                    />
                </button>
            </div>
            <input
                class="image-selector"
                ref="imageSelector"
                type="file"
                multiple="multiple"
                accept="image/*"
                v-on:change="onSelectImage"
            >
            <input
                class="attach-selector"
                ref="attachSelector"
                type="file"
                multiple="multiple"
                v-on:change="onSelectAttach"
            >
        </div>
        <div
            v-if="loadingMask"
            class="loading-mask"
        >
            <div
                v-if="imageUploadPseudoPercentage && imageUploadPseudoPercentage != 100"
                class='progress'
            >{{imageUploadingProgress}}&nbsp;{{imageUploadPseudoPercentage}}%</div>
            <div
                v-else
                class='progress'
            >{{imageUploadingProgress}}</div>
        </div>
    </div>
</template>

<script lang="ts">
    import {Component, Emit, Prop, Ref, Vue, Watch} from 'vue-property-decorator';
    import Quill from 'quill';
    import {cloneDeep} from 'lodash';
    import {IS_MOBILE, UNSUPPORTED_ATTACH_FILES_TYPE, SUPPORTED_IMAGE_TYPE, bindEvent, debugLog, formatBytes, getFileExtension, removeEvent, removeListItemByIndex, sleep} from '@/helpers/Utils';
    import {DeltaInterface, PostInterface, UploadedImageUrl} from '@/helpers/Interfaces';
    import {Response} from '@/http/Response';
    import EmojiBlot from '@/emoji/format-emoji-blot';
    import ShortNameEmoji from '@/emoji/module-emoji';
    import ToolbarEmoji from '@/emoji/module-toolbar-emoji';
    import TextAreaEmoji from '@/emoji/module-textarea-emoji';
    import VueScrollBar from '@/components/VueScrollBar.vue';

    @Component({
        components: {
            VueScrollBar,
        },
    })
    export default class Editor extends Vue {

        @Ref('editorDiv')
        readonly editorDiv!: HTMLDivElement;
        @Ref('toolbarDiv')
        readonly toolbarDiv!: HTMLDivElement;
        @Ref('linkButton')
        readonly linkButton!: HTMLButtonElement;
        @Ref('imageSelector')
        readonly imageSelector!: HTMLInputElement;
        @Ref('attachSelector')
        readonly attachSelector!: HTMLInputElement;

        @Prop()
        public styles!: string;
        @Prop()
        public defaultPost!: PostInterface;
        @Prop()
        public maxHeight!: number;
        @Prop()
        public minHeight!: number;
        @Prop()
        public clearContent!: boolean;
        @Prop()
        public setFocus!: boolean;
        @Prop()
        public submitComplete!: boolean;
        @Prop()
        public mixedContent!: boolean;// whether allow mixed content in post, such as text and images
        @Prop({default: false})
        public isThreadEditor!: boolean;
        @Prop()
        public placeholder!: string;

        protected maxAllowedSize: number = 5;
        protected maxAllowImages: number = 4;
        protected imageCounter: number = 0;

        protected maxAllowedAttachSize: number = 25;
        protected maxAllowAttach: number = this.isThreadEditor ? 5 : 1;
        protected attached_files: {id: number, countDown: number, progress: number, fileObj: File}[] = [];

        protected isMobile: boolean = IS_MOBILE;
        protected editor!: Quill;
        protected hasFocus: boolean = false;

        protected sendDisabled: boolean = true;
        // attachments id
        protected image_attachments: number[] = [];
        protected file_attachments: number[] = [];

        protected sendBtnText: string = ''; //this.$t('send') as string;
        // protected videoModal: boolean = false;
        // protected videoLink: string = '';

        protected uploadExternalImages: boolean = true;
        // the customized scroll bar position
        protected scrollPos: number = 0;

        protected disableImageInsert: boolean = false;
        protected disableAttachFiles: boolean = false;

        protected showAttachedFiles: boolean = false;
        protected deletedAttachedFiles: number[] = [];

        protected formats: string[] = ['bold', 'italic', 'code', 'italic', 'size', 'strike', 'underline', 'image', 'video', 'link', 'emoji'];

        protected loadingMask: boolean = false;
        protected toLoadedCount: number = 0;
        protected totalToLoadedCount: number = 0;
        protected imageUploadingProgress: string = '';
        protected imageUploadPseudoPercentage: number = 0;
        protected imageUploadPseudoCountdown: number = 0;
        protected imageUploadProgressAnimation: number = 0;

        protected pasting: boolean = false;

        get adminStatus(): number {
            return this.$store.getters['GroupExtensions/getFeatureStatus']('adminsAndModerators');
        }

        get isAdmin(): string
        {
            return this.$store.getters['User/isSuperAdmin']() || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 1, this.adminStatus) 
            || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 2, this.adminStatus) 
            || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 3, this.adminStatus);
        }

        private buttonText(sending: boolean) {
            if (sending) {
                this.sendBtnText = this.defaultPost ? this.$t('saving') as string : this.$t('sending') as string;
            } else {
                this.sendBtnText = this.defaultPost ? this.$t('save') as string : this.$t('send') as string;
            }
        }

        protected created() {

            if (this.$store.getters['GroupExtensions/getAttachmentsStatus'] && this.$store.state.Group.attached_files) {

                if (parseInt(this.$store.state.Group.attached_files.allow_everyone) || this.isAdmin) {
                    if (parseInt(this.$store.state.Group.attached_files.allow_post) || this.isThreadEditor) {
                        this.showAttachedFiles = true;
                    } else {
                        this.showAttachedFiles = false;
                    }
                    
                } else {
                    this.showAttachedFiles = false;
                }
                
            } else {
                this.showAttachedFiles = false;
            }

            if (this.defaultPost) {
                if (this.defaultPost.attached_files && this.defaultPost.attached_files.length) {
                    for (let i = 0; i < this.defaultPost.attached_files.length; i++) {
                        this.attached_files.push({id: this.defaultPost.attached_files[i].id, countDown: 0, progress: 0, fileObj: this.defaultPost.attached_files[i]});
                    }
                }
            }

            if (this.isThreadEditor) {
                this.formats.push('list');
                this.maxAllowImages = 20;
            }

            Quill.register({
                'formats/emoji': EmojiBlot,
                'modules/emoji-shortname': ShortNameEmoji,
                'modules/emoji-toolbar': ToolbarEmoji,
                'modules/emoji-textarea': TextAreaEmoji
            }, true); 
        }

        /**
         * initialize editor, bind events
         */
        protected mounted(): void {

            this.buttonText(false);

            this.editor = new Quill(this.editorDiv, {
                theme: 'snow',
                formats: this.formats,
                modules: {
                    "emoji-toolbar": true,
                    "emoji-textarea": true,
                    "emoji-shortname": true,
                    toolbar: {
                        container: this.toolbarDiv,
                        handlers: {
                            'image': () => {
                                if (!this.disableImageInsert) {
                                    this.imageSelector.click();
                                }
                            },
                            // 'emoji': () => {
                            //     // console.log('emoji');
                            // },
                            'attach': () => {
                                this.attachSelector.click();
                            }
                        },
                    },
                },
                bounds: '#confined_box',
                placeholder: this.placeholder,
            });

            if (this.defaultPost) {

                const content = {ops: JSON.parse(this.defaultPost.content)} as any;
                this.editor.setContents(content);
                this.sendDisabled = false;

                this.notAllowMixedContent(content);
            }

            bindEvent(this.editorDiv, 'drop', this.dropImage as EventListener);
            bindEvent(this.editorDiv, 'paste', this.pasteVideo as EventListener);
            // when editor get focus, the parent box get .focus class
            this.editor.on('selection-change', this.onFocus);
            this.editor.on('text-change', this.onContentChange);

            this.setVideoHeight();
        }

        protected beforeDestroy(): void {
            removeEvent(this.editorDiv, 'drop', this.dropImage as EventListener);
            removeEvent(this.editorDiv, 'paste', this.pasteVideo as EventListener);
            this.editor.off('selection-change', this.onFocus);
            this.editor.off('text-change', this.onContentChange);
            // remove from memory
            this.editor = {} as Quill;
        }

        private setVideoHeight() {
            const boxWidth: number = this.editorDiv.getBoundingClientRect().width;
            const videoHeight: number = Math.ceil(boxWidth * 9 / 16);
            const videoIframes: NodeList = this.editorDiv.querySelectorAll('.ql-video');

            for (let i = 0; i < videoIframes.length; i++) {
                (videoIframes[i] as HTMLIFrameElement).setAttribute('style', 'height: ' + videoHeight + 'px;');
            }
        }

        /**
         * when click on editor's box, set the focus to editor
         */
        protected onClickContainer() {
            if (!this.editor.hasFocus()) {
                this.editor.focus();
            }
        }

        /**
         * when editor blur, remove 'focus' class from container
         * @param range
         */
        protected onFocus(range: undefined | {index: number, length: number}, oldRange: undefined | {index: number, length: number}, source: string) {
            this.hasFocus = !!range;

            if (!this.hasFocus && this.sendDisabled) {
                this.onCloseEditor();
            }

            if (source === 'user' && range && range.length) {
                this.linkButton.classList.remove('disabled');
            } else {
                this.linkButton.classList.add('disabled');
            }
        }

        /**
         * on content change, if content empty, disable send
         */
        protected onContentChange(delta: DeltaInterface, oldContents: DeltaInterface, source: String) {

            if (source === 'api') {
                return;
            }

            const content = this.editor.getContents() as DeltaInterface;
            if (!content || !content.ops ||
                (content.ops.length == 1
                && content.ops[0].insert 
                && content.ops[0].insert === '\n')){

                this.sendDisabled = true;
                // when it is cleared
                this.editor.enable();
                this.disableImageInsert = false;
            } else {
                this.sendDisabled = false;
            }
            // when we not allow mixed content
            this.notAllowMixedContent(content);

            if (content.ops.length > 5000) {
                this.sendDisabled = true;
            }
        }

        /**
         * not allow mixed content in comments
         */
        protected notAllowMixedContent(content: DeltaInterface) {
            let clean = true;
            this.imageCounter = 0;
            // when we not allow mixed content
            if(content.ops.length && !this.mixedContent) {
                for (let i = 0; i < content.ops.length; i++) {
                    if (typeof content.ops[i].insert === 'string' && content.ops[i].insert !== '\n') {
                        // it means we got some text
                        if (!this.isThreadEditor) {
                            this.disableImageInsert = true;
                        }

                        // console.log(content.ops[i].insert);

                    } else if (typeof content.ops[i].insert === 'object' && content.ops[i].insert.image){
                        if (!this.isThreadEditor && clean) {
                            // this.editor.disable();
                            this.removeTextFromContent(content);
                            this.disableAttachFiles = true;

                            clean = false;
                        }

                        this.imageCounter += 1;
                    }
                }
            }

            if (this.disableImageInsert == false) {
                if (this.imageCounter >= this.maxAllowImages) {
                    this.disableImageInsert = true;
                } else {
                    this.disableImageInsert = false;
                }
            }
        }

        /**
         * 
         */
        protected removeTextFromContent(content: DeltaInterface) {
            
            let newcontent = cloneDeep(content);
            let setNew: boolean = false;

            if(content.ops.length && !this.mixedContent) {
                for (let i = 0; i < content.ops.length; i++) {
                    if (typeof content.ops[i].insert === 'string' && content.ops[0].insert !== '\n') {
                        removeListItemByIndex(newcontent.ops, i);
                        setNew = true;
                    }
                }
            }

            if (setNew) {
                this.editor.setContents(newcontent as any, 'api');
            }
        }

        @Watch('setFocus', {immediate: true})
        protected function(val: boolean){
            if(val){
                this.$nextTick (() => {
                    this.editor.focus();
                });
            } else {
                this.$nextTick (() => {
                    this.editor.blur();
                    this.hasFocus = false;
                });
            }
        }

        @Watch('clearContent')
        protected onClearContent(val: boolean) {
            if(val){
                this.editor.setContents([] as any, 'api');
                this.editor.focus();
                this.sendDisabled = true;

                this.attached_files = [];
                this.image_attachments = [];
                this.file_attachments = [];
            }
        }

        /**
         * select images from local system
         */
        protected onSelectImage(e: Event) {
            if (this.imageSelector.files && this.imageSelector.files.length){

                this.toLoadedCount = (this.imageSelector.files.length + this.imageCounter) >= this.maxAllowImages ? (this.maxAllowImages - this.imageCounter) : this.imageSelector.files.length;

                if (this.toLoadedCount <= 0) {
                    return;
                }

                this.totalToLoadedCount = this.toLoadedCount;

                this.loadingMask = true;
                this.imageUploadingProgress = this.$t('uploading_image_progress', {count: (this.totalToLoadedCount-this.toLoadedCount) + ' of ' + this.totalToLoadedCount}) as string;

                this.readImages(this.imageSelector.files, this.insertDroppedImage);
            }
        }

        /**
         * drop image event listener
         * @param e
         */
        protected dropImage(e: DragEvent): void {
            e.preventDefault();
            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {

                if (this.disableImageInsert) {
                    return;
                }

                this.toLoadedCount = (e.dataTransfer.files.length + this.imageCounter) >= this.maxAllowImages ? (this.maxAllowImages - this.imageCounter) : e.dataTransfer.files.length;
                
                if (this.toLoadedCount <= 0) {
                    return;
                }

                this.totalToLoadedCount = this.toLoadedCount;

                this.loadingMask = true;
                this.imageUploadingProgress = this.$t('uploading_image_progress', {count: (this.totalToLoadedCount-this.toLoadedCount) + ' of ' + this.totalToLoadedCount}) as string;

                this.readImages(e.dataTransfer.files, this.insertDroppedImage)
            }
        }

        /**
         * if we found matched video url, we insert video into editor
         */
        protected getVideoUrl(url: string): string {
            const regExpYT = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
            const regExpVM = /https?:\/\/(www\.)?vimeo.com\/(\d+)($|\/)/;
            const regExDM = /https?:\/\/(www\.)?dailymotion\.com\/video\/([\w\d]+)/;
            let match: RegExpMatchArray | null = [];

            if (match = url.match(regExpYT)){

                const videoID: string | null = (match && match[2].length === 11) ? match[2] : null;

                return videoID ? "https://www.youtube.com/embed/" + videoID : '';
            }else if(match = url.match(regExpVM)) {

                const videoID = (match && match[2].length > 0) ? match[2] : null;

                return videoID ? "https://player.vimeo.com/video/" + videoID : '';
            }else if (match = url.match(regExDM)) {

                const videoID = (match && match[2].length > 0) ? match[2] : null;

                return videoID ? "//www.dailymotion.com/embed/video/" + videoID : '?autoplay=1&mute=1';
            }

            return '';
        }

        /**
         * paste image event listener
         * @param e
         */
        protected pasteVideo(e: ClipboardEvent): void {

            this.pasting = true;

            const paste = (e.clipboardData || (window as any).clipboardData).getData('text');

            if (paste === '' && this.disableImageInsert) {
                e.preventDefault();
                return;
            }

            // 10 is just a random threshold, if it's too short, it's unlikely a video link
            if (paste && typeof paste === 'string' && paste.length > 10 && !this.disableImageInsert) {

                const videoUrl = this.getVideoUrl(paste.trim());

                if (videoUrl) {
                    e.preventDefault();

                    let index = this.editor.getLength();
                    // if user has focused in the editor
                    const range = this.editor.getSelection(true);
                    // get the caret position
                    if (range) {
                        index = range.index;
                    }

                    this.editor.insertEmbed(index, 'video', videoUrl, 'user');

                    this.setVideoHeight();
                }

                // todo, append url card
                // if (paste.substring(0, 4) === 'http') {
                //     console.log(paste);    

                //     const img = document.createElement('img');

                //     img.setAttribute('src', 'fasd fads');

                //     document.activeElement.appendChild(img);
                // }
            }
        }

        /**
         * read files info from event
         * @param files
         * @param callback
         */
        protected readImages(files: FileList | DataTransferItemList, callback: EventListener): void {
            [].forEach.call(files, (file: File) => {

                if (!file.size) {
                    this.$message.error(this.$t('uploaded_file_empty') as string);
                    return;
                }

                if (file.size > (this.maxAllowedSize * 1048576)) {
                    this.$message.error(this.$t('uploaded_file_exceed_max_size', [this.maxAllowedSize, (file.size / 1048576).toFixed(2)]) as string);
                    return;
                }

                if (!file.type.match(SUPPORTED_IMAGE_TYPE)) {
                    // file is not an image
                    // Note that some file formats such as psd start with image/* but are not readable
                    // todo add allowed extension
                    this.$message.error(this.$t('uploaded_file_type_not_allowed', [file.type]) as string);
                    return;
                }
                const reader = new FileReader();

                bindEvent(reader, 'load', callback);

                reader.readAsDataURL(file);
            });
        }

        /**
         * insert drag and dropped image to editor content
         * @param e
         */
        protected insertDroppedImage(e: Event): void {
            this.imageCounter += 1;
            if (this.imageCounter > this.maxAllowImages) {
                return;
            }

            if (this.imageUploadProgressAnimation) {
                cancelAnimationFrame(this.imageUploadProgressAnimation);

                this.imageUploadPseudoPercentage = 0;
            }

            this.imageUploadProgress();

            // base64 encoded file data
            const dataUrl = (e.target as FileReader).result;

            this.$store.dispatch('Attachment/uploadAttach', {dataUrl: dataUrl})
            .then((uploadedUrl: UploadedImageUrl) => {
                if (uploadedUrl.id) {

                    this.image_attachments.push(uploadedUrl.id);

                    this.insertEditor(uploadedUrl.url as string);

                    this.toLoadedCount -= 1;

                    this.imageUploadingProgress = this.$t('uploading_image_progress', {count: (this.totalToLoadedCount-this.toLoadedCount) + ' of ' + this.totalToLoadedCount}) as string;
                }
            })
            .catch(() => {
                // when upload failed, just ignore it
                this.toLoadedCount -= 1;
            })
            .finally(() => {
                if (this.toLoadedCount <= 0) {
                    this.loadingMask = false;

                    this.totalToLoadedCount = 0;

                    this.imageUploadPseudoPercentage = 100;

                    // auto submit immediately 
                    if (!this.isThreadEditor) {
                        this.prepareData(null);
                    } 

                }
            });
            
        }

        protected imageUploadProgress() {
            // console.log(this.attached_files[fileIndex]);
            this.imageUploadProgressAnimation = requestAnimationFrame(() => {this.imageUploadProgress()});

            this.imageUploadPseudoCountdown += 1;

            if (this.imageUploadPseudoCountdown % 6 == 0) {
                this.imageUploadPseudoPercentage += 3;
            }

            if (this.imageUploadPseudoPercentage >= 99) {
                cancelAnimationFrame(this.imageUploadProgressAnimation);
            }
        }

        /**
         * insert base64 encoded image data to editor
         * @param dataUrl
         */
        protected insertEditor(dataUrl: string): void {
            // default insert position is the end of the content
            let index = this.editor.getLength();
            // if user has focused in the editor
            const range = this.editor.getSelection();
            // get the caret position
            if (range) {
                index = range.index;
            }

            this.editor.insertEmbed(index, 'image', dataUrl, 'user');
            // set editor coursor after the image
            this.$nextTick(() => {
                this.editor.setSelection((range!.index as any) + 1);
            });
        }

        private assignImageAttributes(blot: any, attributes: any, imageObject: HTMLImageElement) {
            // if quill can't handle the extra attributes,
            // we will have to figure out something else
            blot.attributes = {
                thumb_url: attributes.thumb_url ? attributes.thumb_url : '',
                id: attributes.id ? attributes.id : '',
                width: imageObject.width,
                height: imageObject.height,
            }
        }

        protected onSelectAttach(e: Event) {
            if (this.attachSelector.files && this.attachSelector.files.length){
                this.readAttachedFiles(this.attachSelector.files);
            }
        }

        protected removeAttachedFiles(attach_index: number) {

            if (this.attached_files[attach_index] && this.attached_files[attach_index].id) {
                this.deletedAttachedFiles.push(this.attached_files[attach_index].id);
            }

            this.file_attachments = this.file_attachments.filter(item => item != this.attached_files[attach_index].id);
            // console.log(this.file_attachments, this.attached_files[attach_index].id);
            removeListItemByIndex(this.attached_files, attach_index);

            if (!this.attached_files.length) {
                const content = this.editor.getContents() as DeltaInterface;

                if (!content || !content.ops ||
                    (content.ops.length == 1
                    && content.ops[0].insert 
                    && content.ops[0].insert === '\n')){
                        this.disableImageInsert = false;
                }
            }
        }

        protected readAttachedFiles(files: FileList | DataTransferItemList): void {
            [].forEach.call(files, (file: File) => {

                if (!file.size) {
                    this.$message.error(this.$t('uploaded_file_empty') as string);
                    return;
                }

                if (file.size > (this.maxAllowedAttachSize * 1048576)) {
                    this.$message.error(this.$t('uploaded_file_exceed_max_size', [this.maxAllowedAttachSize, (file.size / 1048576).toFixed(2)]) as string);
                    return;
                }

                if (UNSUPPORTED_ATTACH_FILES_TYPE. indexOf(getFileExtension(file.name).toUpperCase()) !== -1) {
                    // file is not an image
                    // Note that some file formats such as psd start with image/* but are not readable
                    // todo add allowed extension
                    this.$message.error(this.$t('uploaded_file_type_not_allowed', [getFileExtension(file.name)]) as string);
                    return;
                }

                this.insertAttachedFiles(file);

            });
        }

        private insertAttachedFiles(file: File): void {

            if (this.attached_files.length >= this.maxAllowAttach) {
                this.$message.error(this.$t('max_allowed_attached_files', {max_allowed: this.maxAllowAttach}) as string);
                return;
            }
            // todo upload images
            this.attached_files.push({id: 0, countDown: 0, progress: 0, fileObj: file});

            this.uploadProgress(this.attached_files.length - 1);
            this.$store.dispatch('Attachment/uploadAttachedFiles', file)
            .then((response: Response) => {

                const data = response.getData();

                for (let i = 0; i < this.attached_files.length; i++) {
                    if (file === this.attached_files[i].fileObj) {
                        this.attached_files[i].progress = 100;
                        if (data && data.attached_file_id) {
                            this.attached_files[i].id = data.attached_file_id;
                        }
                    }
                }

                if (data && data.attached_file_id) {
                    this.file_attachments.push(data.attached_file_id);
                    if (!this.isThreadEditor) {
                        // if there are attatched files, disable inline image
                        this.disableImageInsert = true;
                    }
                    // we can submit attached files alone
                    this.sendDisabled = false;
                }

                // auto submit immediately 
                // if (!this.isThreadEditor) {
                //     this.prepareData(null);
                // } 
            });
        }

        protected uploadProgress(fileIndex: number) {
            // console.log(this.attached_files[fileIndex]);
            const animation = requestAnimationFrame(() => {this.uploadProgress(fileIndex)});

            if (!this.attached_files[fileIndex].countDown) {
                this.attached_files[fileIndex].countDown = 1;
            } else {
                this.attached_files[fileIndex].countDown += 1;
            }

            if (this.attached_files[fileIndex].countDown % 6 == 0) {
                if (!this.attached_files[fileIndex].progress) {
                    this.attached_files[fileIndex].progress = 3;
                } else {
                    this.attached_files[fileIndex].progress += 3;
                }
            }

            if (this.attached_files[fileIndex].progress >= 99) {
                cancelAnimationFrame(animation);
            }
        }

        protected sizeDisplay(bytes: number) {
            return formatBytes(bytes);
        }

        /**
         * submit by click or ctrl+enter
         * @param e
         */
        protected async prepareData(e: KeyboardEvent | MouseEvent | null) {

            if (e instanceof KeyboardEvent && !e.ctrlKey) {
                return;
            }

            const content = this.editor.getContents() as DeltaInterface;

            if (!content || !content.ops ||
                (content.ops.length == 1 
                && content.ops[0].insert 
                && content.ops[0].insert === '\n')){
                if (this.file_attachments.length == 0) {
                    return;
                } else {
                    content.ops[0].insert = '';
                }
            }            

            for (let i = 0; i < content.ops.length; i++ ){
                if (content.ops[i].insert && content.ops[i].insert.image){

                    let base64Data:string = '';

                    const imageObj = new Image();
                    
                    if (content.ops[i].insert.image.search(/^https?/) !== 0){

                        base64Data = content.ops[i].insert.image;
                        imageObj.src = content.ops[i].insert.image;

                    // we are not going to re-submit cdn files
                    } else if (this.uploadExternalImages 
                    && content.ops[i].insert.image.search(/cdn.everforo.com/) === -1
                    && content.ops[i].insert.image.search(/tapatalk-cdn.com/) === -1){
                        
                        // if it's an external link image, try to get the file content and upload it
                        // const xhr = new XMLHttpRequest();
                        // xhr.onload = () => {
                        //     const reader = new FileReader();
                        //     reader.onloadend = () => {
                        //         base64Data = reader.result as string;
                        //     }
                        //     reader.readAsDataURL(xhr.response);
                        // };
                        // xhr.open('GET', content.ops[i].insert.image);
                        // xhr.responseType = 'blob';
                        // xhr.send();


                        imageObj.crossOrigin = 'anonymous';
                        // image.crossOrigin = 'use-credentials';
                        // create an empty canvas element
                        const canvas = document.createElement("canvas");
                        const canvasContext = canvas.getContext("2d");

                        imageObj.onload = function () {
                            
                            //Set canvas size is same as the picture
                            canvas.width = imageObj.width;
                            canvas.height = imageObj.height;
                        
                            // draw image into canvas element
                            canvasContext!.drawImage(imageObj, 0, 0, imageObj.width, imageObj.height);
                        
                            // get canvas contents as a data URL (returns png format by default)
                            base64Data = canvas.toDataURL();
        
                        };

                        imageObj.src = content.ops[i].insert.image;

                        // wait maximum 5 seconds
                        for (let j = 0; j < 50; j++){
                            if (base64Data) {
                                break;
                            }
                            await sleep(100);
                        }
                        // if we don't get the file in 5 seconds, we give up uploading the image, juts use the external link
                        if (!base64Data) {
                            continue;
                        }
                    }

                    if (base64Data.length > (this.maxAllowedSize * 1048576)) {
                        this.$message.error(this.$t('uploaded_file_exceed_max_size', [this.maxAllowedSize, (base64Data.length / 1048576).toFixed(2)]) as string);
                        return;
                    }

                    if (base64Data){

                        // this.$message.info(this.$t('image_uploading') as string);

                        let uploadedUrl: UploadedImageUrl = await this.$store.dispatch('Attachment/uploadAttach', {dataUrl: base64Data});

                        if (uploadedUrl.id) {

                            this.image_attachments.push(uploadedUrl.id);

                            content.ops[i].insert.image = uploadedUrl.url;

                            this.assignImageAttributes(content.ops[i], uploadedUrl, imageObj);

                            // this.$message.success(this.$tc('image_uploaded', i, {n: i}) as string);
                        }
                    }
                }
                // this will eliminate the new line (\n), I don't know why we have this code at thew first place
                // if (content.ops[i].insert && typeof content.ops[i].insert === 'string'){
                    // content.ops[i].insert = content.ops[i].insert.trim();
                // }
            }

            this.onSubmit(content.ops);
        }

        @Emit('on-submit')
        protected onSubmit(content: any[]): FormData {
            const data = new FormData();

            data.append('content', JSON.stringify(content));

            if (this.image_attachments.length) {
                data.append('image_attachments', this.image_attachments.join(','));
            }

            if (this.file_attachments.length) {
                data.append('file_attachments', this.file_attachments.join(','));
            }

            if (this.deletedAttachedFiles.length) {
                data.append('deleted_attached_files', this.deletedAttachedFiles.join(','));
            }

            this.sendDisabled = true;
            this.buttonText(true);

            return data;
        }

        @Watch('submitComplete')
        protected onSubmitComplete(val: boolean) {
            if (val) {
                const content = this.editor.getContents() as DeltaInterface;
                if (!content || !content.ops ||
                    (content.ops.length == 1
                    && content.ops[0].insert 
                    && content.ops[0].insert === '\n')){
                    this.sendDisabled = true;
                } else {
                    this.sendDisabled = false;
                }
                this.buttonText(false);

                this.disableImageInsert = false;
                this.disableAttachFiles = false;
            }
        }

        @Emit('close-editor')
        protected onCloseEditor() {
            /**
             * when there is no content, blur trigger close editor
             */
        }

        protected linkPopPosition() {
            setTimeout(() => {
                const tip = document.querySelector('.ql-tooltip.ql-editing.ql-flip') as HTMLDivElement;

                const left = parseInt(tip.style.left);
                const top = parseInt(tip.style.top);

                if (left < 0) {
                    tip.style.left = '0';
                }

                if (top < 0) {
                    tip.style.top = '10px';
                }
            }, 0);
        }

    }
</script>
<style lang="scss" scoped>
    /* manually import quill editor style */
    @import '~quill/dist/quill.core.css';
    @import '~quill/dist/quill.snow.css';

    .ql-container.ql-snow,
    .ql-toolbar.ql-snow {
        border: 0;
    }

    .mobile {
        .ql-container.ql-snow {
            min-height: 120px;
        }
    }

    .ql-container.ql-snow {
        min-height: 150px;
    }

    .ql-container {
        @include content_font;
        font-family: inherit;
    }

    .editor-box {

        position: relative;

        // &:not(.mobile) {
            border: $border-width $border-style var(--border-color5);
            border-radius: $border-radius1;
            @include transition(all);

            &.focus {
                border-color: var(--theme-color);
                box-shadow: $box-shadow-focus;
            }
        // }

        // &.mobile {
        //     .scrollbar-wrap {
        //         // background-color: var(--input-bg);
        //     }
        // }

        .attach-block {
            position: relative;
            width: 90%;
            height: 30px;
            margin: 0 0 var(--p2) var(--p4);
            padding: 0 var(--p2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--input-bg);
            border: $border-width $border-style var(--border-color5);

            .name-size {

                display: flex;
                align-items: center;
                justify-content: flex-start;
                max-width: 80%;
                span:first-child {
                    display: inline-block;
                    max-width: 70%;
                    overflow: hidden;
                    white-space: nowrap;
                    text-overflow: ellipsis;
                }
                span:last-child {
                    margin-left: var(--p2);
                }
            }

            .ico {
                font-size: $font-size0;
                cursor: pointer;
            }
        }

        .toolbar-box {
            position: relative;
            display: flex;
            flex-direction: row;
            justify-content: flex-start;
            align-items: center;
            padding: var(--p3) var(--p4) var(--p3) var(--p4);

            .reset {
                @include capitalize;
                margin-right: var(--p6);
                font-weight: $title-weight;
                order: 1;
            }

            .send {
                @include capitalize;
                margin-right: var(--p4);
                font-weight: $title-weight;
                order: 2;
                border-radius: 4px;
                padding-left: var(--p8);
                padding-right: var(--p8);
            }

            .ql-toolbar {
                display: inline-block;
                padding: 0;
                order: 3;
                flex-grow: 1;

                button.disabled {
                    opacity: 0.5;
                }

                .ql-attach {
                    svg {
                        width: 14px;
                        height: 14px;
                        g {
                            fill: var(--font-color2);
                        }
                    }
                }

            }
        }

        .divider {
            height: 1px;
            margin-left: var(--p4);
            margin-right: var(--p4);
            background: var(--border-color5);
        }

        .image-selector, .attach-selector {
            display: none;
        }

        .loading-mask {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: $mask-color;
            border-radius: $border-radius1;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            color: $font-color-contrast;
            font-size: $font-size4;
        }
    }
</style>
<style lang="scss">
    
    @import '@/emoji/styles.scss';
    // @import '@/emoji/emoji.scss';

    .ql-editor.ql-blank {

        &:before {

            font-size: $font-size1;
            color: var(--desc-color);
            font-style: normal;
        }

        -webkit-user-select:text;
    }

    .ql-snow {
        .ql-editor {
            -webkit-user-select:text;
            @include content_font;

            img {
                max-width: 88%;
            }

            .ql-video {
                width: 100%;
            }
        }

        .ql-stroke {
            stroke: var(--font-color2);
        }

        button.editor-reset {
            .ico {
                color: var(--font-color2);
                font-size: 14px;
                vertical-align: top;
            }

            padding-right: 0;
            text-align: right;
            float: right;

            &:focus {
                outline: none;
            }
        }

        .ql-tooltip {
            background-color: var(--hover-bg);
            border-color: var(--border_color1);
            box-shadow: $box-shadow;

            input {
                background-color: var(--input-bg);
                color: var(--font-color1);
            }
        }
    }
</style>