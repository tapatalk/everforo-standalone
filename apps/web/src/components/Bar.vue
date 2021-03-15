<template>
    <div
            v-show="this.scrollSize > this.clientSize"
            class="scrollbar"
            ref="bar"
            :style="barStyle"
    ></div>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Ref, Vue, Watch} from 'vue-property-decorator';
    import {bindEvent, getPositionEvent, isTouchDevice, removeEvent, sleep} from '@/helpers/Utils';

    @Component
    export default class Bar extends Vue {

        @Ref('bar')
        readonly bar!: HTMLDivElement;

        @Prop()
        public parentScroll!: number;
        @Prop()
        public clientSize!: number;
        @Prop()
        public scrollSize!: number;

        protected sPosition: number = 0;

        protected startPosition: number = 0;

        protected startEvent!: MouseEvent | TouchEvent;

        protected dragStartEventName!: string;
        protected dragMoveEventName!: string;
        protected dragEndEventName!: string;

        get size(): number {
            return (this.clientSize / this.scrollSize) * this.clientSize;
        }

        get positionRange(): { min: number, max: number } {
            return {
                min: 0,
                max: this.clientSize - this.size,
            };
        }

        get barStyle(): object {
            return {
                top: `${this.sPosition}px !important`,
                height: `${this.size}px !important`,
            };
        }

        protected created() {
            if (isTouchDevice()) {
                this.dragStartEventName = 'touchstart';
                this.dragMoveEventName = 'touchmove';
                this.dragEndEventName = 'touchend';
            } else {
                this.dragStartEventName = 'mousedown';
                this.dragMoveEventName = 'mousemove';
                this.dragEndEventName = 'mouseup';
            }
        }

        protected beforeDestroy() {
            removeEvent(this.bar, this.dragStartEventName, this.dragStart as EventListener);
        }

        @Watch('scrollSize', {immediate: true})
        protected onScrollSize() {
            if (this.scrollSize <= this.clientSize) {
                return;
            }

            this.bindStartEvent();
        }

        protected async bindStartEvent() {
            
            if (this.bar) {
                bindEvent(this.bar, this.dragStartEventName, this.dragStart as EventListener);
                return;
            }
            
            while (!this.bar) {
                await sleep(100);
                
                if (this.bar){
                    bindEvent(this.bar, this.dragStartEventName, this.dragStart as EventListener);
                }
            }
        }

        @Watch('parentScroll', {immediate: true})
        protected onParentScroll(val: number) {

            this.sPosition =
                ((this.positionRange.max - this.positionRange.min) / (this.scrollSize - this.clientSize)) * val
                + this.positionRange.min
        }

        protected dragStart(e: MouseEvent | TouchEvent) {

            this.startPosition = this.sPosition;

            this.startEvent = e;

            removeEvent(this.bar, this.dragStartEventName, this.dragStart as EventListener);
            bindEvent(document, this.dragMoveEventName, this.drag as EventListener, {passive: true});
            bindEvent(document, this.dragEndEventName, this.dragEnd);
        }

        protected drag(e: MouseEvent | TouchEvent) {

            const position = Math.min(
                this.positionRange.max,
                Math.max(
                    this.positionRange.min,
                    this.startPosition + (getPositionEvent(e).clientY - getPositionEvent(this.startEvent).clientY),
                ),
            );

            this.scrollTo(position);
        }

        protected dragEnd() {
            removeEvent(document, this.dragMoveEventName, this.drag as EventListener, {passive: true});
            removeEvent(document, this.dragEndEventName, this.dragEnd);
            bindEvent(this.bar, this.dragStartEventName, this.dragStart as EventListener);
        }

        @Emit('scroll-to')
        protected scrollTo(position: number): object {
            return {
                scrollTop: (position / this.positionRange.max) * (this.scrollSize - this.clientSize),
            };
        }

    }
</script>