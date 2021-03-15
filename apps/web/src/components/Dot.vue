<template>
    <span
        :class="['dot-dot', {'mobile': isMobile}]"
    >
        &#8226;
    </span>
</template>
<script lang="ts">
    import {Component, Vue} from 'vue-property-decorator';
    import {IS_MOBILE} from "@/helpers/Utils";

    @Component({
        components: {
        }
    })
    export default class Dot extends Vue {
        protected isMobile: boolean = IS_MOBILE;
    }
</script>
<style lang="scss" scoped>
    .dot-dot {
        display: inline-block;
        padding: 0 var(--p1);
        color: var(--font-color2);
        font-size: 0.8rem;

        &.mobile {
            padding: 0 var(--p2);
        }
    }
</style>