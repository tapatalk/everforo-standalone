<template>
    <img
        v-if="show"
        :src="src"
        :alt="alt"
        :style="styles"
    >
    <div
        v-else
        class="img-loading"
        ref="loading"
        :style="styles"
    ></div>
</template>
<script lang="ts">
    import {Component, Prop, Ref, Vue} from 'vue-property-decorator';

    @Component
    export default class ImageHolder extends Vue {
        @Ref()
        readonly loading!: HTMLDivElement;

        @Prop()
        public src!: string;
        @Prop()
        public alt!: string;
        @Prop()
        public width!: string;
        @Prop()
        public height!: string;
        @Prop()
        public radius!: boolean;

        protected show: boolean = false;

        get styles(){

            const style = {};

            if (this.width){
                Object.assign(style, {width: this.width});
            }

            if (this.height) {
                Object.assign(style, {height: this.height});
            }

            if (this.radius) {
                Object.assign(style, {borderRadius: '5px'});
            }

            return style;
        }

        protected created() {
            const img = new Image();

            img.onload = () => {
                this.show = true;
            };

            img.src = this.src;
        }
    }
</script>
<style lang="scss" scoped>
    .img-loading {
        @include img_placeholder;
        width: 100%;
        height: 200px;
        max-height: 100%;
    }
</style>