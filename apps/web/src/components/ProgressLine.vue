<template>
    <div class="progress-line"></div>
</template>
<script lang="ts">
    import {Component, Vue} from 'vue-property-decorator';

    @Component
    export default class ProgressLine extends Vue {
    }
</script>
<style lang="scss" scoped>
    .progress-line {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        z-index: $nav-z-index;
        animation: img-placeholder 1.5s ease infinite;
        background: linear-gradient(90deg, rgb(141, 197, 230), rgb(245, 146, 78), rgb(132, 154, 230));
        background-size: 200%;
    }
</style>
