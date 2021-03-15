<template>
    <div
        class="media-holder"
        :style="{width: width, height: height}"
        ref="drop"
        v-on:click="onHolderClick"
        v-on:dragover.prevent
        v-on:drop="dropMedia"
    >
        <div class="camera">
            <svg width="24px" height="20px" viewBox="0 0 24 20" version="1.1">
                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                    <rect fill="#FFFFFF" opacity="0.00999999978" x="0" y="0" width="24" height="24"></rect>
                    <g fill="#606878" fill-rule="nonzero" stroke="#606878" stroke-width="0.2">
                        <path d="M1.23214286,20.0188268 C0.535714302,20.0188268 0,19.4938924 0,18.8114777 L0,4.74323625 C0,4.06082157 0.535714279,3.53588717 1.23214286,3.53588719 L4.28571428,3.53588719 C5.03571428,3.43090032 6.05357142,3.16843312 6.32142857,2.27604466 L6.53571429,1.2261759 C6.75000001,0.22880057 7.98214287,0.0713202594 8.83928571,0.018826815 L15.1607143,0.018826815 C16.875,0.018826815 17.3035714,0.858721836 17.4107143,1.2261759 L17.6785714,2.48601841 C17.9464286,3.06344623 18.4285714,3.37840686 19.125,3.53588719 L22.7678571,3.53588719 C23.4642857,3.53588719 24,4.06082157 24,4.74323625 L24,18.8114777 C24,19.4938924 23.4642857,20.0188268 22.7678571,20.0188268 L1.23214286,20.0188268 Z M9.37499999,1.27866932 C8.35714285,1.27866932 8.14285713,1.75111028 7.875,2.43352497 L7.66071428,3.11593968 C7.12500001,4.42827563 5.03571428,4.69074283 4.125,4.74323625 L2.41071429,4.74323625 C1.39285714,4.74323625 1.28571428,5.16318376 1.28571428,5.53063783 L1.28571428,17.761609 C1.28571428,18.4965171 1.49999999,18.7589843 2.03571429,18.7589843 L21.75,18.7589843 C22.3392857,18.7589843 22.7142857,18.3915302 22.7142857,17.761609 L22.7142857,5.53063783 C22.7142857,5.21567721 22.6071429,4.74323625 21.5892857,4.74323625 L18.5892857,4.74323625 C17.3571428,4.5332625 16.5535714,3.90334123 16.3392857,2.95845937 L16.125,2.38103155 L15.9642857,2.0660709 C15.75,1.64612339 15.5892857,1.2261759 14.7857143,1.2261759 L9.37499999,1.27866932 Z M12,17.6566221 C8.67857143,17.6566221 5.99999999,15.0319502 5.99999999,11.777357 C5.99999999,8.52276383 8.67857141,5.8980919 12,5.8980919 C15.3214286,5.8980919 18,8.52276381 18,11.777357 C18,15.0319502 15.3214286,17.6566221 12,17.6566221 Z M12,7.10544098 C9.37499999,7.10544098 7.28571429,9.20517852 7.28571429,11.7248636 C7.28571429,14.2445486 9.42857143,16.3442861 12,16.3442861 C14.625,16.3442861 16.7142857,14.2445486 16.7142857,11.7248636 C16.7142857,9.20517852 14.625,7.10544098 12,7.10544098 L12,7.10544098 Z M8.35714285,11.777357 L8.35714285,11.5673832 C8.46428569,9.73011292 9.91071427,8.26029663 11.7857143,8.20780318 L12,8.20780318 L12,8.89021789 L11.7857143,8.89021789 C10.2857143,8.94271134 9.10714286,10.1500604 8.99999999,11.6198767 L8.99999999,11.8298504 L8.35714285,11.777357 L8.35714285,11.777357 Z"></path>
                    </g>
                </g>
            </svg>
        </div>
        <div class="desc">
            <span>{{description}}</span>
        </div>
        <input
            type="file"
            ref="input"
            v-on:change="selectMedia"
            accept="image/*"
        >
        <img ref="img">
        <div
            class="camera-overlay"
            :style="{width: width, height: height}"
        >
            <a-icon type="camera" />
        </div>
        <img class="profile-modal-camera" src="/img/camera.png">
        <video
            ref="video"
            loop
        ></video>
    </div>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Ref, Vue, Watch} from 'vue-property-decorator';
    import {SUPPORTED_IMAGE_TYPE, bindEvent} from "@/helpers/Utils";
    import {LocaleMessage} from "vue-i18n";

    @Component
    export default class MediaInput extends Vue {
        @Ref('drop')
        readonly dropBox!: HTMLDivElement;
        @Ref('input')
        readonly input!: HTMLInputElement;
        @Ref('img')
        readonly img!: HTMLImageElement;
        @Ref('video')
        readonly video!: HTMLVideoElement;

        @Prop()
        public width!: string;
        @Prop()
        public height!: string;
        @Prop()
        public clearFile!: boolean;
        @Prop()
        public defaultMedia!:string;
        @Prop()
        public description!: string;
        @Prop()
        public cameraColor!: string;
        @Prop()
        public borderRadius!: number;
        @Prop({default: 5})
        public maxAllowedSize!: number;
        @Prop({default: false})
        public noPreview!: boolean;

        // protected maxAllowedSize: number = 20;
        protected fileType: string = '';

        private displayDefaultMedia(){

            if (this.defaultMedia){
                this.img.setAttribute('src', this.defaultMedia);
                this.dropBox.classList.add('displaying');
                this.img.classList.add('show');
                if (this.borderRadius){
                    this.img.style.borderRadius = this.borderRadius + 'px';
                    this.video.style.borderRadius = this.borderRadius + 'px'
                }
            }
        }

        protected mounted(){
            this.displayDefaultMedia();
        }

        protected onHolderClick() {
            this.input.click();
        }

        /**
         * when parent try to clear file object, remove the displayed media
         */
        @Watch('clearFile', {immediate: true})
        protected onClearFile(newValue: boolean): void {
            if (newValue) {
                this.dropBox.classList.remove('displaying');
                this.input.value = '';
                this.img.classList.remove('show');
                this.img.removeAttribute('src');
                this.video.classList.remove('show');
                this.video.removeAttribute('src');
                // tell parent component file cleared, it might need to reset some value
                this.fileCleared();
                // if there is a default media, display it, 
                // note that if parent component needs to clear the default media
                // it must be parent component clear its own default media value before invoke this call
                this.displayDefaultMedia();
            }
        }

        /**
         * send the file cleared notice flag to parent component
         */
        @Emit('file-cleared')
        protected fileCleared(): boolean {
            return true;
        }

        /**
         * choose image from file system
         * @param e
         */
        protected selectMedia(e: Event): void {
            const target = e.target as HTMLInputElement;

            if (target.files && target.files.length) {
                this.readFiles(target.files[0] as File);
            }
        }

        /**
         * drop image event listener
         * @param e
         */
        protected dropMedia(e: DragEvent): void {
            e.preventDefault();

            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
                this.readFiles(e.dataTransfer.files[0] as File);
            }
        }

        /**
         * pass the uploaded file object to parent component
         */
        @Emit('file-uploaded')
        protected fileUploaded(fileObject: string | Blob): string | Blob {
            return fileObject;
        }

        // @Emit('file-display')
        // protected fileDisplayed(mediaRatio: number): number {
        //     return 250 * mediaRatio;
        // }

        /**
         * read files info from select/drop event
         * @param files
         */
        protected readFiles(file: File): void {

            if (!file.size) {
                this.$message.error(this.$t('uploaded_file_empty') as string);
                return;
            }

            if (file.size > (this.maxAllowedSize * 1048576)) {
                this.$message.error(this.$t('uploaded_file_exceed_max_size', [this.maxAllowedSize, (file.size / 1048576).toFixed(2)]) as string);
                return;
            }

            // this.$message.info(this.$t('start_reading_file_preview'));

            this.fileType = file.type;

            if (file.type.match(SUPPORTED_IMAGE_TYPE)) {
                const reader = new FileReader();

                bindEvent(reader, 'progress', this.loadingProgress as EventListener);
                bindEvent(reader, 'error', this.loadingError as EventListener);
                bindEvent(reader, 'load', this.displayImage as EventListener);

                reader.readAsDataURL(file);
            }
            // else if (file.type.match(/^video\/(mp4)/i)) {
            //     const reader = new FileReader();
            //
            //     bindEvent(reader, 'progress', this.loadingProgress as EventListener);
            //     bindEvent(reader, 'error', this.loadingError as EventListener);
            //     bindEvent(reader, 'load', this.displayVideo as EventListener);
            //
            //     reader.readAsArrayBuffer(file);
            // }
            else {
                this.$message.error(this.$t('uploaded_file_type_not_allowed', [file.type]) as string);
                return;
            }
        }

        /**
         * display loading progress, might not needed if limited size is small
         * @param e
         */
        protected loadingProgress(e: ProgressEvent): void {
            if (e.lengthComputable) {
                // const loaded = (e.total - e.loaded) / 100;
                // console.info(loaded);
            }
        }

        /**
         * show load failed error, can't simulate this case, just leave it here
         * @param e
         */
        protected loadingError(e: ProgressEvent): void {
            this.$message.error(this.$t('file_loading_error') as string);
        }

        /**
         * display image preview
         * @param e
         */
        protected displayImage(e: ProgressEvent): void {
            // base64 encoded file data
            const dataUrl = (e.target as FileReader).result as string;

            this.fileUploaded(dataUrl);
            if (!this.noPreview) {
                this.img.setAttribute('src', dataUrl);
            }

            const img = new Image();

            img.onload = () => {
                this.dropBox.classList.add('displaying');
                this.img.classList.add('show');
                this.video.classList.remove('show');
                if(this.borderRadius){
                    this.img.style.borderRadius = this.borderRadius + 'px';
                    this.video.style.borderRadius = this.borderRadius + 'px'
                }
                // this.fileDisplayed(img.height / img.width);
            };

            img.src = dataUrl;
        }

        /**
         * display video preview
         * @param e
         */
        // protected displayVideo(e: ProgressEvent): void {
        //     // file blob
        //     const blob = new Blob([(e.target as FileReader).result as BlobPart], {type: this.fileType});
        //     const video = this.$refs.video as HTMLVideoElement;
        //
        //     bindEvent(video, 'loadedmetadata', () => {
        //         this.dropBox.classList.add('displaying');
        //         this.img.classList.remove('show');
        //         this.video.classList.add('show');
        //         const videoSize = this.video.getBoundingClientRect();
        //         this.fileDisplayed(videoSize.height / videoSize.width);
        //         this.sendNotice(this.$t('cover_video_ready_upload'));
        //         video.play();
        //     });
        //
        //     video.src = (URL || webkitURL).createObjectURL(blob);
        // }
    }

</script>
<style lang="scss" scoped>
    .media-holder {
        @include media_holder;

        float: left;

        input, img, video {
            display: none;
        }


        .camera-overlay {
            display: none;
            background-color: rgba(0, 0, 0, 0.15);
            position: absolute;
            border-radius: 50%;

            i {
                display: block;
                font-size: 17px;
                color: #ffffff;
                margin-top: 27px;
            }
        }

        .profile-modal-camera {
            display: none;
        }


        img.show,
        video.show {
            max-width: 100%;
            max-height: 100%;
            display: initial;
            width: 100%;
            height: 100%;
            object-fit: cover;
            -o-object-fit: cover;
        }
    }
</style>