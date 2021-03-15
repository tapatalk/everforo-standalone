<template>
    <div class="not-found">
        <img src="/img/404.png" alt="">
        <p>{{$t('page_not_found')}}</p>
    </div>
</template>
<script lang="ts">
    import {Component, Vue} from 'vue-property-decorator';

    @Component
    export default class NotFound extends Vue {

    }
</script>
<style lang="scss" scoped>
    .not-found {
        margin: 10% 0 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        p {
            margin-top: var(--p6);
            @include info_font;
        }

        img, p {
            max-width: 80%;
        }
    }
</style>
