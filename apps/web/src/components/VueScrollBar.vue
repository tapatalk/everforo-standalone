<template>
    <div
        class="scrollbar-wrap"
        :style="wrapperStyle"
        v-on:click.self="onClickScrollWrap"
    >
        <div
            class="scrollbar-content"
            :style="contentStyle"
            ref="content"
        >
            <slot/>
        </div>
        <template
            v-if="height.content"
        >
            <Bar
                :parent-scroll="scrollPos"
                :client-size="this.height.content"
                :scroll-size="scrollSize ? 0 : this.height.contentInner"
                v-on:scroll-to="setScroll($event, 'drag')"
            />
        </template>
    </div>
</template>

<script lang="ts">
    import {Component, Emit, Prop, Ref, Vue, Watch} from 'vue-property-decorator';
    import {bindEvent, removeEvent} from '@/helpers/Utils';
    import Bar from '@/components/Bar.vue';

    @Component({
        components: {
            Bar,
        },
    })
    export default class VueScrollBar extends Vue {

        @Ref('content')
        readonly content!: HTMLDivElement;

        @Prop()
        public maxHeight!: number;
        @Prop()
        public minHeight!: number;
        @Prop()
        public scrollTo!: number;
        @Prop()
        public scrollSize!: number;
        @Prop()
        public pasting!: boolean;

        protected height = {content: 0, contentInner: 0};
        protected scrollPos: number = 0;
        protected isTop: boolean = false;
        protected isBottom: boolean = false;
        protected wrapperStyle!: { maxHeight: string, minHeight: string };
        protected contentStyle!: { maxHeight: string };

        protected previousScrollTop: number = 0;

        get maxScroll(): { scrollTop: number } {
            return {
                scrollTop: this.height.contentInner - this.height.content,
            };
        }

        protected mounted(): void {
            if (typeof window !== 'undefined') {
                this.getHeight();
                bindEvent(this.content, 'scroll', this.scroll, {passive: true});
            }
        }

        protected updated(): void {
            this.getHeight();
        }

        protected beforeDestroy(): void {
            removeEvent(this.content, 'scroll', this.scroll, {passive: true});
        }

        @Watch('maxHeight', {immediate: true})
        protected onMaxHeightChange(): void {
            this.wrapperStyle = {
                maxHeight: this.maxHeight + 'px',
                minHeight: this.minHeight ? this.minHeight + 'px' : 'auto',
            };

            this.contentStyle = {
                maxHeight: this.maxHeight + 'px',
            };
        }

        @Watch('minHeight')
        protected onMinHeightChange() {
            this.wrapperStyle = {
                maxHeight: this.maxHeight + 'px',
                minHeight: this.minHeight ? this.minHeight + 'px' : 'auto',
            };
        }

        @Watch('scrollTo', {immediate: true})
        protected onScrollTo(val: number): void {
            this.$nextTick(() => {
                if (val) {
                    const pos = {scrollTop: 0};

                    pos.scrollTop = +val * this.maxScroll.scrollTop;

                    this.setScroll(pos, 'drag');
                }
            });
        }

        @Watch('isBottom')
        protected onIsBottom(val: boolean): void {
            if (val) {
                this.emitReachBottom();
            }
        }

        @Emit('reach-bottom')
        protected emitReachBottom() {
        }

        @Watch('isTop')
        protected onIsTop(val: boolean): void {
            if (val) {
                this.emitReachTop();
            }
        }

        @Emit('reach-top')
        protected emitReachTop() {
        }

        @Emit('click-scroll-wrap')
        protected onClickScrollWrap() {

        }

        protected getHeight(): void {
            const {
                scrollHeight,
                clientHeight,
            } = this.content;
            this.height.content = clientHeight;
            this.height.contentInner = scrollHeight;
        }

        protected scroll(): void {
            // todo a chrome bug which cause scrollTop=0 when paste
            let {scrollTop} = this.content;
            // fix scroll jump to top when pasting
            if (this.pasting && scrollTop == 0) {
                scrollTop = this.previousScrollTop;
                this.content.scrollTop = this.previousScrollTop;
                this.resetPasting();
            }

            this.previousScrollTop = scrollTop;

            this.setScroll({scrollTop});
        }

        @Emit('reset-pasting')
        protected resetPasting(): void {

        }

        protected setScroll(
            {scrollTop = undefined as number | undefined},
            type = 'scroll',
        ): void {
            const needScroll = type !== 'scroll';
            if (scrollTop !== undefined) {
                this.setPos(scrollTop, needScroll);
            }

            this.isTop = this.scrollPos === 0;
            this.isBottom = Math.ceil(this.scrollPos) === Math.floor(this.maxScroll.scrollTop);
        }

        protected setPos(val: number, needScroll: boolean): void {
            this.scrollPos = val;
            if (needScroll) {
                this.content.scrollTop = val;
            }
        }

    }
</script>
<style lang="scss" scoped>
    .scrollbar-wrap {
        position: relative;
        padding: 0;
        overflow: hidden;
        // height: 100%;
        height: auto;
        overscroll-behavior: contain;

        .scrollbar-content {
            height: 100%;
            overflow: scroll;
            -ms-overflow-style: none;
            scrollbar-width: none;

            &::-webkit-scrollbar {
                width: 0;
                height: 0;
            }
        }

        .scrollbar {
            position: absolute;
            border-radius: .25em;
            background: #eee;
            box-shadow: 0 0 2px rgba(0, 0, 0, 0.1);
            right: 0.25em;
            width: 4px;
            opacity: 0.8;
            pointer-events: auto;
            cursor: pointer;
            user-select: none; // so when drag, won't select any content
        }
    }
</style>