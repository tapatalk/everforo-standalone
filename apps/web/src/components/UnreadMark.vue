<template>
    <i class="unread"></i>
</template>
<script lang="ts">
    import {Component, Vue} from 'vue-property-decorator';

    @Component({
        components: {}
    })
    export default class UnreadMark extends Vue {
    }
</script>
<style lang="scss" scoped>
    .unread {
        display: inline-block;
        width: var(--p3);
        height: var(--p3);
        background-color: var(--theme-color);
        border-radius: 50%;
    }
</style>
