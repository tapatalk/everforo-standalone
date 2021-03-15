<template>
    <section
        :class="['mask', {'mobile': isMobile}]"
        v-on:mousedown.self="onCloseMouseDown"
        v-on:mouseup.self="onCloseMouseUp"
    >
        <div
            class="content height-unset-i"
            :style="styles"
            ref="content"
        >
            <slot name="header"></slot>
            <VueScrollBar
                :max-height="scrollHeight"
                :scroll-to="0"
                v-on:reach-bottom="onButtom"
                :class="[{'has-title':(this.$slots && this.$slots.header)}]"
            >
                <slot></slot>
            </VueScrollBar>
            <div
                    v-if="!cha"
                v-on:click="onClose"
                :class="['modal-close-btn', {'mobile': isMobile}]"
            >
                <Icons
                    type="chacha"
                />
            </div>
        </div>
    </section>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Ref, Vue} from 'vue-property-decorator';
    import {MODAL_POPUP_HEIGHT_RATIO, NAV_BAR_HEIGHT, windowHeight} from "@/helpers/Utils";
    import VueScrollBar from '@/components/VueScrollBar.vue';
    import {IS_MOBILE} from "@/helpers/Utils";

    @Component({
        components: {
            VueScrollBar,
        },
    })
    export default class FullScreenModal extends Vue {

        @Prop()
        public height!: number;
        @Prop()
        public top!: any;
        @Prop()
        public contentMaxWidth!: string;
        @Prop()
        public contentHeight!: string;
        @Prop()
        public maxHeight!: string;
        @Prop()
        public cha!: boolean;

        @Ref('content')
        readonly content!: HTMLDivElement;

        protected defaultStyle = {width: '860px', height: 'auto', top: NAV_BAR_HEIGHT + 'px', maxHeight: '950px', maxWidth: '88%'};
        readonly fullScreenStyle = {width: '100%', height: '100%', top: '0', maxHeight: '100%', maxWidth: '88%'};
        protected styles = this.defaultStyle;

        protected fullScreen: boolean = false;
        protected scrollHeight: number = 0;
        readonly popupPadding: number = 44;
        protected isMobile:boolean = IS_MOBILE;

        protected closeMaskMouseDown: boolean = false;
        /**
         * lifecycle hook when component created
         */
        protected created() {
            const popupHeight: number = this.height ? this.height : Math.floor(windowHeight() * MODAL_POPUP_HEIGHT_RATIO);

            if (this.contentHeight) {
                this.defaultStyle.height = this.contentHeight;
            } else {
                this.defaultStyle.height = popupHeight + 'px';
            }
            // this.defaultStyle.maxHeight = popupHeight + 'px';

            if (this.top){
                if (typeof(this.top)=='string' && this.top.indexOf('%') !== -1) {
                    this.defaultStyle.top = this.top;
                } else {
                    this.defaultStyle.top = this.top + 'px';
                }
            }

            if (this.maxHeight) {
                this.defaultStyle.maxHeight = this.maxHeight;
            }

            if (this.contentMaxWidth) {
                this.defaultStyle.maxWidth = this.contentMaxWidth;
            }

            this.scrollHeight = (popupHeight - this.popupPadding);
        }

        protected mounted() {
            if (this.$slots.header){
                // minus title height
                this.scrollHeight = this.scrollHeight - 50;
            }
            // it only works on first click due to keep-alive
            document.body.classList.add('no-scroll');
        }

        protected beforeDestroy() {
            document.body.classList.remove('no-scroll');
        }

        protected isAutoHeight(): boolean {
            return this.height ? false : true;
        }

        @Emit('full-screen')
        protected onFullScreen() {

            this.styles = this.fullScreenStyle;

            this.fullScreen = true;

            this.scrollHeight = windowHeight() - this.popupPadding;
        }

        @Emit('full-screen-exit')
        protected onFullScreenExit() {

            this.styles = this.defaultStyle;

            this.fullScreen = false;

            this.scrollHeight = parseInt(this.defaultStyle.height) - this.popupPadding;
        }
        /**
         * solve the case when click on modal itself and drag out of the modal then release
         */
        protected onCloseMouseDown(){
            this.closeMaskMouseDown = true;
        }
        /**
        close modal fired only when mouse down and up both on mask element itself
        to avoid accident close modal
         */
        protected onCloseMouseUp() {
            if (this.closeMaskMouseDown){
                this.onClose();
            }

            this.closeMaskMouseDown = false;
        }

        @Emit('close')
        protected onClose() {
            document.body.classList.remove('no-scroll');
        }

        @Emit('buttom')
        public onButtom() {
        }
    }
</script>
<style lang="scss" scoped>
    .mask {
        @include mask;

        .content {
            box-sizing: border-box;
            position: relative;
            margin: 0 auto;
            padding: var(--p6) 0 var(--p6) var(--p8);
            border-radius: $border-radius1;
            background-color: var(--body-bg);
            max-width: 1200px;
            overflow: visible;

            @media (max-width: 1200px) {
                max-width: 100%;
            }

            .title {

                @include title_font();

                height: 50px - $border-width;
                padding-bottom: var(--p4);
                margin: 0 var(--p8) var(--p5) 0;
                border-bottom: $border-width $border-style var(--border-color5);

                display: flex;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;

                span {
                    @include capitalize;
                    // font-size: 20px;
                    // line-height: 25px;
                    @include title_font;
                }

                .ant-btn-link {
                    @include capitalize;
                    padding-right: 0px;

                    &.can-save {
                        color: var(--theme-color);
                        font-weight: 700;
                    }
                }
            }

            .buttons {
                position: absolute;
                top: 0;
                right: var(--p8);
                cursor: pointer;
                display: flex;
                flex-direction: row;
                z-index: $nav-z-index;

                .ico.modal-full-icon {
                    margin-right: var(--p4);
                }

                .ico.modal-full-icon, .ico.modal-close-icon {
                    font-size: $font-size4;
                }
            }

            .scrollbar-wrap {
                padding-right: var(--p8);
                &.has-title {
                    
                        height: calc(100% - 50px);
                    
                }
            }

            
        }

        &.mobile {
            .scrollbar-wrap {
                background: none;
            }
        }
    }
</style>