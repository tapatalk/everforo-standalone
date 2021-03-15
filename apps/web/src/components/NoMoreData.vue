<template>
    <div 
        :class="['no-more', {'mobile': isMobile}]">
        <span>{{$t('no_more_data')}}</span>&nbsp;
        <a
            v-if="showBackToTop"
            v-on:click="backToTop"
        >{{$t('back_to_top')}}</a>
    </div>
</template>
<script lang="ts">
    import {Component, Vue} from 'vue-property-decorator';
    import {IS_MOBILE} from "@/helpers/Utils";

    @Component
    export default class NoMoreData extends Vue {
        protected isMobile: boolean = IS_MOBILE;

        protected rootElem: HTMLElement = document.documentElement || document.body;

        get showBackToTop(): boolean {
            return this.rootElem.scrollHeight > this.rootElem.clientHeight;
        }

        protected backToTop() {
            this.rootElem.scrollTop = 0;
        }
    }
</script>
<style lang="scss" scoped>
    .no-more {
        padding: var(--p12);
        margin-bottom: 60px; // make some room for bottom emoji dropdown
        text-align: center;
        @include info_font;

        &.mobile {
            margin-top: var(--p10);
        }
    }
</style>
